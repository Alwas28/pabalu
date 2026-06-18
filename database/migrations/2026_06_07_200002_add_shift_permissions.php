<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $lastOrder = (int) DB::table('permissions')->max('order');

        $perms = [
            ['name' => 'Lihat Riwayat Shift', 'slug' => 'shift.view',   'group' => 'Shift', 'order' => $lastOrder + 1],
            ['name' => 'Buka Shift',           'slug' => 'shift.open',   'group' => 'Shift', 'order' => $lastOrder + 2],
            ['name' => 'Tutup Shift',          'slug' => 'shift.close',  'group' => 'Shift', 'order' => $lastOrder + 3],
            ['name' => 'Edit & Hapus Shift',   'slug' => 'shift.manage', 'group' => 'Shift', 'order' => $lastOrder + 4],
        ];

        foreach ($perms as $perm) {
            DB::table('permissions')->insertOrIgnore($perm + ['created_at' => now(), 'updated_at' => now()]);
        }

        $ids = DB::table('permissions')
            ->whereIn('slug', ['shift.view', 'shift.open', 'shift.close', 'shift.manage'])
            ->pluck('id', 'slug');

        $roles = DB::table('roles')->whereIn('slug', ['admin', 'owner', 'admin_outlet', 'kasir'])
            ->pluck('id', 'slug');

        $assignments = [
            'admin'        => ['shift.view', 'shift.open', 'shift.close', 'shift.manage'],
            'owner'        => ['shift.view', 'shift.open', 'shift.close', 'shift.manage'],
            'admin_outlet' => ['shift.view', 'shift.open', 'shift.close'],
            'kasir'        => ['shift.view', 'shift.open', 'shift.close'],
        ];

        foreach ($assignments as $roleSlug => $slugs) {
            if (!isset($roles[$roleSlug])) continue;
            $roleId = $roles[$roleSlug];
            foreach ($slugs as $slug) {
                if (!isset($ids[$slug])) continue;
                DB::table('role_permission')->insertOrIgnore([
                    'role_id'       => $roleId,
                    'permission_id' => $ids[$slug],
                ]);
            }
            // Flush cached permissions for this role
            Cache::forget("role.{$roleId}.perms");
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('slug', ['shift.view', 'shift.open', 'shift.close', 'shift.manage'])
            ->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('slug', ['shift.view', 'shift.open', 'shift.close', 'shift.manage'])->delete();
    }
};
