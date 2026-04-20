<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(Request $request): View
    {
        $user  = auth()->user();
        $query = $user->accessibleOutlets()->withCount('products')->with('owner');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->get('status') !== '') {
            $query->where('is_active', $request->get('status') === '1');
        }

        $outlets = $query->latest()->paginate(15)->withQueryString();

        $base    = $user->accessibleOutlets();
        $stats   = [
            'total'    => (clone $base)->count(),
            'aktif'    => (clone $base)->where('is_active', true)->count(),
            'nonaktif' => (clone $base)->where('is_active', false)->count(),
        ];

        return view('outlets.index', compact('outlets', 'stats'));
    }

    public function create(): View
    {
        $owners = auth()->user()->isAdmin()
            ? User::role('owner')->orderBy('name')->get()
            : collect();
        return view('outlets.create', compact('owners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'nama'       => ['required', 'string', 'max:150', 'unique:outlets,nama'],
            'alamat'     => ['nullable', 'string', 'max:500'],
            'telepon'    => ['nullable', 'string', 'max:20'],
            'email'      => ['nullable', 'email', 'max:150'],
            'keterangan' => ['nullable', 'string'],
            'is_active'  => ['boolean'],
        ];

        if (auth()->user()->isAdmin()) {
            $rules['owner_id'] = ['nullable', 'exists:users,id'];
        }

        $validated = $request->validate($rules);

        if (auth()->user()->isOwner()) {
            $validated['owner_id'] = auth()->id();
        }

        $outlet = Outlet::create($validated);
        $outlet->update(['slug' => $this->uniqueSlug($outlet->nama, $outlet->id)]);

        ActivityLog::record('create_outlet', "Outlet \"{$outlet->nama}\" dibuat.", $outlet);

        return redirect()->route('outlets.index')
            ->with('success', "Outlet \"{$outlet->nama}\" berhasil ditambahkan.");
    }

    public function edit(Outlet $outlet): View
    {
        $this->authorizeOutlet($outlet);
        $owners = auth()->user()->isAdmin()
            ? User::role('owner')->orderBy('name')->get()
            : collect();
        return view('outlets.edit', compact('outlet', 'owners'));
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        $rules = [
            'nama'       => ['required', 'string', 'max:150', 'unique:outlets,nama,' . $outlet->id],
            'alamat'     => ['nullable', 'string', 'max:500'],
            'telepon'    => ['nullable', 'string', 'max:20'],
            'email'      => ['nullable', 'email', 'max:150'],
            'keterangan' => ['nullable', 'string'],
            'is_active'  => ['boolean'],
        ];

        if (auth()->user()->isAdmin()) {
            $rules['owner_id'] = ['nullable', 'exists:users,id'];
        }

        $validated = $request->validate($rules);

        // Checkbox tidak terkirim saat unchecked — baca dari boolean helper
        $validated['is_active'] = $request->boolean('is_active');

        $outlet->update($validated);

        if ($outlet->wasChanged('nama') || ! $outlet->slug) {
            $outlet->update(['slug' => $this->uniqueSlug($outlet->nama, $outlet->id)]);
        }

        ActivityLog::record('update_outlet', "Outlet \"{$outlet->nama}\" diperbarui.", $outlet);

        return redirect()->route('outlets.index')
            ->with('success', "Outlet \"{$outlet->nama}\" berhasil diperbarui.");
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        if ($outlet->products()->exists()) {
            return redirect()->route('outlets.index')
                ->with('error', "Outlet \"{$outlet->nama}\" tidak dapat dihapus karena masih memiliki produk.");
        }

        $nama = $outlet->nama;
        ActivityLog::record('delete_outlet', "Outlet \"{$nama}\" dihapus.");
        $outlet->delete();

        return redirect()->route('outlets.index')
            ->with('success', "Outlet \"{$nama}\" berhasil dihapus.");
    }

    // Pastikan owner hanya bisa akses outlet miliknya
    private function authorizeOutlet(Outlet $outlet): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;
        if ($user->isOwner() && $outlet->owner_id === $user->id) return;
        abort(403);
    }

    private function uniqueSlug(string $nama, int $excludeId): string
    {
        $base = Str::slug($nama);
        $slug = $base;
        $i    = 1;
        while (Outlet::where('slug', $slug)->where('id', '!=', $excludeId)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
