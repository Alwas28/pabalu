# API Kasir Mobile — Pabalu2

API ini dibuat khusus untuk aplikasi kasir Android (React Native). Terpisah dari
`routes/web.php` (server-rendered Blade untuk dashboard web) — auth pakai token
Sanctum (Bearer), bukan session/cookie.

- Base URL (lokal): `http://<host>:<port>/api/v1`
- Semua request/response: JSON (`Accept: application/json`)
- Auth: `Authorization: Bearer <token>` di setiap endpoint kecuali `POST /login`
- Semua nominal uang dalam **Rupiah, integer** (bukan desimal, bukan string)

## Status Cakupan

Didukung:
- **F&B, Retail, Salon** dengan `order_mode = quick` — alur keranjang produk →
  checkout langsung (endpoint "Kasir": `categories`, `products`, `transactions`).
  Pelacakan stok beda per jenis: F&B ikut `requires_opening_stock`, Retail selalu
  melacak stok, Salon ikut `track_cogs` outlet type.
- **Laundry** — alur order bertahap: buat order (belum bayar) → status berjalan
  (masuk → proses → selesai) → bayar saat status "selesai" (endpoint `laundry/*`,
  beda bentuk dari endpoint Kasir di atas, lihat bagian "Laundry" di bawah).

Belum didukung:
- Outlet F&B mode **kitchen** (order masuk dulu → diproses dapur → checkout terpisah)
- **Sewa/Rental** — alur berbasis unit/durasi sewa/deposit, jauh berbeda dari
  yang lain. Ditunda atas keputusan pemilik proyek (bukan terlewat) — lihat
  `docs/CLAUDE.md` untuk konteks & opsi scope kalau nanti mau dilanjutkan
- Upload bukti pembayaran (foto) untuk pembayaran non-tunai
- Opening stok (outlet dengan `requires_opening_stock = true` perlu opening stok
  dulu di web sebelum bisa transaksi lewat API)

Endpoint `GET /outlets` mengembalikan field `route_prefix` dan `order_mode` per
outlet — pakai ini di aplikasi untuk menentukan endpoint mana yang dipakai
(Kasir biasa vs Laundry vs belum didukung), alih-alih membiarkan user mencoba
dan mendapat error 501. Pemetaan `route_prefix` → endpoint:

| `route_prefix` | Endpoint transaksi |
|---|---|
| `fnb` (bukan mode kitchen), `retail`, `salon` | `categories`, `products`, `transactions` (Kasir biasa) |
| `laundry` | `laundry/products`, `laundry/orders` (order bertahap) |
| `sewa` | belum didukung |

## Autentikasi

### `POST /login`
Rate limit: 10 request/menit per IP.

Request:
```json
{
  "email": "kasir@contoh.com",
  "password": "rahasia123",
  "device_name": "Samsung A14 - Kasir 1"
}
```
`device_name` bebas (dipakai sebagai label token) — sebaiknya nama perangkat
supaya gampang dikenali kalau perlu di-revoke dari sisi admin nanti. Login ulang
dengan `device_name` yang sama otomatis mencabut token lama (satu token aktif
per perangkat).

Response `200`:
```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx",
  "user": { "id": 11, "name": "Budi", "email": "kasir@contoh.com", "role": "kasir" }
}
```
Response `422` (email/password salah, atau akun nonaktif):
```json
{ "message": "Email atau password salah.", "errors": { "email": ["Email atau password salah."] } }
```

Simpan `token` (misal di secure storage) dan kirim di setiap request berikutnya:
`Authorization: Bearer <token>`.

### `POST /logout`
Mencabut token yang sedang dipakai. Response `200`: `{ "message": "Logout berhasil." }`

### `GET /me`
Profil user yang login.
```json
{ "id": 11, "name": "Budi", "email": "kasir@contoh.com", "role": "kasir", "role_label": "Kasir" }
```

Token tidak valid/sudah di-logout → semua endpoint `auth:sanctum` mengembalikan
`401 { "message": "Unauthenticated." }`.

## Outlet

### `GET /outlets`
Daftar outlet yang boleh diakses user (admin = semua outlet, owner = outlet
miliknya, kasir/admin_outlet = outlet tempat dia ditugaskan).
```json
{ "data": [
  {
    "id": 13, "name": "Warung Berkah", "code": "WRBK", "address": null,
    "outlet_type": "warung_makan", "outlet_type_label": "Warung Makan",
    "route_prefix": "fnb", "order_mode": "quick",
    "enable_opening_shift": true, "requires_opening_stock": true,
    "is_active": true
  }
] }
```

## Dashboard

