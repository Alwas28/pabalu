<?php

namespace App\Http\Controllers;

use App\Models\PromoBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PromoBannerController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola banner iklan homepage.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'image'       => ['required', 'image', 'max:2048'],
            'badge'       => ['nullable', 'string', 'max:100'],
            'title'       => ['required', 'string', 'max:200'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'button_url'  => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['image']      = $request->file('image')->store('promo-banners', 'public');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        PromoBanner::create($data);

        return back()->with('success', 'Banner iklan berhasil ditambahkan.');
    }

    public function update(Request $request, PromoBanner $promoBanner): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'image'       => ['nullable', 'image', 'max:2048'],
            'badge'       => ['nullable', 'string', 'max:100'],
            'title'       => ['required', 'string', 'max:200'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'button_url'  => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image')) {
            if ($promoBanner->image) {
                Storage::disk('public')->delete($promoBanner->image);
            }
            $data['image'] = $request->file('image')->store('promo-banners', 'public');
        }

        $data['sort_order'] = $data['sort_order'] ?? 0;

        $promoBanner->update($data);

        return back()->with('success', 'Banner iklan berhasil diperbarui.');
    }

    public function destroy(PromoBanner $promoBanner): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($promoBanner->image) {
            Storage::disk('public')->delete($promoBanner->image);
        }
        $promoBanner->delete();

        return back()->with('success', 'Banner iklan berhasil dihapus.');
    }

    public function toggleActive(PromoBanner $promoBanner): RedirectResponse
    {
        $this->authorizeAdmin();

        $promoBanner->update(['is_active' => !$promoBanner->is_active]);
        $status = $promoBanner->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Banner iklan berhasil {$status}.");
    }
}
