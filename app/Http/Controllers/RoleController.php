<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // Kelompokkan permission berdasarkan modul
    public static function permissionGroups(): array
    {
        return [
            'Outlet'            => ['outlet.create','outlet.read','outlet.update','outlet.delete'],
            'Role & Permission' => ['role.create','role.read','role.update','role.delete','permission.create','permission.read','permission.update','permission.delete'],
            'User / Karyawan'   => ['user.create','user.read','user.update','user.delete','user.assign'],
            'Produk'            => ['product.create','product.read','product.update','product.delete'],
            'Kategori'          => ['category.create','category.read','category.update','category.delete'],
            'Stok'              => ['stock.opening','stock.in','stock.waste','stock.read'],
            'Transaksi / POS'   => ['transaction.create','transaction.read','transaction.void'],
            'Pengeluaran'       => ['expense.create','expense.read','expense.update','expense.delete'],
            'Closing Harian'    => ['closing.create','closing.read'],
            'Laporan'           => ['report.outlet','report.all'],
            'Order Online'      => ['order.read','order.manage'],
            'Log & Pengaturan'  => ['log.read','setting.read','setting.update'],
            'Panduan'           => ['guide.read','guide.update'],
        ];
    }

    public function index(): View
    {
        $roles = Role::withCount('permissions')->withCount('users')->get();
        return view('rbac.index', compact('roles'));
    }

    public function create(): View
    {
        $groups         = self::permissionGroups();
        $allPermissions = Permission::orderBy('name')->get();
        return view('rbac.create', compact('groups', 'allPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => ['required','string','max:64','unique:roles,name'],
            'permissions' => ['nullable','array'],
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        $permCount = $role->permissions()->count();
        ActivityLog::record('create_role',
            "Role \"{$role->name}\" dibuat dengan {$permCount} permission."
        );

        return redirect()->route('rbac.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    public function edit(Role $role): View
    {
        $groups          = self::permissionGroups();
        $allPermissions  = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('rbac.edit', compact('role', 'groups', 'allPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name'        => ['required','string','max:64','unique:roles,name,'.$role->id],
            'permissions' => ['nullable','array'],
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        $permCount = $role->permissions()->count();
        ActivityLog::record('update_role',
            "Role \"{$role->name}\" diperbarui, {$permCount} permission aktif."
        );

        return redirect()->route('rbac.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'admin') {
            return redirect()->route('rbac.roles.index')
                ->with('error', 'Role admin tidak dapat dihapus.');
        }

        $name = $role->name;

        ActivityLog::record('delete_role', "Role \"{$name}\" dihapus.");

        $role->delete();

        return redirect()->route('rbac.roles.index')
            ->with('success', "Role \"{$name}\" berhasil dihapus.");
    }
}
