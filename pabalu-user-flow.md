# Pabalu — User Flow
> Referensi redesign sistem. Dibuat Juni 2026.

---

## DAFTAR ISI

1. [Peta Roles & Akses](#1-peta-roles--akses)
2. [Flow: Publik (Tanpa Login)](#2-flow-publik-tanpa-login)
3. [Flow: Onboarding Owner](#3-flow-onboarding-owner)
4. [Flow: Login & Masuk Sistem](#4-flow-login--masuk-sistem)
5. [Flow: Kasir — Operasional Harian](#5-flow-kasir--operasional-harian)
6. [Flow: POS (Point of Sale)](#6-flow-pos-point-of-sale)
7. [Flow: Order Online](#7-flow-order-online)
8. [Flow: Manajemen Stok](#8-flow-manajemen-stok)
9. [Flow: Pengeluaran](#9-flow-pengeluaran)
10. [Flow: Closing Harian](#10-flow-closing-harian)
11. [Flow: Owner — Setup & Konfigurasi](#11-flow-owner--setup--konfigurasi)
12. [Flow: Laporan](#12-flow-laporan)
13. [Flow: Admin — Kelola Sistem](#13-flow-admin--kelola-sistem)
14. [Flow: Billing & Subscription](#14-flow-billing--subscription)
15. [Diagram Alur Jenis Outlet](#15-diagram-alur-jenis-outlet)
16. [Peta Navigasi Sidebar per Role](#16-peta-navigasi-sidebar-per-role)

---

## 1. PETA ROLES & AKSES

```
┌─────────────────────────────────────────────────────────────────┐
│                         ROLES PABALU                            │
├───────────────┬─────────────────────────────────────────────────┤
│  admin        │  Semua akses. Kelola owner, billing, RBAC,      │
│               │  settings, jenis outlet.                        │
├───────────────┼─────────────────────────────────────────────────┤
│  owner        │  Kelola outlet miliknya, produk, kategori,      │
│               │  user, laporan, billing aplikasi sendiri.       │
├───────────────┼─────────────────────────────────────────────────┤
│  admin_outlet │  Operasional harian + laporan outlet sendiri.   │
│               │  Tidak bisa kelola user atau billing.           │
├───────────────┼─────────────────────────────────────────────────┤
│  kasir        │  POS, stok harian, pengeluaran, antrian order.  │
│               │  Terkunci ke 1 outlet yang di-assign.          │
└───────────────┴─────────────────────────────────────────────────┘
```

---

## 2. FLOW: PUBLIK (TANPA LOGIN)

### 2A. Landing Page

```
Pengunjung masuk ke /
  ├── Lihat daftar outlet aktif
  ├── Klik "Daftar Sekarang" ──► Flow Onboarding Owner
  └── Klik "Masuk" ──► Halaman Login
```

### 2B. Order Online (Pelanggan)

```
Pelanggan scan QR Code outlet
       │
       ▼
  /order/{slug}
  ┌──────────────────────────────────┐
  │  Halaman Menu Publik             │
  │  - Lihat daftar produk aktif     │
  │  - Isi nama & nomor telepon      │
  │  - Tambah produk ke keranjang    │
  └──────────────────────────────────┘
       │
       ▼ Submit order
  Validasi stok tersedia?
  ├── TIDAK ──► Pesan error "stok habis"
  └── YA   ──► Order tersimpan (status: pending)
                     │
                     ▼
              /order/status/{nomor}
              ┌────────────────────────────┐
              │  Halaman Tracking Status   │
              │  (auto-refresh tiap 10 dt) │
              │                            │
              │  pending                   │
              │     │                      │
              │     ▼                      │
              │  processing                │
              │     │                      │
              │     ▼                      │
              │  ready ◄── kasir siapkan   │
              │     │                      │
              │     ▼                      │
              │  completed                 │
              └────────────────────────────┘
```

---

## 3. FLOW: ONBOARDING OWNER

```
/daftar (hanya untuk tamu, belum login)
  │
  ▼
Isi Form Registrasi
  ├── Nama lengkap
  ├── Email
  ├── Nama bisnis
  └── Password
       │
       ▼
Kirim email verifikasi
       │
       ▼
/verify-email
  ├── Klik link di email ──► Akun terverifikasi
  └── Belum dapat email ──► Kirim ulang
       │
       ▼
Login → Dashboard
  │
  ▼
Status akun: TRIAL (14 hari atau sesuai setting admin)
  │
  ▼
CHECKLIST SETUP PERTAMA:
  │
  ├── 1. Buat Outlet (/outlets/create)
  │         ├── Isi nama, alamat, telepon, email
  │         └── Pilih JENIS OUTLET (dari daftar):
  │               ┌─────────────────────────────────────────┐
  │               │  Jenis Outlet (DB-driven, bisa ditambah) │
  │               ├──────────────┬──────────────────────────┤
  │               │  F&B/Warung  │  Opening stok harian ✓   │
  │               │              │  Harga beli ✗             │
  │               ├──────────────┼──────────────────────────┤
  │               │  Retail      │  Opening stok harian ✗   │
  │               │              │  Harga beli ✓             │
  │               ├──────────────┼──────────────────────────┤
  │               │  Custom      │  Sesuai konfigurasi admin │
  │               └──────────────┴──────────────────────────┘
  │
  ├── 2. Buat Kategori (/categories/create)
  │         └── Nama kategori (cth: Makanan, Minuman, Snack)
  │
  ├── 3. Tambah Produk (/products/create)
  │         ├── Pilih outlet, kategori, nama, satuan
  │         ├── Harga jual (wajib)
  │         ├── Harga beli (muncul jika outlet track_cogs=true)
  │         └── Upload foto (opsional)
  │
  └── 4. Tambah Kasir (/users/create)
            ├── Isi nama, email, password
            ├── Pilih role: kasir / admin_outlet
            └── Assign ke outlet
```

---

## 4. FLOW: LOGIN & MASUK SISTEM

```
/login
  │
  ▼
Masukkan email + password
  │
  ▼
Validasi akun
  ├── Email belum diverifikasi ──► /verify-email
  ├── Akun suspended/trial habis ──► /account-suspended
  │         └── Klik "Bayar Tagihan" ──► Flow Billing
  └── Berhasil ──► /dashboard
                      │
                      ▼
               Dashboard tampil berbeda per role:
               ┌──────────────────────────────────────────┐
               │  admin       │ ringkasan global sistem    │
               │  owner       │ ringkasan semua outletnya  │
               │  admin_outlet│ ringkasan outlet sendiri   │
               │  kasir       │ shortcut operasional cepat │
               └──────────────────────────────────────────┘
```

---

## 5. FLOW: KASIR — OPERASIONAL HARIAN

Alur berbeda tergantung jenis outlet yang di-assign ke kasir.

### 5A. Outlet dengan Opening Stok Harian (requires_opening_stock = true)

```
  PAGI
  ──────────────────────────────────
       ▼
  /opening — Input Stok Awal
  ┌─────────────────────────────────┐
  │  List semua produk aktif        │
  │  Kasir isi qty stok awal        │
  │  tiap produk hari ini           │
  └─────────────────────────────────┘
       │ Simpan
       ▼
  Stok hari ini ter-set ✓

  SIANG / SORE
  ──────────────────────────────────
       ▼
  ┌────────────────────────────────────────────────┐
  │  Pilih aktivitas:                              │
  │                                                │
  │  [POS]        transaksi penjualan              │
  │  [Order]      terima order online masuk        │
  │  [Stok Masuk] restock dari supplier            │
  │  [Waste]      catat barang rusak/expired       │
  │  [Pengeluaran]catat biaya operasional harian   │
  └────────────────────────────────────────────────┘

  MALAM
  ──────────────────────────────────
       ▼
  /closing — Closing Harian
  ┌─────────────────────────────────┐
  │  Ringkasan hari ini:            │
  │  - Total omzet                  │
  │  - Total transaksi              │
  │  - Total pengeluaran            │
  │  - Estimasi laba bersih         │
  └─────────────────────────────────┘
```

### 5B. Outlet tanpa Opening Stok Harian (requires_opening_stock = false)

```
  KAPAN SAJA (tidak ada urutan wajib)
  ──────────────────────────────────────────────────
  ┌────────────────────────────────────────────────┐
  │  Pilih aktivitas:                              │
  │                                                │
  │  [POS]        transaksi penjualan              │
  │  [Order]      terima order online masuk        │
  │  [Stok Masuk] tambah stok dari supplier        │
  │  [Waste]      catat barang rusak               │
  │  [Pengeluaran]catat biaya operasional          │
  └────────────────────────────────────────────────┘
  (Tidak ada menu Opening & Closing)
```

---

## 6. FLOW: POS (POINT OF SALE)

```
/transactions/pos
  │
  ▼
  ┌─────────────────────────────────────────────────────┐
  │  LAYAR POS                                          │
  │                                                     │
  │  [Daftar Produk]          [Keranjang]               │
  │  - Grid produk aktif      - Item + qty              │
  │  - Filter kategori        - Subtotal per item       │
  │  - Search produk          - Total keseluruhan       │
  │  - Info stok realtime     - Tombol Bayar            │
  └─────────────────────────────────────────────────────┘
       │ Klik produk
       ▼
  Masuk keranjang (qty +1, bisa ubah qty manual)
       │ Klik Bayar
       ▼
  Pilih Metode Pembayaran:
  │
  ├── TUNAI
  │     ├── Input nominal bayar
  │     ├── Tampil kembalian otomatis
  │     └── Konfirmasi → Transaksi tersimpan (paid)
  │
  ├── QRIS / TRANSFER
  │     ├── Upload bukti pembayaran
  │     └── Konfirmasi → Transaksi tersimpan (paid)
  │
  └── PAYMENT GATEWAY (Midtrans)
        ├── Sistem generate Snap Token
        ├── Modal Midtrans terbuka (QRIS, VA Bank, dll)
        ├── Pelanggan bayar
        ├── Webhook callback → status paid
        └── Transaksi tersimpan otomatis
              │
              ▼
        /transactions/{id} — STRUK
        ┌──────────────────────────┐
        │  Nomor transaksi         │
        │  Tanggal & waktu         │
        │  Item + qty + harga      │
        │  Total & metode bayar    │
        │  Kasir yang melayani     │
        └──────────────────────────┘
              │
              └── Void (batalkan) ──► permission: transaction.void
```

---

## 7. FLOW: ORDER ONLINE

### Sisi Admin/Kasir

```
/orders — Antrian Order Online
  │
  ▼
  ┌──────────────────────────────────────────────┐
  │  DASHBOARD ANTRIAN                           │
  │  - Badge merah counter order pending         │
  │  - List order beserta nama, nomor HP, item   │
  │  - Auto-refresh (polling tiap ~15 detik)     │
  └──────────────────────────────────────────────┘
       │
       ▼
  Aksi per order:
  │
  ├── ADVANCE STATUS (urutan wajib):
  │     pending ──► processing ──► ready ──► completed
  │     (setiap advance, pelanggan melihat update di halaman tracking)
  │
  └── CANCEL
        └── Order dibatalkan, stok tidak berkurang
```

---

## 8. FLOW: MANAJEMEN STOK

### 8A. Lihat Stok

```
/stock — Ringkasan Stok
  │
  ├── Outlet requires_opening_stock = true:
  │     Filter tanggal aktif | Kolom "Opening" tampil
  │     Stok = opening + masuk − waste − terjual (per hari)
  │
  └── Outlet requires_opening_stock = false:
        Tidak ada filter tanggal | Stok kumulatif
        Stok = total masuk − waste − terjual (sepanjang waktu)
```

### 8B. Input Stok Masuk

```
/stock/in
  │
  ▼
  ┌─────────────────────────────┐
  │  Pilih produk               │
  │  Input qty masuk            │
  │  Catatan (opsional)         │
  │  Tanggal (default: hari ini)│
  └─────────────────────────────┘
       │ Simpan
       ▼
  Stok bertambah ✓
  Tercatat di /stock/in/history
```

### 8C. Input Waste

```
/stock/waste
  │
  ▼
  ┌─────────────────────────────┐
  │  Pilih produk               │
  │  Input qty waste            │
  │  Alasan / catatan           │
  └─────────────────────────────┘
       │ Simpan
       ▼
  Stok berkurang ✓
  Tercatat di /stock/waste/history
```

### 8D. Opening Stok (hanya outlet yang requires_opening_stock = true)

```
/opening
  │
  ▼
  ┌──────────────────────────────────────────────┐
  │  List semua produk aktif outlet hari ini     │
  │  Stok kemarin ditampilkan sebagai referensi  │
  │  Kasir isi stok awal hari ini per produk     │
  └──────────────────────────────────────────────┘
       │ Simpan Semua
       ▼
  Opening stok tercatat ✓
  Dasar perhitungan stok hari ini
```

---

## 9. FLOW: PENGELUARAN

```
/expenses — Daftar Pengeluaran
  │
  ▼
  ┌──────────────────────────────────┐
  │  Filter: tanggal, outlet         │
  │  Daftar pengeluaran hari ini     │
  │  Total pengeluaran harian        │
  └──────────────────────────────────┘
       │
       ▼
  Tambah Pengeluaran (form inline atau modal):
  ┌────────────────────────────────────┐
  │  Nama / keterangan                 │
  │  Kategori (listrik, gaji, dll)     │
  │  Nominal                           │
  │  Tanggal                           │
  └────────────────────────────────────┘
       │ Simpan
       ▼
  Masuk daftar ✓ | Bisa diedit / dihapus
```

---

## 10. FLOW: CLOSING HARIAN

> Hanya tersedia untuk outlet dengan `requires_opening_stock = true`

```
/closing
  │
  ▼
  ┌──────────────────────────────────────────────────┐
  │  RINGKASAN CLOSING HARI INI                      │
  │                                                  │
  │  Omzet total          Rp xxxxxxxxx               │
  │  Total transaksi      xx transaksi               │
  │  Total pengeluaran    Rp xxxxxxxxx               │
  │  HPP (estimasi)       Rp xxxxxxxxx               │
  │  ─────────────────────────────────               │
  │  Laba Bersih (est.)   Rp xxxxxxxxx               │
  │                                                  │
  │  Breakdown per jam / per metode bayar            │
  └──────────────────────────────────────────────────┘

  (Halaman ini bersifat READ-ONLY — tidak ada aksi simpan)
```

---

## 11. FLOW: OWNER — SETUP & KONFIGURASI

```
Owner login → Dashboard → Menu Manajemen
  │
  ├── KELOLA OUTLET (/outlets)
  │     ├── Lihat daftar outlet miliknya
  │     ├── Buat outlet baru
  │     │     └── Pilih jenis outlet → behavior ditentukan otomatis
  │     └── Edit outlet (nama, kontak, jenis, aktif/nonaktif)
  │
  ├── KELOLA PRODUK (/products)
  │     ├── Filter per outlet / kategori / status
  │     ├── Tambah produk
  │     │     ├── Harga beli muncul jika outlet track_cogs=true
  │     │     └── Upload foto produk
  │     └── Edit / nonaktifkan produk
  │
  ├── KELOLA KATEGORI (/categories)
  │     └── CRUD kategori produk
  │
  ├── KELOLA USER (/users)
  │     ├── Tambah kasir / admin_outlet
  │     ├── Assign ke outlet
  │     └── Reset password / nonaktifkan
  │
  ├── METODE PEMBAYARAN (/owner/payment-methods)
  │     └── Toggle aktif/nonaktif: Tunai | QRIS | Transfer | Gateway
  │
  ├── PEMBAYARAN ONLINE (/owner/payment-settings)
  │     └── Input Midtrans Server Key & Client Key
  │
  └── BILLING APLIKASI (/billing)
        └── Lihat & bayar tagihan Pabalu
```

---

## 12. FLOW: LAPORAN

> Tersedia untuk Owner dan Admin Outlet

```
Menu Laporan
  │
  ├── LAPORAN PENJUALAN (/reports/sales)
  │     ┌────────────────────────────────────────────────┐
  │     │  Filter: tanggal awal–akhir, outlet            │
  │     │  Tampil:                                       │
  │     │  - Total omzet periode                         │
  │     │  - Jumlah transaksi                            │
  │     │  - Top produk terlaris                         │
  │     │  - Breakdown per hari                          │
  │     │  - Breakdown per metode bayar                  │
  │     └────────────────────────────────────────────────┘
  │
  ├── LAPORAN STOK (/reports/stock)
  │     ┌────────────────────────────────────────────────┐
  │     │  Filter: tanggal, outlet                       │
  │     │  Tampil:                                       │
  │     │  - Stok awal, masuk, terjual, waste, sisa      │
  │     │  - Per produk                                  │
  │     └────────────────────────────────────────────────┘
  │
  └── LABA & RUGI (/reports/profit-loss)
        ┌────────────────────────────────────────────────┐
        │  Filter: tanggal awal–akhir, outlet            │
        │  Tampil:                                       │
        │  - Omzet                                       │
        │  - HPP (harga beli × qty terjual)              │
        │  - Gross profit                                │
        │  - Total pengeluaran                           │
        │  - Laba bersih                                 │
        │  - Margin %                                    │
        │  - Breakdown per hari                          │
        └────────────────────────────────────────────────┘
```

---

## 13. FLOW: ADMIN — KELOLA SISTEM

```
Admin login → Dashboard global

├── KELOLA OWNER (/admin/owner-accounts)
│     ├── Lihat semua akun owner + status (trial/premium/inactive)
│     ├── Set Trial: perpanjang trial X hari
│     ├── Set Premium: akun tidak perlu bayar
│     ├── Deactivate / Activate: manual suspend
│     └── Payment Settings: input Midtrans credentials untuk owner
│
├── KELOLA TAGIHAN (/admin/billing)
│     ├── Buat invoice baru untuk owner
│     ├── Cancel invoice
│     └── Hapus invoice
│
├── JENIS OUTLET (/outlet-types)          ← konfigurasi sistem
│     ┌──────────────────────────────────────────────────────────┐
│     │  Daftar jenis outlet yang bisa dipakai di semua outlet   │
│     │  Setiap jenis punya 2 flag behavior:                     │
│     │                                                          │
│     │  ☐ requires_opening_stock                                │
│     │       true  → kasir wajib opening + closing harian       │
│     │               stok dihitung per hari                     │
│     │       false → tidak ada opening/closing                  │
│     │               stok kumulatif sepanjang waktu             │
│     │                                                          │
│     │  ☐ track_cogs                                            │
│     │       true  → field harga beli muncul di form produk     │
│     │       false → field harga beli disembunyikan             │
│     └──────────────────────────────────────────────────────────┘
│     ├── Tambah jenis baru (nama, slug, icon, deskripsi, flag)
│     ├── Edit jenis yang ada
│     └── Hapus (hanya jika tidak dipakai outlet manapun)
│
├── ROLE & PERMISSION (/rbac/roles)
│     ├── Lihat daftar role
│     ├── Buat role baru + pilih permission (40+ permission, 14 grup)
│     └── Edit / hapus role
│
├── KELOLA USER (/users)
│     └── CRUD semua user lintas owner
│
├── KELOLA OUTLET (/outlets)
│     └── CRUD semua outlet lintas owner
│
├── PENGATURAN SISTEM (/settings)
│     └── billing_grace_period, nama aplikasi, dll
│
├── LOG AKTIVITAS (/activity-logs)
│     └── Audit trail semua aksi user (filter: user, aksi, tanggal)
│
└── PANDUAN (/guide)
      └── Edit konten panduan (markdown)
```

---

## 14. FLOW: BILLING & SUBSCRIPTION

### 14A. Sisi Owner (Bayar Tagihan)

```
Status akun: TRIAL
  │
  ├── Trial aktif ──► Semua fitur bisa digunakan
  │
  └── Trial habis ──► Redirect ke /account-suspended
                            │
                            ▼
                     /billing — Halaman Tagihan
                     ┌─────────────────────────────────┐
                     │  Invoice yang perlu dibayar      │
                     │  Nominal, due date, status       │
                     └─────────────────────────────────┘
                            │ Klik Bayar
                            ▼
                     Modal Midtrans Snap
                     (QRIS, Virtual Account bank, dll)
                            │ Bayar berhasil
                            ▼
                     Status: PAID ✓
                     Akun kembali aktif
```

### 14B. Sisi Admin (Kelola Tagihan)

```
/admin/billing
  │
  ├── Buat invoice untuk owner
  │     └── Pilih owner, nominal, tanggal jatuh tempo
  │
  ├── Cancel invoice (sebelum dibayar)
  │
  └── Auto-suspend berjalan otomatis:
        php artisan billing:check-due (setiap 01:00)
        Jika invoice lewat due_date → akun owner di-suspend
```

---

## 15. DIAGRAM ALUR JENIS OUTLET

```
Admin tambah jenis outlet baru
(/outlet-types/create)
  │
  ▼
Isi konfigurasi:
  Nama: "Apotek"
  Slug: apotek
  Icon: fa-prescription-bottle
  ☑ Opening Stok Harian
  ☑ Tracking Harga Beli
  │
  ▼
Jenis "Apotek" tersedia di dropdown saat buat outlet
  │
  ▼
Owner buat outlet baru → pilih "Apotek"
  │
  ▼
Kasir outlet Apotek mendapat alur:

  ┌─────────────────────────────────────────────┐
  │  requires_opening_stock = TRUE              │
  │  → Menu "Opening Stok" muncul di sidebar    │
  │  → Menu "Closing Harian" muncul di sidebar  │
  │  → Stok dihitung per hari                   │
  ├─────────────────────────────────────────────┤
  │  track_cogs = TRUE                          │
  │  → Field "Harga Beli" muncul di form produk │
  │  → Laporan Laba Rugi menampilkan HPP        │
  └─────────────────────────────────────────────┘


Contoh matriks kombinasi flag:

  ┌──────────────────┬──────────────┬─────────────┬──────────────────────────┐
  │ Jenis Outlet     │ Req. Opening │ Track COGS  │ Contoh Bisnis            │
  ├──────────────────┼──────────────┼─────────────┼──────────────────────────┤
  │ F&B / Warung     │      ✓       │      ✗      │ Restoran, kafe, warung   │
  │ Retail           │      ✗       │      ✓      │ Minimarket, sembako      │
  │ Toko Bangunan    │      ✗       │      ✓      │ Material, hardware       │
  │ Toko Pakaian     │      ✗       │      ✓      │ Butik, distro            │
  │ Apotek           │      ✓       │      ✓      │ Apotek, toko obat        │
  │ Produksi         │      ✓       │      ✓      │ Home industry, catering  │
  └──────────────────┴──────────────┴─────────────┴──────────────────────────┘
```

---

## 16. PETA NAVIGASI SIDEBAR PER ROLE

```
SEMUA ROLE (setelah login)
  └── Dashboard

OPERASIONAL HARIAN
  ├── Opening Stok        [kasir, admin_outlet]  ← hanya jika outlet requires_opening=true
  ├── POS / Kasir         [kasir, admin_outlet]
  ├── Antrian Order       [kasir, admin_outlet]
  ├── Tambah Stok         [kasir, admin_outlet]
  ├── Pengeluaran         [kasir, admin_outlet]
  ├── Waste / Barang Rusak[kasir, admin_outlet]
  └── Closing Harian      [kasir, admin_outlet]  ← hanya jika outlet requires_opening=true

TRANSAKSI
  └── Riwayat Transaksi   [semua role]

PRODUK & STOK
  ├── Produk              [owner, admin_outlet]
  ├── Kategori            [owner, admin_outlet]
  └── Stok & Pergerakan   [semua role]

LAPORAN
  ├── Laporan Penjualan   [owner, admin_outlet]
  ├── Laporan Stok        [owner, admin_outlet]
  └── Laba & Rugi         [owner, admin_outlet]

MANAJEMEN
  ├── Kelola Outlet       [owner, admin]
  ├── Jenis Outlet        [admin]              ← konfigurasi sistem
  ├── Role & Permission   [admin]
  └── Kelola User         [owner, admin]

BANTUAN
  ├── Panduan Penggunaan  [semua role]
  ├── Dokumentasi API     [admin]
  └── Tagihan Aplikasi    [owner]

PENGATURAN
  ├── Akun Saya           [semua role]
  ├── Akun Owner          [admin]
  ├── Log Aktivitas       [admin]
  ├── Pengaturan Sistem   [admin]
  └── Metode Pembayaran   [owner]
```

---

## RINGKASAN KEPUTUSAN DESAIN PENTING

| # | Keputusan | Alasan |
|---|-----------|--------|
| 1 | Jenis outlet **DB-driven** (bukan hardcode) | Admin bisa tambah jenis baru tanpa deploy ulang |
| 2 | Opening/Closing **hanya muncul** jika flag aktif | Kasir toko bangunan tidak perlu lihat menu yang tidak relevan |
| 3 | Harga beli **tersembunyi** jika track_cogs=false | Menyederhanakan form untuk bisnis yang tidak perlu tracking HPP |
| 4 | Stok **tidak bisa diedit manual** | Integritas data — hanya lewat opening/in/waste/transaksi |
| 5 | Feature flags untuk **mobile** dari API `/me` | Mobile tidak hardcode logika role, cukup baca flag dari server |
| 6 | **Kasir terkunci** ke 1 outlet | Keamanan operasional — kasir tidak bisa akses data outlet lain |
| 7 | Trial expired **redirect ke billing** | Akses tetap ada untuk bayar — tidak totally locked out |
| 8 | Order online **tanpa login** pelanggan | Friction minimal, cukup nama + telepon |
