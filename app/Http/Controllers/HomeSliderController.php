<?php

namespace App\Http\Controllers;

use App\Models\HomeSlider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeSliderController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola slider homepage.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'image'       => ['required', 'image', 'max:2048'],
            'badge'       => ['nullable', 'string', 'max:100'],
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'button_url'  => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['image']      = $request->file('image')->store('sliders', 'public');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        HomeSlider::create($data);

        return back()->with('success', 'Slider berhasil ditambahkan.');
    }

    public function update(Request $request, HomeSlider $slider): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'image'       => ['nullable', 'image', 'max:2048'],
            'badge'       => ['nullable', 'string', 'max:100'],
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'button_url'  => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image')) {
            if ($slider->image) {
                Storage::disk('public')->delete($slider->image);
            }
            $data['image'] = $request->file('image')->store('sliders', 'public');
        }

        $data['sort_order'] = $data['sort_order'] ?? 0;

        $slider->update($data);

        return back()->with('success', 'Slider berhasil diperbarui.');
    }

    public function destroy(HomeSlider $slider): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($slider->image) {
            Storage::disk('public')->delete($slider->image);
        }
        $slider->delete();

        return back()->with('success', 'Slider berhasil dihapus.');
    }

    public function toggleActive(HomeSlider $slider): RedirectResponse
    {
        $this->authorizeAdmin();

        $slider->update(['is_active' => !$slider->is_active]);
        $status = $slider->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Slider berhasil {$status}.");
    }
}
