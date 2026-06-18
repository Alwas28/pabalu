<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class RoleController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola role dan permission.');
        }
    }

    public function index(): View
    {
        $this->authorizeAdmin();

        $roles = Role::withCount('permissions')->orderBy('is_system', 'desc')->orderBy('id')->get();

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        $permissions = Permission::orderBy('order')->get()->groupBy('group');

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name'], '_');
        $slug = preg_replace('/[^a-z0-9_]/', '', strtolower($slug));

        if (Role::where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Nama role sudah digunakan. Gunakan nama yang berbeda.'])->withInput();
        }

        $role = Role::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'],
            'is_system'   => false,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    public function show(Role $role): RedirectResponse
    {
        return redirect()->route('roles.edit', $role);
    }

    public function edit(Role $role): View
    {
        $this->authorizeAdmin();

        $permissions    = Permission::orderBy('order')->get()->groupBy('group');
        $rolePermIds    = $role->permissions()->pluck('permissions.id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermIds'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // System role: only allow permission edits, not name changes (unless admin)
        if (!$role->is_system) {
            $slug = \Illuminate\Support\Str::slug($validated['name'], '_');
            $slug = preg_replace('/[^a-z0-9_]/', '', strtolower($slug));

            if (Role::where('slug', $slug)->where('id', '!=', $role->id)->exists()) {
                return back()->withErrors(['name' => 'Nama role sudah digunakan.'])->withInput();
            }

            $role->update([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'description' => $validated['description'],
            ]);
        } else {
            $role->update(['description' => $validated['description']]);
        }

        // Admin role always keeps all permissions
        if ($role->slug !== 'admin') {
            $role->permissions()->sync($validated['permissions'] ?? []);
            Cache::forget("role.{$role->id}.perms");
        }

        return redirect()->route('roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($role->is_system) {
            return back()->with('error', "Role sistem \"{$role->name}\" tidak dapat dihapus.");
        }

        $name = $role->name;
        Cache::forget("role.{$role->id}.perms");
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Role \"{$name}\" berhasil dihapus.");
    }
}
