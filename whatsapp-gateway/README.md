# Pabalu WhatsApp Gateway (Self-Host)

Service kecil berbasis [Baileys](https://github.com/WhiskeySockets/Baileys) untuk
mengirim kode verifikasi WhatsApp dari nomor Anda sendiri — alternatif GRATIS dari
provider pihak ketiga (Fonnte/Wablas) yang sudah didukung Pabalu. Laravel memanggil
service ini lewat HTTP biasa, service ini yang urus koneksi ke WhatsApp Web.

**Ini API TIDAK RESMI** — berjalan meniru sesi WhatsApp Web, bukan WhatsApp Business
Cloud API resmi dari Meta. Risiko nomor kena banned tetap ada, sama seperti provider
pihak ketiga — pakai nomor/SIM khusus, JANGAN nomor WA pribadi/utama bisnis.

## Instalasi

```bash
cd whatsapp-gateway
npm install
cp .env.example .env
```

Buka `.env`, isi `API_KEY` dengan string acak yang panjang (mis. hasil
`openssl rand -hex 32` atau `php -r "echo bin2hex(random_bytes(32));"`).
**Catat nilainya** — nanti diisi PERSIS SAMA di Laravel (Pengaturan Sistem >
Gateway WhatsApp > provider "Self-Host (Baileys)" > API Key).

## Menjalankan

```bash
npm start
```

Pertama kali jalan, QR code akan muncul di terminal — scan pakai WhatsApp di HP
(nomor khusus yang sudah disiapkan): buka WhatsApp > Perangkat Tertaut > Tautkan
Perangkat, lalu scan. Setelah tersambung, sesi login disimpan di folder
`auth_session/` (dibuat otomatis) — restart service berikutnya TIDAK perlu scan
ulang selama folder ini tidak dihapus dan sesi tidak logout dari sisi HP.

Kalau muncul "Sudah logout dari HP" di log, hapus folder `auth_session/` lalu
`npm start` lagi untuk scan QR baru.

## Konfigurasi di Laravel

Buka `/pengaturan-sistem` di aplikasi web (login sebagai admin):

| Field | Isi dengan |
|---|---|
| Provider | **Self-Host (Baileys)** |
| API Key / Token | Nilai `API_KEY` dari file `.env` service ini |
| Domain Server Wablas *(dipakai ulang untuk field URL)* | `http://127.0.0.1:3001` (atau URL server tempat service ini jalan, kalau beda mesin dari Laravel) |

Simpan, lalu coba "Kirim Tes" ke nomor Anda sendiri.

## Menjalankan terus-menerus (production)

`npm start` di atas berhenti kalau terminal ditutup. Untuk produksi, jalankan
lewat process manager supaya otomatis restart kalau crash, misalnya
[PM2](https://pm2.keymetrics.io/):

```bash
npm install -g pm2
pm2 start index.js --name pabalu-wa-gateway
pm2 save
pm2 startup   # ikuti instruksi yang ditampilkan supaya otomatis jalan lagi setelah reboot server
```

## Catatan penting

- **Satu nomor = satu sesi.** Kalau login sesi baru dari HP lain/scan ulang di
  tempat lain, sesi service ini bisa ikut ter-logout.
- **Fallback ke email tetap aktif.** Kalau service ini mati/`connectionStatus`
  bukan `connected` saat dipanggil, Laravel otomatis mengirim email verifikasi
  seperti biasa — tidak ada pengguna yang terjebak tanpa cara verifikasi.
- Endpoint `/send` dan `/status` **wajib** header `X-API-Key` — jangan expose
  port service ini ke internet publik tanpa firewall/reverse-proxy yang membatasi
  akses, karena siapa pun yang tahu API Key bisa kirim pesan atas nama nomor Anda.
