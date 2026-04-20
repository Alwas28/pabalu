<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use League\CommonMark\CommonMarkConverter;

class GuideController extends Controller
{
    public function index(): View
    {
        $markdown = Setting::get('user_guide', '') ?: self::defaultGuide();
        $html     = '';

        $converter = new CommonMarkConverter([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $html = $converter->convert($markdown)->getContent();

        return view('guide.index', compact('html', 'markdown'));
    }

    private static function defaultGuide(): string
    {
        return <<<'MD'
# Panduan Penggunaan Sistem Pabalu

> **Pabalu** adalah sistem manajemen kasir dan operasional outlet berbasis web. Panduan ini disusun khusus untuk membantu **Owner**, **Kasir**, dan **Admin Outlet** menjalankan operasional harian dengan mudah dan efisien.

---

## A. MEMULAI

### Login ke Sistem

1. Buka browser dan akses alamat aplikasi Pabalu
2. Masukkan **Email** dan **Password** yang diberikan oleh Owner atau Admin
3. Klik **Masuk**

> Jika lupa password, hubungi Owner atau Admin untuk direset.

### Profil & Ganti Password

- Klik **nama akun** di pojok kanan atas sidebar
- Pilih **Profil** untuk mengubah nama, nomor HP, atau foto
- Pilih **Ganti Password** untuk memperbarui password

---

## B. DASHBOARD

Dashboard adalah halaman utama setelah login. Menampilkan ringkasan operasional hari ini:

- **Omzet** — total pendapatan dari transaksi selesai
- **Jumlah Transaksi** — berapa kali kasir melakukan penjualan
- **Total Pengeluaran** — biaya operasional yang sudah dicatat
- **Laba Kotor** — omzet dikurangi pengeluaran

**Owner** dapat melihat ringkasan dari semua outlet sekaligus dan membandingkan performa antar outlet.

---

## C. KASIR (POS)

Menu **Kasir** digunakan untuk melayani transaksi penjualan secara langsung (Point of Sale).

### Cara Melakukan Transaksi

1. Buka menu **Kasir** di sidebar
2. Pilih **Outlet** jika kamu mengelola lebih dari satu outlet
3. Cari produk menggunakan kolom pencarian, atau klik **kategori** untuk memfilter
4. Klik produk untuk menambahkan ke **Keranjang**
5. Sesuaikan jumlah (qty) menggunakan tombol **+** dan **−**
6. Pilih **Metode Pembayaran**:
   - **Tunai** — masukkan nominal uang diterima, kembalian otomatis dihitung
   - **QRIS** — tampilkan kode QR kepada pelanggan
   - **Transfer Bank** — catat konfirmasi transfer
   - **Payment Gateway** — bayar online via Midtrans (jika aktif)
7. Tambahkan **Keterangan** jika ada catatan khusus (opsional)
8. Klik **Proses Transaksi**
9. Struk otomatis tampil — klik **Cetak** untuk mencetak

### Tips POS

- Ketik nama produk di kolom pencarian, tekan **Enter** untuk langsung pilih produk pertama
- Klik tanda **×** pada item di keranjang untuk menghapus
- Produk yang stoknya **0** tidak akan muncul di daftar

---

## D. RIWAYAT TRANSAKSI

Halaman ini menampilkan semua transaksi yang pernah terjadi.

- Gunakan filter **tanggal**, **outlet**, atau **metode bayar** untuk mempersempit pencarian
- Klik **nomor transaksi** untuk melihat detail dan mencetak ulang struk
- Transaksi dengan label **Void** berarti sudah dibatalkan

---

## E. ORDER ONLINE (Antrian)

Pelanggan dapat memesan langsung dari meja menggunakan **QR Code** outlet tanpa perlu antri ke kasir.

### Alur Order Online

1. Pelanggan scan **QR Code** di meja atau pintu outlet
2. Pelanggan memilih menu dan mengisi nama serta nomor HP
3. Order masuk ke sistem dengan status **Menunggu**
4. Kasir melihat daftar order di menu **Antrian Order**
5. Klik **Proses** untuk mulai menyiapkan
6. Klik **Siap** setelah pesanan siap diambil
7. Klik **Selesai** setelah pelanggan mengambil pesanan

### Cetak QR Code Outlet

1. Buka menu **Kelola Outlet**
2. Klik ikon **QR Code** pada baris outlet yang diinginkan
3. Preview kartu QR akan muncul lengkap dengan nama outlet
4. Klik **Cetak QR** — tempelkan di meja atau pintu masuk

---

## F. STOK

Pabalu mencatat stok secara **otomatis** setiap transaksi berhasil. Selain itu tersedia pencatatan manual:

| Menu | Fungsi |
|------|--------|
| **Stok Awal (Opening)** | Input stok di awal hari atau saat pertama menggunakan sistem |
| **Tambah Stok** | Catat stok masuk dari pembelian bahan/produk baru |
| **Barang Rusak (Waste)** | Catat produk yang rusak, kadaluarsa, atau tidak bisa dijual |

### Cara Input Stok

1. Buka menu **Stok** → pilih jenis (Stok Awal / Tambah Stok / Waste)
2. Pilih **Outlet** dan **Tanggal**
3. Masukkan jumlah untuk setiap produk yang ingin dicatat
4. Klik **Simpan**

> **Penting:** Produk dengan stok **0** tidak muncul di POS dan tidak bisa dipesan online. Segera input stok jika ada produk yang kosong.

---

## G. PENGELUARAN

Catat semua biaya operasional harian di menu **Pengeluaran** agar laporan laba-rugi akurat.

### Contoh Pengeluaran yang Perlu Dicatat

- Belanja bahan baku dan perlengkapan
- Gaji karyawan harian/mingguan
- Biaya listrik, air, gas, internet
- Ongkos kebersihan dan perawatan
- Biaya lain-lain operasional

### Cara Mencatat Pengeluaran

1. Klik **+ Tambah Pengeluaran**
2. Isi **keterangan**, **jumlah**, **kategori**, dan **tanggal**
3. Upload **bukti** (foto struk/nota) jika ada — opsional
4. Klik **Simpan**

---

## H. CLOSING HARIAN

Closing adalah proses menutup operasional di akhir hari kerja. Lakukan setiap hari sebelum pulang.

### Langkah Closing

1. Buka menu **Closing Harian**
2. Periksa ringkasan hari ini:
   - Omzet dan jumlah transaksi
   - Total pengeluaran per kategori
   - Laba kotor
   - Rincian per metode pembayaran
3. Pastikan semua data sudah benar dan lengkap
4. Klik **Closing Sekarang**

> Closing hanya bisa dilakukan **satu kali per hari** dan **tidak bisa dibatalkan**. Pastikan semua transaksi dan pengeluaran sudah tercatat sebelum closing.

---

## I. KELOLA PRODUK

*(Khusus Owner & Admin)*

### Tambah Produk Baru

1. Buka **Kelola Produk** → klik **+ Tambah Produk**
2. Pilih **Outlet** dan **Kategori**
3. Isi **nama produk**, **kode produk** (opsional), dan **satuan** (porsi, pcs, gelas, dll)
4. Masukkan **harga jual**
5. Upload **foto produk** (opsional, maks. 2 MB — JPG/PNG/WEBP)
6. Aktifkan toggle **Produk Aktif** agar muncul di POS
7. Klik **Simpan Produk**

### Nonaktifkan / Edit Produk

- Klik ikon **pensil** pada baris produk untuk mengedit
- Nonaktifkan produk yang sementara tidak tersedia agar tidak muncul di POS
- Produk yang dihapus tidak bisa dikembalikan — lebih aman nonaktifkan

---

## J. KELOLA USER

*(Khusus Owner & Admin)*

### Tambah User Baru

1. Buka **Kelola User** → klik **+ Tambah User**
2. Isi **nama**, **email**, dan **password**
3. Pilih **Role** yang sesuai
4. Pilih **Outlet** yang ditugaskan *(wajib untuk Kasir)*
5. Isi jabatan jika perlu (opsional)
6. Klik **Simpan User**

### Deskripsi Role

| Role | Dapat Mengakses |
|------|----------------|
| **Admin** | Semua fitur termasuk pengaturan sistem, billing, dan role permission |
| **Owner** | Kelola outlet, produk, user, laporan, closing, dan billing |
| **Admin Outlet** | Operasional harian: POS, stok, pengeluaran, closing, laporan outlet |
| **Kasir** | POS, stok, pengeluaran, antrian order, dan riwayat transaksi |

> Kasir **hanya bisa** mengakses outlet yang sudah ditugaskan. Jika outlet nonaktif, kasir tidak bisa membuka POS.

---

## K. KELOLA OUTLET

*(Khusus Admin)*

Setiap outlet dapat dikonfigurasi dengan:

- **Nama**, **alamat**, **telepon**, dan **email** outlet
- **Status Aktif** — outlet nonaktif tidak bisa digunakan kasir
- **Owner** — assign owner yang bertanggung jawab
- **Payment Gateway** — aktifkan pembayaran online (diatur oleh Admin)

---

## L. LAPORAN

*(Khusus Owner & Admin)*

### Laporan Penjualan

Menampilkan data penjualan dalam rentang waktu tertentu:

- Omzet harian dan bulanan
- Produk terlaris berdasarkan qty dan pendapatan
- Perbandingan performa antar outlet *(Owner)*
- Breakdown per metode pembayaran

### Laporan Laba Rugi

Menampilkan perbandingan antara pemasukan dan pengeluaran:

- Total omzet vs total pengeluaran
- Laba kotor per periode
- Detail pengeluaran per kategori

> Gunakan filter **tanggal** dan **outlet** untuk mempersempit data laporan.

---

## M. PEMBAYARAN ONLINE

*(Diaktifkan oleh Admin, digunakan oleh Owner dan Kasir)*

Fitur ini memungkinkan pelanggan membayar pesanan online melalui **QRIS**, **transfer bank**, atau **dompet digital** via Midtrans.

- Owner dapat melihat status aktivasi di menu **Pembayaran Online**
- Jika belum aktif, ikuti langkah di halaman tersebut untuk menghubungi Admin
- Kasir tidak perlu melakukan konfigurasi — pembayaran diproses otomatis

---

## N. PENGATURAN SISTEM

*(Khusus Admin)*

| Menu | Fungsi |
|------|--------|
| **Umum** | Nama aplikasi, logo, zona waktu, format mata uang |
| **Struk** | Teks footer yang muncul di setiap struk transaksi |
| **Midtrans** | Konfigurasi server key & client key untuk payment gateway |
| **Billing** | Kelola tagihan dan paket langganan tiap Owner |

---

## O. TABEL HAK AKSES

| Fitur | Admin | Owner | Admin Outlet | Kasir |
|-------|:-----:|:-----:|:------------:|:-----:|
| POS / Kasir | ✓ | ✓ | ✓ | ✓ |
| Stok (Opening, Tambah, Waste) | ✓ | ✓ | ✓ | ✓ |
| Pengeluaran | ✓ | ✓ | ✓ | ✓ |
| Riwayat Transaksi | ✓ | ✓ | ✓ | ✓ |
| Antrian Order Online | ✓ | ✓ | ✓ | ✓ |
| Closing Harian | ✓ | ✓ | ✓ | — |
| Laporan Outlet | ✓ | ✓ | ✓ | — |
| Laporan Semua Outlet | ✓ | ✓ | — | — |
| Kelola Produk & Kategori | ✓ | ✓ | ✓ | — |
| Kelola User | ✓ | ✓ | — | — |
| Kelola Outlet | ✓ | ✓ | — | — |
| Pembayaran Online | ✓ | ✓ | — | — |
| Pengaturan Sistem | ✓ | — | — | — |
| Role & Permission | ✓ | ✓ | — | — |
| Billing & Tagihan | ✓ | ✓ | — | — |
| Dokumentasi API | ✓ | ✓ | — | — |

---

## P. PERTANYAAN UMUM (FAQ)

**Q: Produk saya tidak muncul di POS, kenapa?**
Kemungkinan stok produk tersebut = 0, atau produk dinonaktifkan. Cek di menu **Kelola Produk** dan **Stok**.

**Q: Saya tidak bisa login, apa yang harus dilakukan?**
Hubungi Owner atau Admin untuk reset password. Pastikan email yang dimasukkan benar.

**Q: Apakah data transaksi yang sudah Void bisa dipulihkan?**
Tidak. Void bersifat permanen. Jika terjadi kesalahan void, hubungi Admin untuk ditinjau melalui log aktivitas.

**Q: Closing sudah dilakukan tapi ada transaksi yang terlupa dicatat, bagaimana?**
Catat transaksi atau pengeluaran dengan tanggal yang benar. Data historis tetap bisa ditambahkan meski sudah melewati hari closing.

**Q: Apakah bisa melihat laporan outlet lain sebagai Kasir?**
Tidak. Kasir hanya bisa melihat data dari outlet yang ditugaskan kepadanya.

---

*Versi panduan ini dikelola oleh Admin sistem. Untuk pertanyaan lebih lanjut, hubungi Admin Pabalu.*
MD;
    }

    public function edit(): View
    {
        $content = Setting::get('user_guide', '') ?: self::defaultGuide();
        return view('guide.edit', compact('content'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['content' => ['nullable', 'string']]);

        Setting::set('user_guide', $request->input('content', ''));
        Cache::forget('setting:user_guide');

        ActivityLog::record('update_guide', 'Panduan penggunaan diperbarui.');

        return redirect()->route('guide.index')->with('success', 'Panduan berhasil disimpan.');
    }
}
