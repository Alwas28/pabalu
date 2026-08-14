<!--
CATATAN: File ini dibuat di repo backend (Laravel) sebagai draf handoff.
Pindahkan/copy file ini ke root proyek React Native sebagai `CLAUDE.md`
begitu proyeknya dibuat — Claude Code otomatis membaca CLAUDE.md di root
setiap kali sesi baru dibuka di folder itu.
-->

# Pabalu Kasir — Aplikasi Android Kasir (React Native)

## Konteks proyek

Ini adalah aplikasi kasir Android yang mengonsumsi API dari backend Laravel
**Pabalu2** (POS multi-jenis-outlet untuk UMKM, lokasi: `e:\Project\pabalu2`,
repo terpisah dari proyek ini). Developer sudah lebih familiar React Native
dibanding Flutter — itu sebabnya RN dipilih meski printer thermal biasanya
lebih mudah di Flutter.

## Keputusan yang sudah diambil (jangan diubah tanpa diskusi ulang)

- **Satu aplikasi untuk semua jenis outlet** (F&B, Retail, Salon, Laundry,
  Sewa/Rental) — bukan aplikasi terpisah per jenis. UI/alur menyesuaikan
  berdasarkan `outlet_type`/`route_prefix` yang dikembalikan API.
- **React Native**, bukan Flutter — familiaritas developer, meski ekosistem
  library printer RN lebih terpecah/kurang terawat dibanding Flutter. Cek dulu
  paket mana yang masih aktif dipelihara sebelum pasang (`react-native-thermal-printer`,
  `react-native-bluetooth-escpos-printer`, dll — validasi versi/maintenance
  terbaru saat mulai, jangan asumsikan dari catatan ini).
- **Printer thermal via Bluetooth** adalah kebutuhan utama (ESC/POS). Ini yang
  menyebabkan pilihan native (RN) dan bukan WebView/PWA — Web Bluetooth API di
  Android tidak cukup andal untuk printer POS.
- **Auth pakai token Sanctum (Bearer)**, bukan session/cookie. Endpoint login
  ada rate limit 10x/menit.
- Backend dan aplikasi mobile adalah **dua repo/folder terpisah** — jangan
  digabung. Kalau butuh ubah/tambah endpoint API, itu dikerjakan di repo
  Laravel (`e:\Project\pabalu2`), bukan di sini.

## Status API backend (per 2026-08-12)

**Sudah selesai dibangun & diuji:** F&B (mode `quick` saja), Retail, Salon
(ketiganya lewat endpoint Kasir biasa: `categories`/`products`/`transactions`),
dan Laundry (lewat endpoint order bertahap `laundry/*` — BEDA BENTUK dari
Kasir biasa, jangan disamakan di UI). Cek field `route_prefix`+`order_mode` dari
`GET /outlets` untuk tahu outlet mana pakai endpoint Kasir biasa, mana pakai
endpoint Laundry, dan mana yang belum didukung sama sekali (sembunyikan di UI,
jangan biarkan user coba dan kena 501) — tabel lengkap pemetaannya ada di
API.md bagian "Status Cakupan".

**Belum didukung: Sewa/Rental, F&B mode kitchen.** Sewa/Rental sengaja
**DITUNDA atas keputusan eksplisit pemilik proyek** (bukan terlewat) — 4 jenis
outlet lain dianggap cukup dulu. `RentalController` di web 522 baris (booking
unit, durasi, deposit, ekstensi, retur, denda, gating verifikasi dokumen
pelanggan) jauh lebih kompleks dari 4 lainnya digabung. Kalau diminta lanjutkan
nanti, tanyakan dulu seberapa lengkap yang dibutuhkan di mobile — pernah
ditawarkan 3 opsi: (a) lengkap: booking+bayar+retur+ekstensi, (b) menengah:
booking+bayar saja (retur/ekstensi tetap di web), (c) minim: cuma lihat sewa
aktif + catat pembayaran/denda (booking baru tetap dari web karena perlu upload
dokumen yang lebih nyaman di layar besar).

Dokumentasi endpoint lengkap (request/response contoh nyata dari hasil test):
`e:\Project\pabalu2\docs\API.md` — baca file itu langsung dari repo backend
kalau butuh detail; ringkasan di bawah ini bisa basi seiring waktu.

## 5 tab utama aplikasi ↔ endpoint

