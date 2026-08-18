<?php

namespace Database\Seeders;

use App\Models\ProFeature;
use App\Models\ProPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Katalog fitur & paket Pro. Data literal (bukan lagi didelegasikan ke
// controller admin) supaya seeder ini tetap bisa jalan sendiri dari kosong
// (migrate:fresh --seed) — sebelumnya sempat memanggil method di
// Admin\ProAdminController yang sudah dihapus saat controller itu disambungkan
// ke database sungguhan, sehingga fresh install akan gagal.
class ProSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $sortOrder = [];

        foreach ($this->features() as $data) {
            $sortOrder[$data['outlet_type']] = ($sortOrder[$data['outlet_type']] ?? -1) + 1;

            ProFeature::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'outlet_type' => $data['outlet_type'],
                    'name'        => $data['name'],
                    'description' => $data['desc'],
                    'sort_order'  => $sortOrder[$data['outlet_type']],
                ]
            );
        }

        foreach ($this->plans() as $index => $data) {
            $plan = ProPlan::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'             => $data['name'],
                    'description'      => $data['desc'],
                    'price'            => $data['price'],
                    'max_outlet_types' => $data['max_outlet_types'],
                    'max_kasir'        => $data['max_kasir'],
                    'is_active'        => $data['is_active'],
                    'is_default'       => $data['is_default'],
                    'sort_order'       => $index,
                ]
            );

            $featureIds = ProFeature::whereIn('slug', $data['features'])->pluck('id');
            $plan->features()->sync($featureIds);
        }
    }

    private function features(): array
    {
        $perType = [
            'kafe' => [
                ['slug' => 'export_excel', 'name' => 'Export Laporan ke Excel', 'desc' => 'Unduh laporan penjualan & stok outlet ini dalam format .xlsx'],
                ['slug' => 'laporan_lanjutan', 'name' => 'Laporan Lanjutan', 'desc' => 'Laba & Rugi, tren pendapatan, laporan per kategori'],
                ['slug' => 'akses_mobile', 'name' => 'Akses Aplikasi Kasir Mobile', 'desc' => 'Kelola outlet ini lewat aplikasi Android Pabalu Kasir'],
                ['slug' => 'hapus_watermark', 'name' => 'Hapus Watermark di Struk', 'desc' => 'Struk tidak menampilkan cap "Powered by Pabalu"'],
                ['slug' => 'self_order', 'name' => 'Aktifkan Self-Order', 'desc' => 'Pelanggan pesan sendiri lewat QR code di meja'],
                ['slug' => 'kitchen_mode', 'name' => 'Mode Dapur (Kitchen)', 'desc' => 'Order masuk dulu, diproses dapur/barista, baru checkout — terpisah dari POS cepat'],
                ['slug' => 'opening_stock', 'name' => 'Opening Stok Harian', 'desc' => 'Catat stok awal tiap hari sebelum outlet mulai berjualan'],
                ['slug' => 'closing_harian', 'name' => 'Tutup Hari (Daily Closing)', 'desc' => 'Tutup buku transaksi harian, rekap otomatis omset & stok akhir'],
                ['slug' => 'manajemen_stok', 'name' => 'Manajemen Stok Masuk', 'desc' => 'Catat stok masuk dari supplier dan lihat kartu stok per produk'],
                ['slug' => 'waste', 'name' => 'Catat Barang Rusak/Waste', 'desc' => 'Catat bahan/produk terbuang supaya tidak mengganggu laporan stok'],
                ['slug' => 'pengeluaran', 'name' => 'Kelola Pengeluaran', 'desc' => 'Catat pengeluaran operasional harian, otomatis masuk ke Laba & Rugi'],
                ['slug' => 'laporan_jual', 'name' => 'Laporan Penjualan Detail', 'desc' => 'Rincian penjualan per produk/kategori/jam, bukan cuma ringkasan'],
                ['slug' => 'wa', 'name' => 'Notifikasi WA Pelanggan', 'desc' => 'Kirim notifikasi pesanan siap/promo otomatis via WhatsApp Gateway'],
                ['slug' => 'payment_custom', 'name' => 'Atur Metode Pembayaran Sendiri', 'desc' => 'Aktif/nonaktifkan QRIS Transfer, Transfer Bank, Kartu manual (di luar Midtrans)'],
                ['slug' => 'midtrans', 'name' => 'Integrasi Midtrans Otomatis', 'desc' => 'QRIS/Kartu diproses otomatis lewat Midtrans, tanpa konfirmasi manual'],
            ],
            'warung_makan' => [
                ['slug' => 'export_excel', 'name' => 'Export Laporan ke Excel', 'desc' => 'Unduh laporan penjualan & stok outlet ini dalam format .xlsx'],
                ['slug' => 'laporan_lanjutan', 'name' => 'Laporan Lanjutan', 'desc' => 'Laba & Rugi, tren pendapatan, laporan per kategori'],
                ['slug' => 'akses_mobile', 'name' => 'Akses Aplikasi Kasir Mobile', 'desc' => 'Kelola outlet ini lewat aplikasi Android Pabalu Kasir'],
                ['slug' => 'hapus_watermark', 'name' => 'Hapus Watermark di Struk', 'desc' => 'Struk tidak menampilkan cap "Powered by Pabalu"'],
                ['slug' => 'self_order', 'name' => 'Aktifkan Self-Order', 'desc' => 'Pelanggan pesan sendiri lewat QR code di meja'],
                ['slug' => 'kitchen_mode', 'name' => 'Mode Dapur (Kitchen)', 'desc' => 'Order masuk dulu, diproses dapur, baru checkout — terpisah dari POS cepat'],
                ['slug' => 'opening_stock', 'name' => 'Opening Stok Harian', 'desc' => 'Catat stok awal tiap hari sebelum outlet mulai berjualan'],
                ['slug' => 'closing_harian', 'name' => 'Tutup Hari (Daily Closing)', 'desc' => 'Tutup buku transaksi harian, rekap otomatis omset & stok akhir'],
                ['slug' => 'manajemen_stok', 'name' => 'Manajemen Stok Masuk', 'desc' => 'Catat stok masuk dari supplier dan lihat kartu stok per bahan/produk'],
                ['slug' => 'waste', 'name' => 'Catat Barang Rusak/Waste', 'desc' => 'Catat bahan/produk terbuang supaya tidak mengganggu laporan stok'],
                ['slug' => 'pengeluaran', 'name' => 'Kelola Pengeluaran', 'desc' => 'Catat pengeluaran operasional harian, otomatis masuk ke Laba & Rugi'],
                ['slug' => 'laporan_jual', 'name' => 'Laporan Penjualan Detail', 'desc' => 'Rincian penjualan per produk/kategori/jam, bukan cuma ringkasan'],
                ['slug' => 'wa', 'name' => 'Notifikasi WA Pelanggan', 'desc' => 'Kirim notifikasi pesanan siap/promo otomatis via WhatsApp Gateway'],
                ['slug' => 'payment_custom', 'name' => 'Atur Metode Pembayaran Sendiri', 'desc' => 'Aktif/nonaktifkan QRIS Transfer, Transfer Bank, Kartu manual (di luar Midtrans)'],
                ['slug' => 'midtrans', 'name' => 'Integrasi Midtrans Otomatis', 'desc' => 'QRIS/Kartu diproses otomatis lewat Midtrans, tanpa konfirmasi manual'],
            ],
            'retail' => [
                ['slug' => 'export_excel', 'name' => 'Export Laporan ke Excel', 'desc' => 'Unduh laporan penjualan & stok outlet ini dalam format .xlsx'],
                ['slug' => 'laporan_lanjutan', 'name' => 'Laporan Lanjutan', 'desc' => 'Laba & Rugi, tren pendapatan, laporan per kategori'],
                ['slug' => 'akses_mobile', 'name' => 'Akses Aplikasi Kasir Mobile', 'desc' => 'Kelola outlet ini lewat aplikasi Android Pabalu Kasir'],
                ['slug' => 'hapus_watermark', 'name' => 'Hapus Watermark di Struk', 'desc' => 'Struk tidak menampilkan cap "Powered by Pabalu"'],
                ['slug' => 'harga_hpp', 'name' => 'Manajemen Harga Jual & HPP', 'desc' => 'Atur harga pokok (HPP) terpisah dari harga jual, pantau margin otomatis'],
                ['slug' => 'manajemen_stok', 'name' => 'Manajemen Stok Masuk', 'desc' => 'Catat stok masuk dari supplier dan lihat kartu stok per produk'],
                ['slug' => 'waste', 'name' => 'Catat Barang Rusak/Waste', 'desc' => 'Catat produk rusak/hilang supaya tidak mengganggu laporan stok'],
                ['slug' => 'pengeluaran', 'name' => 'Kelola Pengeluaran', 'desc' => 'Catat pengeluaran operasional harian, otomatis masuk ke Laba & Rugi'],
                ['slug' => 'laporan_jual', 'name' => 'Laporan Penjualan Detail', 'desc' => 'Rincian penjualan per produk/kategori/jam, bukan cuma ringkasan'],
                ['slug' => 'wa', 'name' => 'Notifikasi WA Pelanggan', 'desc' => 'Kirim struk digital & info promo otomatis via WhatsApp Gateway'],
                ['slug' => 'payment_qris', 'name' => 'Metode Pembayaran QRIS', 'desc' => 'Aktifkan opsi bayar QRIS (manual/transfer) di POS Retail'],
                ['slug' => 'payment_card', 'name' => 'Metode Pembayaran Kartu', 'desc' => 'Aktifkan opsi bayar kartu debit/kredit (manual) di POS Retail'],
                ['slug' => 'midtrans', 'name' => 'Integrasi Midtrans Otomatis', 'desc' => 'QRIS/Kartu diproses otomatis lewat Midtrans, tanpa konfirmasi manual'],
            ],
            'salon' => [
                ['slug' => 'export_excel', 'name' => 'Export Laporan ke Excel', 'desc' => 'Unduh laporan penjualan & stok outlet ini dalam format .xlsx'],
                ['slug' => 'laporan_lanjutan', 'name' => 'Laporan Lanjutan', 'desc' => 'Laba & Rugi, tren pendapatan, laporan per kategori'],
                ['slug' => 'akses_mobile', 'name' => 'Akses Aplikasi Kasir Mobile', 'desc' => 'Kelola outlet ini lewat aplikasi Android Pabalu Kasir'],
                ['slug' => 'hapus_watermark', 'name' => 'Hapus Watermark di Struk', 'desc' => 'Struk tidak menampilkan cap "Powered by Pabalu"'],
                ['slug' => 'manajemen_stok', 'name' => 'Manajemen Stok Masuk', 'desc' => 'Catat stok masuk produk perawatan/consumable dan lihat kartu stok'],
                ['slug' => 'waste', 'name' => 'Catat Barang Rusak/Waste', 'desc' => 'Catat produk rusak/kadaluarsa supaya tidak mengganggu laporan stok'],
                ['slug' => 'pengeluaran', 'name' => 'Kelola Pengeluaran', 'desc' => 'Catat pengeluaran operasional harian, otomatis masuk ke Laba & Rugi'],
                ['slug' => 'laporan_jual', 'name' => 'Laporan Penjualan Detail', 'desc' => 'Rincian penjualan per layanan/produk/jam, bukan cuma ringkasan'],
                ['slug' => 'wa', 'name' => 'Notifikasi WA Pelanggan', 'desc' => 'Kirim reminder & konfirmasi transaksi otomatis via WhatsApp Gateway'],
                ['slug' => 'payment_custom', 'name' => 'Atur Metode Pembayaran Sendiri', 'desc' => 'Aktif/nonaktifkan QRIS Transfer, Transfer Bank, Kartu manual (di luar Midtrans)'],
                ['slug' => 'midtrans', 'name' => 'Integrasi Midtrans Otomatis', 'desc' => 'QRIS/Kartu diproses otomatis lewat Midtrans, tanpa konfirmasi manual'],
            ],
            'laundry' => [
                ['slug' => 'export_excel', 'name' => 'Export Laporan ke Excel', 'desc' => 'Unduh laporan penjualan & stok outlet ini dalam format .xlsx'],
                ['slug' => 'laporan_lanjutan', 'name' => 'Laporan Lanjutan', 'desc' => 'Laba & Rugi, tren pendapatan, laporan per kategori'],
                ['slug' => 'akses_mobile', 'name' => 'Akses Aplikasi Kasir Mobile', 'desc' => 'Kelola outlet ini lewat aplikasi Android Pabalu Kasir'],
                ['slug' => 'hapus_watermark', 'name' => 'Hapus Watermark di Struk', 'desc' => 'Struk tidak menampilkan cap "Powered by Pabalu"'],
                ['slug' => 'simpan_pelanggan_cepat', 'name' => 'Simpan Pelanggan Cepat', 'desc' => 'Simpan data pelanggan baru otomatis langsung dari form Tambah Pesanan Baru, tanpa perlu buka menu Pelanggan'],
                ['slug' => 'wa', 'name' => 'Notifikasi WA Pelanggan', 'desc' => 'WhatsApp otomatis ke pelanggan saat status cucian berubah (diproses/selesai)'],
                ['slug' => 'payment_custom', 'name' => 'Atur Metode Pembayaran Sendiri', 'desc' => 'Aktif/nonaktifkan QRIS Transfer, Transfer Bank, Kartu manual (di luar Midtrans)'],
                ['slug' => 'midtrans', 'name' => 'Integrasi Midtrans Otomatis', 'desc' => 'QRIS/Kartu diproses otomatis lewat Midtrans, tanpa konfirmasi manual'],
            ],
            'sewa' => [
                ['slug' => 'export_excel', 'name' => 'Export Laporan ke Excel', 'desc' => 'Unduh laporan sewa & keuangan outlet ini dalam format .xlsx'],
                ['slug' => 'laporan_lanjutan', 'name' => 'Laporan Lanjutan', 'desc' => 'Laba & Rugi, tren pendapatan sewa'],
                ['slug' => 'akses_mobile', 'name' => 'Akses Aplikasi Kasir Mobile', 'desc' => 'Kelola outlet ini lewat aplikasi Android Pabalu Kasir'],
                ['slug' => 'hapus_watermark', 'name' => 'Hapus Watermark di Struk', 'desc' => 'Struk tidak menampilkan cap "Powered by Pabalu"'],
                ['slug' => 'booking', 'name' => 'Booking/Sewa Baru', 'desc' => 'Buat transaksi sewa baru — pilih unit, durasi, dan deposit'],
                ['slug' => 'perpanjangan', 'name' => 'Perpanjangan Sewa', 'desc' => 'Perpanjang durasi sewa yang sedang berjalan'],
                ['slug' => 'pengembalian', 'name' => 'Proses Pengembalian', 'desc' => 'Proses retur unit, hitung denda keterlambatan/kerusakan'],
                ['slug' => 'maintenance', 'name' => 'Maintenance Unit', 'desc' => 'Catat unit yang sedang perbaikan/perawatan supaya otomatis tidak bisa disewa'],
                ['slug' => 'refund', 'name' => 'Refund Deposit', 'desc' => 'Proses pengembalian deposit ke pelanggan'],
                ['slug' => 'kelola_pelanggan', 'name' => 'Kelola Data Pelanggan', 'desc' => 'Data & riwayat sewa pelanggan, termasuk verifikasi dokumen wajib'],
                ['slug' => 'pengaturan', 'name' => 'Pengaturan Lanjutan', 'desc' => 'Jenis sewa (per jam/hari/bulan), syarat dokumen, tarif, dan pengaturan lain per tab'],
                ['slug' => 'laporan', 'name' => 'Laporan Sewa Lengkap', 'desc' => 'Laporan rental, barang, denda, deposit, dan pendapatan'],
                ['slug' => 'wa', 'name' => 'Notifikasi WA Pelanggan', 'desc' => 'Reminder jatuh tempo sewa & konfirmasi booking otomatis via WhatsApp Gateway'],
                ['slug' => 'payment_custom', 'name' => 'Atur Metode Pembayaran Sendiri', 'desc' => 'Aktif/nonaktifkan QRIS Transfer, Transfer Bank, Kartu manual (di luar Midtrans)'],
                ['slug' => 'midtrans', 'name' => 'Integrasi Midtrans Otomatis', 'desc' => 'QRIS/Kartu diproses otomatis lewat Midtrans, tanpa konfirmasi manual'],
            ],
        ];

        $prefix = [
            'kafe' => 'kafe', 'warung_makan' => 'warmak', 'retail' => 'retail',
            'salon' => 'salon', 'laundry' => 'laundry', 'sewa' => 'sewa',
        ];

        $features = [];
        foreach ($perType as $outletType => $items) {
            foreach ($items as $item) {
                $features[] = [
                    'slug'        => $prefix[$outletType] . '_' . $item['slug'],
                    'outlet_type' => $outletType,
                    'name'        => $item['name'],
                    'desc'        => $item['desc'],
                ];
            }
        }

        return $features;
    }

    private function plans(): array
    {
        $proFeatures = [
            'kafe_akses_mobile', 'kafe_closing_harian', 'kafe_export_excel', 'kafe_kitchen_mode', 'kafe_laporan_jual',
            'kafe_laporan_lanjutan', 'kafe_manajemen_stok', 'kafe_opening_stock', 'kafe_payment_custom', 'kafe_pengeluaran',
            'kafe_self_order', 'kafe_wa', 'kafe_waste',
            'laundry_akses_mobile', 'laundry_export_excel', 'laundry_laporan_lanjutan', 'laundry_payment_custom',
            'laundry_simpan_pelanggan_cepat', 'laundry_wa',
            'retail_akses_mobile', 'retail_export_excel', 'retail_harga_hpp', 'retail_laporan_jual', 'retail_laporan_lanjutan',
            'retail_manajemen_stok', 'retail_payment_card', 'retail_payment_qris', 'retail_pengeluaran', 'retail_wa', 'retail_waste',
            'salon_akses_mobile', 'salon_export_excel', 'salon_laporan_jual', 'salon_laporan_lanjutan', 'salon_manajemen_stok',
            'salon_payment_custom', 'salon_pengeluaran', 'salon_wa', 'salon_waste',
            'warmak_akses_mobile', 'warmak_closing_harian', 'warmak_export_excel', 'warmak_kitchen_mode', 'warmak_laporan_jual',
            'warmak_laporan_lanjutan', 'warmak_manajemen_stok', 'warmak_opening_stock', 'warmak_payment_custom',
            'warmak_pengeluaran', 'warmak_self_order', 'warmak_wa', 'warmak_waste',
        ];

        $maxFeatures = array_values(array_unique(array_merge($proFeatures, [
            'kafe_hapus_watermark', 'kafe_midtrans',
            'laundry_hapus_watermark', 'laundry_midtrans',
            'retail_hapus_watermark', 'retail_midtrans',
            'salon_hapus_watermark', 'salon_midtrans',
            'warmak_hapus_watermark', 'warmak_midtrans',
            'sewa_akses_mobile', 'sewa_booking', 'sewa_export_excel', 'sewa_hapus_watermark', 'sewa_kelola_pelanggan',
            'sewa_laporan', 'sewa_laporan_lanjutan', 'sewa_maintenance', 'sewa_midtrans', 'sewa_payment_custom',
            'sewa_pengaturan', 'sewa_pengembalian', 'sewa_perpanjangan', 'sewa_refund', 'sewa_wa',
        ])));

        return [
            [
                'name'             => 'Free',
                'desc'             => 'Paket bawaan setiap owner baru — tanpa perlu kode.',
                'price'            => 0,
                'max_outlet_types' => 1,
                'max_kasir'        => 1,
                'is_active'        => true,
                'is_default'       => true,
                'features'         => [],
            ],
            [
                'name'             => 'Pro',
                'desc'             => 'Sampai 3 outlet (semua jenis), 1 akun kasir, fitur operasional per jenis outlet, pilih sendiri metode pembayaran (di luar Midtrans). Modul Sewa belum termasuk.',
                'price'            => 99000,
                'max_outlet_types' => 3,
                'max_kasir'        => 1,
                'is_active'        => true,
                'is_default'       => false,
                'features'         => $proFeatures,
            ],
            [
                'name'             => 'Max',
                'desc'             => 'Outlet & kasir tanpa batas, semua fitur Pro, integrasi Midtrans otomatis di semua jenis outlet, plus modul Sewa/Rental penuh.',
                'price'            => 199000,
                'max_outlet_types' => null,
                'max_kasir'        => null,
                'is_active'        => true,
                'is_default'       => false,
                'features'         => $maxFeatures,
            ],
        ];
    }
}
