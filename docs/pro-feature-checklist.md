# Checklist Implementasi Fitur Paket Pro

Daftar 77 fitur di katalog Kelola Paket Pro (`pro_features`), dicek terhadap kode sungguhan per 2026-08-16. Centang `[x]` kalau fitur sudah benar-benar diimplementasikan sesuai deskripsinya. Item yang masih `[ ]` diberi tag status dan alasan singkat.

Tag status:
- `[BELUM ADA]` — tidak ada kode yang cocok sama sekali
- `[SEBAGIAN]` — ada fungsionalitas terkait tapi tidak lengkap/tidak sesuai deskripsi

> **Catatan penting**: `ProPlan::hasFeature()` saat ini TIDAK dipanggil di mana pun selain modelnya sendiri — jadi status "sudah ada" di bawah ini artinya "fungsinya ada di aplikasi", BUKAN berarti fitur itu benar-benar terkunci di belakang paket berlangganan. Sampai sekarang satu-satunya pembatasan yang aktif adalah jumlah outlet (`max_outlet_types`) dan jumlah akun kasir (`max_kasir`) — **kecuali modul Laundry**: per 2026-08-16 sore, 6 dari 8 fiturnya sudah benar-benar dikunci ke paket lewat trait `EnforcesProFeature` (lihat bagian Laundry di bawah). 5 modul lain (Kafe, Warung Makan, Retail, Salon, Sewa) masih belum dikunci.

---

## Kafe & Warung Makan (modul `Fnb/*` — identik untuk keduanya)

- [x] Export Laporan ke Excel — `[SEBAGIAN]` hanya laporan penjualan yang bisa di-export, laporan stok belum
- [x] Laporan Lanjutan — `Fnb/ProfitLossController`
- [x] Akses Aplikasi Kasir Mobile — `[SEBAGIAN]` mobile API cuma dukung outlet mode "quick", mode "kitchen" ditolak
- [ ] Hapus Watermark di Struk — `[BELUM ADA]` tidak ada mekanisme watermark di struk sama sekali
- [x] Aktifkan Self-Order — `SelfOrderController` + rute QR publik
- [x] Mode Dapur (Kitchen) — `Outlet::order_mode`, `Fnb/OrderController`
- [x] Opening Stok Harian — `Fnb/OpeningStockController`
- [x] Tutup Hari (Daily Closing) — `Fnb/DailyClosingController`
- [x] Manajemen Stok Masuk — `Fnb/StockInController` + `StockViewController`
- [x] Catat Barang Rusak/Waste — `Fnb/WasteController`
- [x] Kelola Pengeluaran — `Fnb/ExpenseController`
- [x] Laporan Penjualan Detail — `[SEBAGIAN]` baru per produk & per hari, belum ada per kategori/per jam
- [ ] Notifikasi WA Pelanggan — `[BELUM ADA]` tidak ada integrasi WhatsApp Gateway di seluruh aplikasi
- [x] Atur Metode Pembayaran Sendiri — `[SEBAGIAN]` toggle tersimpan di Pengaturan, tapi POS mode "quick" masih hardcode 4 tombol pembayaran (toggle tidak berpengaruh)
- [x] Integrasi Midtrans Otomatis — `[SEBAGIAN]` cuma jalan untuk mode "kitchen"; mode "quick" bahkan tidak menerima metode `qris_pay`

## Retail

- [x] Export Laporan ke Excel — `[SEBAGIAN]` sales-only
- [x] Laporan Lanjutan — `Retail/ProfitLossController`
- [x] Akses Aplikasi Kasir Mobile — didukung penuh (tidak ada mode kitchen di retail)
- [ ] Hapus Watermark di Struk — `[BELUM ADA]`
- [x] Manajemen Harga Jual & HPP — `Retail/PriceController`, `ProductPriceHistory`
- [x] Manajemen Stok Masuk — `Retail/StockInController`
- [x] Catat Barang Rusak/Waste — `Retail/WasteController`
- [x] Kelola Pengeluaran — `Retail/ExpenseController`
- [x] Laporan Penjualan Detail — `[SEBAGIAN]` belum per kategori/per jam
- [ ] Notifikasi WA Pelanggan — `[BELUM ADA]`
- [x] Metode Pembayaran QRIS — `[SEBAGIAN]` toggle ada, POS tetap hardcode tombolnya
- [x] Metode Pembayaran Kartu — `[SEBAGIAN]` sama, toggle tidak dipakai di POS
- [x] Integrasi Midtrans Otomatis — `[SEBAGIAN]` konfigurasi ada, `MidtransService` tidak pernah dipanggil dari controller Retail