### `GET /outlets/{outlet}/dashboard`
Ringkasan untuk tab Home/Dashboard mobile — data **hari ini, milik user yang
login** (bukan seluruh outlet/kasir lain), konsisten dengan scoping "riwayat
transaksi milik saya" di endpoint Transaksi.
```json
{
  "outlet": { "id": 14, "name": "Warung Berkah", "code": "WRBK", "...": "sama seperti GET /outlets" },
  "business_date": "2026-08-11",
  "shift": {
    "enabled": true,
    "active_shift": { "id": 8, "opened_at": "2026-08-11T22:33:08+08:00", "closed_at": null,
      "opening_cash": 50000, "closing_cash": null, "expected_cash": null, "notes": null, "is_active": true }
  },
  "today": {
    "total_transactions": 2,
    "total_revenue": 47000,
    "top_products": [ { "name": "Nasi Goreng", "qty": 4 }, { "name": "Es Teh", "qty": 1 } ]
  }
}
```
- `shift.active_shift` bernilai `null` kalau belum ada shift aktif (dan `today.*`
  tetap terisi — dashboard tidak bergantung pada shift aktif, cuma tanggal bisnis).
- `top_products` maksimal 5, diurutkan dari qty terbanyak; kosong (`[]`) kalau
  belum ada transaksi hari ini.
- `business_date` bisa berbeda dari tanggal kalender kalau outlet buka melewati
  tengah malam (lihat catatan `requires_opening_stock` di outlet) — pakai field
  ini, jangan hitung tanggal sendiri di klien.

## Shift

Kalau `enable_opening_shift` pada outlet bernilai `false`, kasir bisa langsung
transaksi tanpa buka shift — endpoint di bawah tetap bisa dipanggil tapi
`enabled: false` dan tidak wajib dibuka dulu.

### `GET /outlets/{outlet}/shift`
Status shift milik user yang login (bukan shift kasir lain) untuk outlet ini.
```json
{
  "enabled": true,
  "active_shift": { "id": 7, "opened_at": "2026-08-11T21:56:08+08:00", "closed_at": null,
    "opening_cash": 100000, "closing_cash": null, "expected_cash": null, "notes": null, "is_active": true },
  "stats": { "total_transactions": 3, "total_revenue": 90000, "cash_in": 60000,
    "total_expense": 0, "expected_cash": 160000 }
}
```
`active_shift` dan `stats` bernilai `null` kalau belum ada shift aktif.

### `POST /outlets/{outlet}/shift/open`
```json
{ "opening_cash": 100000, "notes": "opsional" }
```
`422` kalau: fitur shift tidak aktif untuk outlet, outlet belum punya produk aktif,
atau user masih punya shift aktif yang belum ditutup.

### `POST /outlets/{outlet}/shift/close`
```json
{ "closing_cash": 145000, "notes": "opsional" }
```
Response menyertakan `selisih` (positif = lebih, negatif = kurang, 0 = pas):
```json
{ "message": "Shift berhasil ditutup.", "shift": {...}, "selisih": 15000 }
```

## Kategori & Produk

### `GET /outlets/{outlet}/categories`
```json
{ "data": [{ "id": 36, "name": "Minuman", "icon": "fa-tag", "sort_order": 0 }] }
```

### `GET /outlets/{outlet}/products`
```json
{ "data": [{ "id": 17, "category_id": 36, "name": "Es Teh", "sku": null, "unit": "gelas",
  "price": 5000, "stock": 40, "image_url": "http://.../storage/products/xxx.jpg" }] }
```
`stock` bernilai `null` kalau outlet tidak melacak stok — lihat tabel pelacakan
stok per jenis di bagian "Status Cakupan" (Retail selalu melacak, Salon ikut
`track_cogs`, F&B ikut `requires_opening_stock`).

## Transaksi

### `POST /outlets/{outlet}/transactions`
Buat transaksi baru (checkout). Wajib shift aktif dulu jika `enable_opening_shift = true`.

Request:
```json
{
  "items": [{ "id": 17, "qty": 2 }, { "id": 18, "qty": 1 }],
  "payment_method": "cash",
  "payment_amount": 50000,
  "discount_type": "percent",
  "discount_value": 10,
  "notes": "opsional"
}
```
- `payment_method`: salah satu dari `cash`, `qris`, `transfer`, `card`
- `discount_type`/`discount_value`: opsional, default tanpa diskon
- Total & kembalian dihitung di server (jangan percaya perhitungan dari klien)

Response `201`:
```json
{ "data": {
  "id": 25, "transaction_number": "TRXPB09WRBK260811215630001", "date": "2026-08-11",
  "created_at": "2026-08-11T21:56:30+08:00", "cashier": "Budi",
  "subtotal": 30000, "discount_amount": 0, "total": 30000,
  "payment_method": "cash", "payment_label": "Tunai",
  "payment_amount": 50000, "change_amount": 20000, "status": "completed", "notes": null,
  "items": [{ "product_name": "Es Teh", "product_price": 15000, "qty": 2, "subtotal": 30000 }]
} }
```
`422` kalau: shift belum dibuka, opening stok belum dilakukan (outlet yang
melacak stok), produk tidak ditemukan/nonaktif, atau stok tidak cukup — pesan
error ada di field `message`.

### `GET /outlets/{outlet}/transactions`
Riwayat transaksi milik user yang login (bukan semua kasir), terbaru dulu, dengan pagination.
```json
{ "data": [ {...tanpa "items"} ], "meta": { "current_page": 1, "last_page": 3, "total": 42 } }
```

