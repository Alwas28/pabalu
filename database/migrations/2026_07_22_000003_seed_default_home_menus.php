<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('home_menus')->exists()) {
            return; // sudah ada data (mis. sudah diisi manual), jangan timpa
        }

        $now = now();

        $insertParent = function (string $label, string $type, int $sort) use ($now) {
            return DB::table('home_menus')->insertGetId([
                'label' => $label, 'type' => $type, 'url' => '#',
                'sort_order' => $sort, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        };

        $insertChild = function (int $parentId, string $label, int $sort, ?string $group = null, string $url = '#') use ($now) {
            DB::table('home_menus')->insert([
                'parent_id' => $parentId, 'group_label' => $group,
                'label' => $label, 'type' => 'simple', 'url' => $url,
                'sort_order' => $sort, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        };

        // ── Home (biasa) ──
        $home = $insertParent('Home', 'simple', 1);
        $insertChild($home, 'Beranda — Klasik', 1);
        $insertChild($home, 'Beranda — Organik', 2);
        $insertChild($home, 'Beranda — Pasar Segar', 3);
        $insertChild($home, 'Beranda — Grosir', 4);

        // ── Belanja (mega) ──
        $belanja = $insertParent('Belanja', 'mega', 2);
        $insertChild($belanja, 'Buah & Sayur', 1, 'Berdasarkan Kategori');
        $insertChild($belanja, 'Daging & Seafood', 2, 'Berdasarkan Kategori');
        $insertChild($belanja, 'Roti & Kue', 3, 'Berdasarkan Kategori');
        $insertChild($belanja, 'Minuman', 4, 'Berdasarkan Kategori');
        $insertChild($belanja, 'Grid Standar', 1, 'Tampilan Toko');
        $insertChild($belanja, 'Grid Rapat', 2, 'Tampilan Toko');
        $insertChild($belanja, 'Tampilan List', 3, 'Tampilan Toko');
        $insertChild($belanja, 'Sidebar Kiri', 4, 'Tampilan Toko');
        $insertChild($belanja, 'Produk Simpel', 1, 'Halaman Produk');
        $insertChild($belanja, 'Produk Varian', 2, 'Halaman Produk');
        $insertChild($belanja, 'Produk Bundling', 3, 'Halaman Produk');

        // ── Toko (biasa) ──
        $toko = $insertParent('Toko', 'simple', 3);
        $insertChild($toko, 'Daftar Toko', 1);
        $insertChild($toko, 'Detail Toko', 2);
        $insertChild($toko, 'Toko Terdekat', 3);

        // ── Mega Menu (mega) ──
        $mega = $insertParent('Mega Menu', 'mega', 4);
        $insertChild($mega, 'Keripik', 1, 'Snack & Cemilan');
        $insertChild($mega, 'Biskuit', 2, 'Snack & Cemilan');
        $insertChild($mega, 'Cokelat', 3, 'Snack & Cemilan');
        $insertChild($mega, 'Beras', 1, 'Sembako');
        $insertChild($mega, 'Minyak Goreng', 2, 'Sembako');
        $insertChild($mega, 'Gula & Tepung', 3, 'Sembako');
        $insertChild($mega, 'Kopi & Teh', 1, 'Minuman');
        $insertChild($mega, 'Jus Kemasan', 2, 'Minuman');
        $insertChild($mega, 'Air Mineral', 3, 'Minuman');
        $insertChild($mega, 'Bayi & Anak', 1, 'Perawatan');
        $insertChild($mega, 'Kebersihan Rumah', 2, 'Perawatan');
        $insertChild($mega, 'Kesehatan', 3, 'Perawatan');

        // ── Halaman (biasa) ──
        $halaman = $insertParent('Halaman', 'simple', 5);
        $insertChild($halaman, 'Tentang Kami', 1);
        $insertChild($halaman, 'Layanan Pelanggan', 2);
        $insertChild($halaman, 'Karir', 3);
        $insertChild($halaman, 'Blog', 4);

        // ── Akun (biasa) ──
        $akun = $insertParent('Akun', 'simple', 6);
        $insertChild($akun, 'Masuk', 1, null, '/login');
        $insertChild($akun, 'Daftar', 2, null, '/register');
        $insertChild($akun, 'Riwayat Pesanan', 3);
        $insertChild($akun, 'Wishlist Saya', 4);

        // ── Dokumentasi (biasa, tanpa sub-menu) ──
        $insertParent('Dokumentasi', 'simple', 7);
    }

    public function down(): void
    {
        // delete() dulu, bukan truncate() — home_menus punya FK self-referencing (parent_id)
        DB::table('home_menus')->whereNotNull('parent_id')->delete();
        DB::table('home_menus')->delete();
    }
};