## Salon

- [x] Export Laporan ke Excel — `[SEBAGIAN]` sales-only
- [x] Laporan Lanjutan — `Salon/ProfitLossController`
- [x] Akses Aplikasi Kasir Mobile — didukung penuh
- [ ] Hapus Watermark di Struk — `[BELUM ADA]`
- [x] Manajemen Stok Masuk — `[SEBAGIAN]` kalau `track_cogs` outlet dimatikan (salon jasa murni), catatan stok tidak berpengaruh ke mana pun
- [x] Catat Barang Rusak/Waste — `Salon/WasteController`
- [x] Kelola Pengeluaran — `Salon/ExpenseController`
- [x] Laporan Penjualan Detail — `[SEBAGIAN]` belum per kategori/per jam
- [ ] Notifikasi WA Pelanggan — `[BELUM ADA]`
- [x] Atur Metode Pembayaran Sendiri — `[SEBAGIAN]` toggle tidak dipakai di POS
- [x] Integrasi Midtrans Otomatis — `[SEBAGIAN]` konfigurasi doang, tidak pernah dipanggil

## Laundry

Status implementasi (kolom lama) + status penguncian ke paket (kolom baru, dikerjakan 2026-08-16 sore lewat trait `App\Http\Controllers\Concerns\EnforcesProFeature`, dipakai di `SalesReportController`, `ProfitLossController`, `LaundryOrderController`, `SettingController` (web), dan `Api\V1\LaundryController` (mobile)). Admin sistem selalu bebas dari kunci ini. Diverifikasi lewat fixture `ZZTEST-*` di tinker: paket Free → semua `hasFeature()` `false`, paket Pro → 5 dari 6 fitur bisa dikunci jadi `true`, `midtrans` tetap `false` (eksklusif Max).

- [x] Export Laporan ke Excel — `[SEBAGIAN]` sales-only — **[TERKUNCI]** 403 di `SalesReportController::export()` kalau owner tidak punya `laundry_export_excel`
- [x] Laporan Lanjutan — `Laundry/ProfitLossController` — **[TERKUNCI]** 403 di `ProfitLossController::index()` kalau owner tidak punya `laundry_laporan_lanjutan`
- [x] Akses Aplikasi Kasir Mobile — API laundry khusus (`Api\V1\LaundryController`), paling lengkap dari semua modul — **[TERKUNCI]** 403 di keenam endpoint (`products`, `index`, `show`, `store`, `updateStatus`, `pay`) kalau owner tidak punya `laundry_akses_mobile`
- [ ] Hapus Watermark di Struk — `[BELUM ADA]` — **[TIDAK BISA DIKUNCI]** tidak ada mekanisme watermark di struk sama sekali untuk dikunci
- [x] Simpan Pelanggan Cepat — `Laundry/LaundryOrderController::store()` — **[TERKUNCI]** (revisi 2026-08-16 malam, sebelumnya silent-degrade). Tanpa `laundry_simpan_pelanggan_cepat`, pesanan HARUS untuk pelanggan yang sudah terdaftar (`customer_id` valid milik outlet) — kalau tidak ada/tidak valid, request ditolak 422 dengan pesan "tambahkan pelanggan ini dulu di menu Pelanggan". Owner tetap bisa order asal pelanggannya sudah ditambahkan manual lebih dulu di menu Pelanggan (menu itu sendiri tidak dikunci). Berlaku sama di web & mobile API.
- [ ] Notifikasi WA Pelanggan — `[BELUM ADA]` — **[TIDAK BISA DIKUNCI]** tidak ada integrasi WhatsApp Gateway sama sekali untuk dikunci
- [x] Atur Metode Pembayaran Sendiri — `[SEBAGIAN]` sudah dipakai betulan di alur bayar pesanan laundry (paling baik dari semua modul), tapi POS generik masih hardcode — **[TERKUNCI-SILENT]** di `SettingController::update()`, toggle `enable_qris_transfer`/`enable_transfer`/`enable_card` dipaksa `false` kalau owner tidak punya `laundry_payment_custom` (bukan 403 supaya toggle lain di halaman Pengaturan — mis. Opening Shift — tetap bisa disimpan)
- [x] Integrasi Midtrans Otomatis — `[SEBAGIAN]` konfigurasi ada, tidak pernah benar-benar men-charge lewat Midtrans — **[TERKUNCI-SILENT + disembunyikan dari owner]** (revisi 2026-08-16 malam). Card "Koneksi Midtrans" (input Server Key/Client Key/mode produksi) di halaman Pengaturan sekarang cuma tampil untuk admin sistem (`@if($isSystemAdmin)`) — owner tidak pernah melihat form-nya sama sekali. Di `SettingController::update()`, ketiga field itu HANYA bisa diubah kalau yang submit admin (dijaga di server juga, bukan cuma UI), dan `enable_qris_pay` tetap dipaksa `false` kalau owner tidak punya `laundry_midtrans` walau admin yang mencoba mengaktifkan. Jadi: owner dengan paket Max bisa "berhak" pakai Midtrans, tapi tetap admin yang harus login dan mengaktifkannya manual.

