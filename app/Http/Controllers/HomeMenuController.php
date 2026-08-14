<?php

namespace App\Http\Controllers;

use App\Models\HomeMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeMenuController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola menu homepage.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'parent_id'   => ['nullable', 'exists:home_menus,id'],
            'group_label' => ['nullable', 'string', 'max:100'],
            'label'       => ['required', 'string', 'max:100'],
            'type'        => ['nullable', 'in:simple,mega'],
            'url'         => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['url']        = $data['url'] ?: '#';
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if (!empty($data['parent_id'])) {
            // Sub-menu: tipe cuma berlaku untuk menu utama.
            unset($data['type']);
        } else {
            // Menu utama: group_label (judul kolom) cuma berlaku untuk sub-menu di mega menu.
            $data['type']        = $data['type'] ?? 'simple';
            $data['group_label'] = null;
        }

        HomeMenu::create($data);

        return back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function update(Request $request, HomeMenu $homeMenu): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'parent_id'   => ['nullable', 'exists:home_menus,id'],
            'group_label' => ['nullable', 'string', 'max:100'],
            'label'       => ['required', 'string', 'max:100'],
            'type'        => ['nullable', 'in:simple,mega'],
            'url'         => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        if ((int) ($data['parent_id'] ?? 0) === $homeMenu->id) {
            return back()->with('error', 'Menu tidak bisa menjadi sub-menu dari dirinya sendiri.');
        }

        $data['url']        = $data['url'] ?: '#';
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if (!empty($data['parent_id'])) {
            unset($data['type']);
        } else {
            $data['type']        = $data['type'] ?? 'simple';
            $data['group_label'] = null;
        }

        $homeMenu->update($data);

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(HomeMenu $homeMenu): RedirectResponse
    {
        $this->authorizeAdmin();

        $homeMenu->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    public function toggleActive(HomeMenu $homeMenu): RedirectResponse
    {
        $this->authorizeAdmin();

        $homeMenu->update(['is_active' => !$homeMenu->is_active]);
        $status = $homeMenu->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Menu berhasil {$status}.");
    }
}