### `GET /outlets/{outlet}/transactions/{transaction}`
Detail satu transaksi (termasuk `items`) — dipakai untuk data yang dikirim ke printer struk.

## Laundry (order bertahap — beda bentuk dari Kasir biasa)

Khusus outlet `route_prefix = laundry`. Tidak pakai keranjang→checkout langsung —
alurnya: **buat order (belum bayar)** → **status berjalan** → **bayar saat status
"Selesai"**. Tidak ada pengecekan shift di endpoint-endpoint ini (mengikuti
perilaku web).

### `GET /outlets/{outlet}/laundry/products`
Daftar layanan/produk laundry aktif (sama bentuk dengan `GET products` di atas)
— dipakai untuk mengisi pilihan item saat membuat order (nama & harga bisa juga
diketik manual, lihat `store` di bawah).

### `GET /outlets/{outlet}/laundry/orders?status=masuk&q=budi`
List order per status (`masuk`/`proses`/`selesai`/`diambil`, default `masuk`),
opsional cari berdasarkan nomor order/nama/no. HP pelanggan. Response berbentuk
sama seperti Riwayat Transaksi (`data` + `meta` pagination), tiap item punya
bentuk `LaundryOrderResource` (lihat contoh di `store` di bawah, tanpa `items`).

### `POST /outlets/{outlet}/laundry/orders`
Request:
```json
{
  "customer_name": "Budi",
  "customer_phone": "08123456789",
  "save_customer": true,
  "weight_kg": 5,
  "estimated_done_at": "2026-08-13 17:00:00",
  "notes": "opsional",
  "items": [
    { "product_id": 3, "product_name": "Cuci Reguler", "product_price": 6000, "qty": 5, "unit": "kg" }
  ]
}
```
- `product_id` opsional (boleh null kalau item diketik manual, bukan dari daftar produk)
- `save_customer: true` akan membuat/mengupdate data Customer di outlet ini
- `weight_kg`, `estimated_done_at`, `notes` semua opsional

Response `201`:
```json
{ "data": {
  "id": 5, "order_number": "LDR-ZZLD-20260812-001",
  "customer_name": "Budi", "customer_phone": "08123456789", "weight_kg": 5,
  "notes": null, "estimated_done_at": null,
  "status": "masuk", "status_label": "Masuk", "next_status": "proses", "next_status_label": "Sedang Diproses",
  "subtotal": 30000, "discount_amount": 0, "total": 30000,
  "payment_method": null, "payment_amount": null, "change_amount": null, "paid_at": null,
  "created_at": "2026-08-12T00:00:19+08:00",
  "items": [{ "product_name": "Cuci Reguler", "product_price": 6000, "qty": 5, "unit": "kg", "subtotal": 30000, "item_notes": null }]
} }
```

### `GET /outlets/{outlet}/laundry/orders/{laundryOrder}`
Detail satu order (termasuk `items`).

### `POST /outlets/{outlet}/laundry/orders/{laundryOrder}/advance`
Majukan status satu tingkat (`masuk`→`proses`→`selesai`). `422` kalau order sudah
di status akhir (`diambil`) — pakai `pay` untuk transisi terakhir, bukan `advance`.

### `POST /outlets/{outlet}/laundry/orders/{laundryOrder}/pay`
Hanya bisa dipanggil kalau status order = `selesai`. Request:
```json
{ "payment_method": "cash", "payment_amount": 50000 }
```
Sukses → status jadi `diambil`, kembalian dihitung server, **otomatis tercatat**
ke tabel transaksi umum (jadi ikut muncul di `GET transactions` dan `GET dashboard`
outlet yang sama — tidak perlu panggilan tambahan). `422` kalau status order
bukan `selesai`.

## Error & Kode Status

| Status | Arti |
|---|---|
| 401 | Token tidak ada/tidak valid/sudah logout |
| 403 | User tidak punya akses ke outlet ini |
| 404 | Outlet/transaksi tidak ditemukan |
| 422 | Validasi gagal / aturan bisnis gagal (lihat `message`) |
| 429 | Rate limit login terlampaui |
| 501 | Jenis outlet/mode order belum didukung API (lihat bagian Cakupan) |

Body error validasi standar Laravel:
```json
{ "message": "Ringkasan error.", "errors": { "field": ["pesan error"] } }
```

## Rencana Lanjutan (belum dibangun)
- Sewa/Rental — alur berbasis unit/durasi sewa/deposit, jauh berbeda dari yang
  lain (juga ada gating verifikasi dokumen pelanggan di web) — butuh desain
  endpoint terpisah, belum dikerjakan
- Upload bukti pembayaran non-tunai
- Endpoint cetak/print data (kemungkinan tidak perlu endpoint khusus — data dari
  `GET /transactions/{id}` sudah cukup untuk dirender ke format ESC/POS di aplikasi)
- Push notification (misal notifikasi order masuk untuk mode kitchen, kalau nanti didukung)