**Catatan UX yang belum dikerjakan (di luar cakupan sesi ini)**: sidebar & tombol aksi di halaman Laundry belum menyembunyikan/meredupkan menu yang terkunci — owner Free tetap melihat menu Laporan Laba & Rugi, tombol Export, dan checkbox "simpan pelanggan", baru dapat pesan 403 (atau silent no-op) setelah diklik/disubmit.

## Sewa/Rental

- [ ] Export Laporan ke Excel — `[BELUM ADA]` satu-satunya outlet type tanpa fungsi export sama sekali
- [x] Laporan Lanjutan — `Sewa/ProfitLossController`
- [ ] Akses Aplikasi Kasir Mobile — `[BELUM ADA]` tidak ada endpoint mobile API untuk Sewa
- [ ] Hapus Watermark di Struk — `[BELUM ADA]`
- [x] Booking/Sewa Baru — `Sewa/RentalController::create()/store()`
- [x] Perpanjangan Sewa — `Sewa/RentalController::extend()/storeExtension()`
- [x] Proses Pengembalian — `Sewa/RentalController::returns()/processReturn()`
- [x] Maintenance Unit — `Sewa/MaintenanceController`
- [x] Refund Deposit — `Sewa/RefundController`
- [x] Kelola Data Pelanggan — `Sewa/CustomerController` + `Sewa/DocumentController`
- [x] Pengaturan Lanjutan — `Sewa/SettingController`, `Sewa/ItemController`
- [x] Laporan Sewa Lengkap — `RevenueReportController`, `RentalReportController`, `ItemReportController`, `FineReportController`, `DepositReportController`
- [ ] Notifikasi WA Pelanggan — `[BELUM ADA]`
- [x] Atur Metode Pembayaran Sendiri — `[SEBAGIAN]` sudah dipakai betulan (lebih baik dari modul lain), cuma opsi "Kartu manual" belum ada padanannya
- [x] Integrasi Midtrans Otomatis — `[SEBAGIAN]` konfigurasi ada, tidak pernah dipanggil

*(Catatan: komentar lama di `routes/web.php` yang bilang rute Sewa "masih placeholder" sudah usang — cuma sub-menu Booking kalender yang masih `ComingSoonController` stub, modul intinya sendiri sudah lengkap.)*

---

## Temuan lintas-modul

- **Notifikasi WA Pelanggan** dan **Hapus Watermark di Struk** — 0% diimplementasikan di SEMUA 6 jenis outlet (11 dari 77 baris katalog).
- **Integrasi Midtrans** cuma benar-benar jalan (charge sungguhan) untuk Kafe/Warung Makan mode Kitchen. Modul lain cuma punya kolom setting kosong.
- **Toggle metode pembayaran custom** kebanyakan kosmetik — cuma benar-benar dipakai di mode Kitchen F&B, alur bayar Laundry, dan Sewa. POS generik mode "quick" (Fnb/Retail/Salon/Laundry) tetap hardcode 4 tombol pembayaran.
- **Export Excel** selalu sales-only di semua modul yang punya, dan sama sekali tidak ada untuk Sewa.
