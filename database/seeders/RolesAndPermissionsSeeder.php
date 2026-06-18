<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 14 permission groups × 51 permissions total
        $groups = [
            'Dashboard' => [
                ['Lihat Dashboard',      'dashboard.view'],
                ['Lihat Analitik',       'dashboard.analytics'],
            ],
            'POS' => [
                ['Akses POS / Kasir',    'pos.access'],
                ['Terapkan Diskon',      'pos.discount'],
                ['Void Transaksi POS',   'pos.void'],
            ],
            'Order Online' => [
                ['Lihat Antrian Order',  'order.view'],
                ['Proses / Update Order','order.process'],
                ['Batalkan Order',       'order.cancel'],
            ],
            'Transaksi' => [
                ['Lihat Riwayat',        'transaction.view'],
                ['Ekspor Transaksi',     'transaction.export'],
                ['Void Transaksi',       'transaction.void'],
            ],
            'Stok' => [
                ['Lihat Stok',           'stock.view'],
                ['Input Opening Stok',   'stock.opening'],
                ['Tambah Stok Masuk',    'stock.in'],
                ['Catat Waste / Rusak',  'stock.waste'],
                ['Closing Harian',       'stock.closing'],
            ],
            'Pengeluaran' => [
                ['Lihat Pengeluaran',    'expense.view'],
                ['Tambah Pengeluaran',   'expense.create'],
                ['Edit Pengeluaran',     'expense.edit'],
                ['Hapus Pengeluaran',    'expense.delete'],
            ],
            'Produk' => [
                ['Lihat Produk',         'product.view'],
                ['Tambah Produk',        'product.create'],
                ['Edit Produk',          'product.edit'],
                ['Hapus Produk',         'product.delete'],
            ],
            'Kategori' => [
                ['Lihat Kategori',       'category.view'],
                ['Tambah Kategori',      'category.create'],
                ['Edit Kategori',        'category.edit'],
                ['Hapus Kategori',       'category.delete'],
            ],
            'Laporan' => [
                ['Laporan Penjualan',    'report.sales'],
                ['Laporan Stok',         'report.stock'],
                ['Laba & Rugi',          'report.profit_loss'],
                ['Ekspor Laporan',       'report.export'],
            ],
            'Outlet' => [
                ['Lihat Outlet',         'outlet.view'],
                ['Tambah Outlet',        'outlet.create'],
                ['Edit Outlet',          'outlet.edit'],
                ['Hapus Outlet',         'outlet.delete'],
            ],
            'Kelola User' => [
                ['Lihat Daftar User',    'user.view'],
                ['Tambah User',          'user.create'],
                ['Edit User',            'user.edit'],
                ['Hapus User',           'user.delete'],
                ['Verifikasi Email User','user.verify'],
            ],
            'Billing' => [
                ['Lihat Tagihan',        'billing.view'],
                ['Bayar Tagihan',        'billing.pay'],
                ['Kelola Tagihan',       'billing.manage'],
            ],
            'Role & Permission' => [
                ['Lihat Role',           'role.view'],
                ['Buat Role Baru',       'role.create'],
                ['Edit Role',            'role.edit'],
                ['Hapus Role',           'role.delete'],
            ],
            'Pengaturan Sistem' => [
                ['Lihat Log Aktivitas',  'log.view'],
                ['Lihat Pengaturan',     'setting.view'],
                ['Edit Pengaturan',      'setting.edit'],
                ['Kelola Jenis Outlet',  'outlet_type.manage'],
            ],
            'Shift' => [
                ['Lihat Riwayat Shift',  'shift.view'],
                ['Buka Shift',           'shift.open'],
                ['Tutup Shift',          'shift.close'],
                ['Edit & Hapus Shift',   'shift.manage'],
            ],
        ];

        // Seed permissions
        $order = 0;
        foreach ($groups as $group => $items) {
            foreach ($items as [$name, $slug]) {
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'group' => $group, 'order' => $order++]
                );
            }
        }

        // Default roles
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Akses penuh ke seluruh sistem',
                'is_system' => true,
            ],
            [
                'name' => 'Pemilik Toko',
                'slug' => 'owner',
                'description' => 'Kelola outlet, produk, laporan, dan user kasir',
                'is_system' => true,
            ],
            [
                'name' => 'Admin Outlet',
                'slug' => 'admin_outlet',
                'description' => 'Operasional harian dan laporan outlet sendiri',
                'is_system' => true,
            ],
            [
                'name' => 'Kasir',
                'slug' => 'kasir',
                'description' => 'POS, stok harian, pengeluaran, dan antrian order',
                'is_system' => true,
            ],
        ];

        foreach ($roles as $data) {
            Role::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $all          = Permission::pluck('id')->toArray();
        $adminRole    = Role::where('slug', 'admin')->first();
        $ownerRole    = Role::where('slug', 'owner')->first();
        $aoRole       = Role::where('slug', 'admin_outlet')->first();
        $kasirRole    = Role::where('slug', 'kasir')->first();

        // Admin: all permissions
        $adminRole->permissions()->sync($all);

        // Owner: semua kecuali system-only
        $ownerPerms = Permission::whereNotIn('slug', [
            'billing.manage',
            'role.create', 'role.edit', 'role.delete',
            'log.view', 'setting.edit', 'outlet_type.manage',
        ])->pluck('id')->toArray(); // shift.* otomatis masuk karena tidak di-exclude
        $ownerRole->permissions()->sync($ownerPerms);

        // Admin Outlet: operasional + laporan, no management
        $aoPerms = Permission::whereIn('slug', [
            'dashboard.view',
            'pos.access', 'pos.discount', 'pos.void',
            'order.view', 'order.process', 'order.cancel',
            'transaction.view', 'transaction.export', 'transaction.void',
            'stock.view', 'stock.opening', 'stock.in', 'stock.waste', 'stock.closing',
            'expense.view', 'expense.create', 'expense.edit', 'expense.delete',
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'category.view', 'category.create', 'category.edit',
            'report.sales', 'report.stock', 'report.export',
            'shift.view', 'shift.open', 'shift.close',
        ])->pluck('id')->toArray();
        $aoRole->permissions()->sync($aoPerms);

        // Kasir: basic operations only — no stock input, no closing, no category/product modification
        $kasirPerms = Permission::whereIn('slug', [
            'dashboard.view',
            'pos.access', 'pos.discount',
            'order.view', 'order.process', 'order.cancel',
            'transaction.view',
            'stock.view',
            'expense.view', 'expense.create',
            'product.view',
            'shift.view', 'shift.open', 'shift.close',
        ])->pluck('id')->toArray();
        $kasirRole->permissions()->sync($kasirPerms);
    }
}