| Tab | Endpoint |
|---|---|
| Dashboard (Home) | `GET /outlets/{outlet}/dashboard` — ringkasan hari ini: omset, jumlah transaksi, produk terlaris, status shift. Sama untuk semua jenis outlet (termasuk Laundry — transaksi laundry otomatis ikut terhitung). |
| Produk | F&B/Retail/Salon: `products`+`categories`. Laundry: `laundry/products`. (mode lihat-lihat, endpoint sama dengan tab Kasir) |
| Kasir | F&B/Retail/Salon: `categories`+`products`+`POST transactions` (keranjang→checkout). **Laundry beda total**: `POST laundry/orders` (buat order belum bayar) → `POST laundry/orders/{id}/advance` (majukan status) → `POST laundry/orders/{id}/pay` (bayar saat status "selesai"). Cek `route_prefix` outlet untuk tahu mode mana yang dipakai. |
| Riwayat | F&B/Retail/Salon/Laundry semua: `GET /outlets/{outlet}/transactions` (list) + `.../transactions/{id}` (detail) — laundry otomatis tercatat di sini setelah dibayar. Laundry tambahan: `GET laundry/orders?status=...` untuk lihat order yang BELUM dibayar (status masuk/proses/selesai). |
| Profile | `GET /me`, `POST /logout`, `GET/POST .../shift` (buka/tutup shift; Laundry tidak pakai shift di endpoint order-nya) |

### Ringkasan endpoint (lihat API.md untuk detail & contoh JSON)

```
POST   /api/v1/login                                  (email, password, device_name)
POST   /api/v1/logout
GET    /api/v1/me
GET    /api/v1/outlets
GET    /api/v1/outlets/{outlet}/dashboard
GET    /api/v1/outlets/{outlet}/shift
POST   /api/v1/outlets/{outlet}/shift/open             (opening_cash, notes?)
POST   /api/v1/outlets/{outlet}/shift/close             (closing_cash, notes?)
GET    /api/v1/outlets/{outlet}/categories
GET    /api/v1/outlets/{outlet}/products
GET    /api/v1/outlets/{outlet}/transactions
POST   /api/v1/outlets/{outlet}/transactions             (items[], payment_method, payment_amount, ...)
GET    /api/v1/outlets/{outlet}/transactions/{transaction}

# Khusus Laundry (route_prefix=laundry) — beda bentuk, lihat API.md bagian "Laundry"
GET    /api/v1/outlets/{outlet}/laundry/products
GET    /api/v1/outlets/{outlet}/laundry/orders?status=masuk
POST   /api/v1/outlets/{outlet}/laundry/orders            (customer_name, items[], weight_kg?, ...)
GET    /api/v1/outlets/{outlet}/laundry/orders/{id}
POST   /api/v1/outlets/{outlet}/laundry/orders/{id}/advance
POST   /api/v1/outlets/{outlet}/laundry/orders/{id}/pay    (payment_method, payment_amount)
```

Semua nominal uang: **integer Rupiah**, bukan desimal/string. Total & kembalian
dihitung server-side — jangan hitung ulang di klien untuk keputusan bisnis,
tampilkan saja hasil dari server.

## Alur kasir yang perlu direplikasi di UI

1. Login → simpan token di secure storage (mis. `react-native-keychain` atau
   `expo-secure-store`, jangan AsyncStorage polos untuk token).
2. Pilih outlet (kalau kasir cuma ditugaskan 1 outlet, langsung masuk situ).
3. Cek status shift (`GET .../shift`). Kalau `enabled:true` dan belum ada
   `active_shift` → wajib buka shift dulu sebelum bisa transaksi (server juga
   menolak transaksi dengan 422 kalau ini dilewati, tapi sebaiknya dicegah di UI).
4. Layar POS: kategori → produk → keranjang → checkout (`POST transactions`).
5. Setelah transaksi sukses, respons `data` berisi semua yang dibutuhkan untuk
   cetak struk (tidak perlu request tambahan) — inilah yang harus dikonversi
   ke perintah ESC/POS untuk dikirim ke printer.
6. Tutup shift di akhir sesi kerja kasir.

## Yang belum diputuskan / perlu didiskusikan berikutnya

- Paket printer RN mana yang dipakai (belum dipilih — validasi saat mulai
  implementasi printer, bukan sekarang).
- Strategi offline (kalau koneksi outlet sering putus) — API saat ini
  murni online, belum ada dukungan queue/sync offline.
- Upload bukti pembayaran non-tunai (foto) — belum ada di API v1.
