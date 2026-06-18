<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PartnerController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola mitra UMKM.');
        }
    }

    private function rules(bool $isUpdate = false): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'logo'        => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ];
    }

    public function index(): View
    {
        $this->authorizeAdmin();

        $partners = Partner::orderBy('sort_order')->orderBy('name')->get();

        return view('partners.index', compact('partners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate($this->rules());

        $data['logo']       = $request->file('logo')->store('partners', 'public');
        $data['is_active']  = true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Partner::create($data);

        return back()->with('success', "Mitra \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate($this->rules(true));

        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('logo')) {
            Storage::disk('public')->delete($partner->logo);
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }

        $partner->update($data);

        return back()->with('success', "Mitra \"{$partner->name}\" berhasil diperbarui.");
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $this->authorizeAdmin();

        Storage::disk('public')->delete($partner->logo);

        $name = $partner->name;
        $partner->delete();

        return back()->with('success', "Mitra \"{$name}\" berhasil dihapus.");
    }

    public function toggleActive(Partner $partner): RedirectResponse
    {
        $this->authorizeAdmin();

        $partner->update(['is_active' => !$partner->is_active]);
        $status = $partner->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Mitra \"{$partner->name}\" berhasil {$status}.");
    }
}
