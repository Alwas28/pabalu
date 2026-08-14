<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola halaman.');
        }
    }

    public function index(): View
    {
        $this->authorizeAdmin();

        $pages = Page::orderByDesc('updated_at')->get();

        return view('pages.index', compact('pages'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'title'             => ['required', 'string', 'max:200'],
            'slug'              => ['nullable', 'string', 'max:180', 'alpha_dash', 'unique:pages,slug'],
            'image'             => ['nullable', 'image', 'max:2048'],
            'content'           => ['nullable', 'string'],
            'meta_title'        => ['nullable', 'string', 'max:200'],
            'meta_description'  => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $data['slug'] ?: Str::slug($data['title']);
        $slug = $this->uniqueSlug($slug);

        $page = Page::create([
            'title'             => $data['title'],
            'slug'              => $slug,
            'image'             => $request->hasFile('image') ? $request->file('image')->store('pages', 'public') : null,
            'content'           => $data['content'] ?? null,
            'meta_title'        => $data['meta_title'] ?: $data['title'],
            'meta_description'  => $data['meta_description'] ?? null,
            'is_active'         => $request->boolean('is_active'),
        ]);

        return redirect()->route('pages.edit', $page)->with('success', "Halaman \"{$page->title}\" berhasil dibuat.");
    }

    public function edit(Page $page): View
    {
        $this->authorizeAdmin();

        return view('pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'title'             => ['required', 'string', 'max:200'],
            'slug'              => ['nullable', 'string', 'max:180', 'alpha_dash', "unique:pages,slug,{$page->id}"],
            'image'             => ['nullable', 'image', 'max:2048'],
            'content'           => ['nullable', 'string'],
            'meta_title'        => ['nullable', 'string', 'max:200'],
            'meta_description'  => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $data['slug'] ?: Str::slug($data['title']);
        if ($slug !== $page->slug) {
            $slug = $this->uniqueSlug($slug, $page->id);
        }

        $updateData = [
            'title'             => $data['title'],
            'slug'              => $slug,
            'content'           => $data['content'] ?? null,
            'meta_title'        => $data['meta_title'] ?: $data['title'],
            'meta_description'  => $data['meta_description'] ?? null,
            'is_active'         => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            if ($page->image) {
                Storage::disk('public')->delete($page->image);
            }
            $updateData['image'] = $request->file('image')->store('pages', 'public');
        }

        $page->update($updateData);

        return back()->with('success', "Halaman \"{$page->title}\" berhasil disimpan.");
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($page->image) {
            Storage::disk('public')->delete($page->image);
        }

        $title = $page->title;
        $page->delete();

        return redirect()->route('pages.index')->with('success', "Halaman \"{$title}\" berhasil dihapus.");
    }

    public function toggleActive(Page $page): RedirectResponse
    {
        $this->authorizeAdmin();

        $page->update(['is_active' => !$page->is_active]);
        $status = $page->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Halaman berhasil {$status}.");
    }

    /** Upload gambar dari editor konten (TinyMCE) — dipanggil via AJAX, balas format {location} yang diharapkan TinyMCE. */
    public function uploadImage(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'file' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('file')->store('pages/content', 'public');

        return response()->json(['location' => Storage::url($path)]);
    }

    /** Halaman publik — hanya bisa dilihat kalau aktif, kecuali admin (mode preview). */
    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        $user = Auth::user();
        $isPreviewingAdmin = $user && $user->role === 'admin';

        if (!$page->is_active && !$isPreviewingAdmin) {
            abort(404);
        }

        return view('pages.show', compact('page'));
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i    = 1;
        while (Page::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
