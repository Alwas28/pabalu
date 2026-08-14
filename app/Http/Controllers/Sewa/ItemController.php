<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\RentalItem;
use App\Models\RentalItemImage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ItemController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }
        return $user;
    }

    private function authorizeItem(Outlet $outlet, RentalItem $item): void
    {
        if ((int) $item->outlet_id !== (int) $outlet->id) {
            abort(404);
        }
    }

    private function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:150'],
            'category_id'  => ['nullable', 'exists:categories,id'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'images'       => ['nullable', 'array', 'max:' . RentalItem::MAX_IMAGES],
            'images.*'     => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function storeImages(RentalItem $item, array $files, int $startSort = 0): void
    {
        foreach (array_values($files) as $i => $file) {
            $path = $file->store("rental-items/{$item->outlet_id}", 'public');
            RentalItemImage::create([
                'rental_item_id' => $item->id,
                'path'           => $path,
                'sort_order'     => $startSort + $i,
            ]);
        }
    }

    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        $items      = $outlet->rentalItems()->with(['category', 'images'])->get();
        $categories = $outlet->categories()->where('is_active', true)->get();

        return view('sewa.items.index', compact('outlet', 'user', 'items', 'categories'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        $data = $request->validate($this->rules());

        if (!empty($data['category_id'])) {
            $outlet->categories()->findOrFail($data['category_id']);
        }

        $item = RentalItem::create([
            'outlet_id'   => $outlet->id,
            'category_id' => $data['category_id'] ?? null,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if ($request->hasFile('images')) {
            $this->storeImages($item, $request->file('images'));
        }

        return back()->with('success', 'Barang sewa berhasil ditambahkan.');
    }

    public function update(Request $request, Outlet $outlet, RentalItem $item): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeItem($outlet, $item);

        $data = $request->validate($this->rules());

        if (!empty($data['category_id'])) {
            $outlet->categories()->findOrFail($data['category_id']);
        }

        // Hapus foto yang ditandai untuk dihapus
        $removeIds = collect($request->input('remove_images', []))->map(fn ($id) => (int) $id);
        $toRemove  = $item->images()->whereIn('id', $removeIds)->get();
        foreach ($toRemove as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }

        $remainingCount = $item->images()->count();
        $newFiles       = $request->file('images', []);
        $newCount       = is_array($newFiles) ? count($newFiles) : 0;

        if ($remainingCount + $newCount > RentalItem::MAX_IMAGES) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('images', 'Maksimal ' . RentalItem::MAX_IMAGES . ' foto per barang.');
            })->validate();
        }

        $item->update([
            'category_id' => $data['category_id'] ?? null,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if ($newCount > 0) {
            $maxSort = $item->images()->max('sort_order');
            $this->storeImages($item, $newFiles, $maxSort !== null ? $maxSort + 1 : 0);
        }

        return back()->with('success', 'Barang sewa berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet, RentalItem $item): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeItem($outlet, $item);

        foreach ($item->images as $img) {
            Storage::disk('public')->delete($img->path);
        }

        $item->delete();

        return back()->with('success', 'Barang sewa berhasil dihapus.');
    }

    public function toggleActive(Outlet $outlet, RentalItem $item): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeItem($outlet, $item);

        $item->update(['is_active' => !$item->is_active]);
        $status = $item->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Barang sewa berhasil {$status}.");
    }
}
