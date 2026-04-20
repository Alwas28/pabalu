<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // ── Outlet ──
            'outlet.create',
            'outlet.read',
            'outlet.update',
            'outlet.delete',

            // ── Role & Permission ──
            'role.create',
            'role.read',
            'role.update',
            'role.delete',
            'permission.create',
            'permission.read',
            'permission.update',
            'permission.delete',

            // ── User / Karyawan ──
            'user.create',
            'user.read',
            'user.update',
            'user.delete',
            'user.assign',       // assign user + role + outlet

            // ── Produk & Kategori ──
            'product.create',
            'product.read',
            'product.update',
            'product.delete',
            'category.create',
            'category.read',
            'category.update',
            'category.delete',

            // ── Stok (berdasarkan movement) ──
            'stock.opening',     // input stok awal (opening)
            'stock.in',          // tambah stok / produksi
            'stock.waste',       // input waste / barang rusak
            'stock.read',        // lihat stok

            // ── Transaksi / POS (Kasir) ──
            'transaction.create',
            'transaction.read',
            'transaction.void',

            // ── Pengeluaran (expense) ──
            'expense.create',
            'expense.read',
            'expense.update',
            'expense.delete',

            // ── Closing Harian ──
            'closing.create',
            'closing.read',

            // ── Laporan ──
            'report.outlet',     // laporan outlet sendiri (Admin)
            'report.all',        // laporan semua outlet (Owner)

            // ── Order Online (Antrian) ──
            'order.read',    // lihat antrian order
            'order.manage',  // advance / cancel order

            // ── Log & Pengaturan ──
            'log.read',
            'setting.read',
            'setting.update',

            // ── Panduan Penggunaan ──
            'guide.read',
            'guide.update',

            // ── Billing (tagihan aplikasi) ──
            'billing.read',   // owner: lihat tagihan sendiri
            'billing.manage', // admin: buat/batalkan tagihan

            // ── Dokumentasi API ──
            'api.docs',       // akses halaman dokumentasi API
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── Roles ──

        // Admin: semua hak akses
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        // Owner: kelola outlet, user, role; lihat semua laporan + log + setting
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $ownerRole->syncPermissions([
            'outlet.create', 'outlet.read', 'outlet.update', 'outlet.delete',
            'role.create', 'role.read', 'role.update', 'role.delete',
            'permission.read', 'permission.update',
            'user.create', 'user.read', 'user.update', 'user.delete', 'user.assign',
            'product.create', 'product.read', 'product.update', 'product.delete',
            'category.create', 'category.read', 'category.update', 'category.delete',
            'stock.opening', 'stock.in', 'stock.waste', 'stock.read',
            'transaction.read',
            'expense.create', 'expense.read', 'expense.update', 'expense.delete',
            'closing.read',
            'report.outlet', 'report.all',
            'order.read', 'order.manage',
            'log.read',
            'setting.read', 'setting.update',
            'guide.read',
            'billing.read',
            'api.docs',
        ]);

        // Admin Outlet: operasional harian + laporan outlet sendiri
        $adminOutletRole = Role::firstOrCreate(['name' => 'admin_outlet']);
        $adminOutletRole->syncPermissions([
            'product.read', 'product.create', 'product.update',
            'category.read',
            'stock.opening', 'stock.in', 'stock.waste', 'stock.read',
            'transaction.read',
            'expense.create', 'expense.read', 'expense.update', 'expense.delete',
            'closing.create', 'closing.read',
            'report.outlet',
            'order.read', 'order.manage',
            'guide.read',
        ]);

        // Kasir: hanya POS / transaksi
        $kasirRole = Role::firstOrCreate(['name' => 'kasir']);
        $kasirRole->syncPermissions([
            'product.read',
            'category.read',
            'stock.read',
            'transaction.create', 'transaction.read',
            'order.read', 'order.manage',
            'guide.read',
        ]);
    }
}
