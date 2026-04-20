# Tutorial Deploy Pabalu ke Shared Hosting (cPanel)

> **Versi:** Laravel 13 · PHP 8.3 · MySQL
> **Cocok untuk:** Niagahoster, Hostinger, DomaiNesia, IDCloudHost, dll (cPanel-based)

---

## DAFTAR ISI

1. [Persiapan di Komputer Lokal](#1-persiapan-di-komputer-lokal)
2. [Buat Database di cPanel](#2-buat-database-di-cpanel)
3. [Upload File ke Hosting](#3-upload-file-ke-hosting)
4. [Konfigurasi .env](#4-konfigurasi-env)
5. [Arahkan Domain ke Folder public](#5-arahkan-domain-ke-folder-public)
6. [Jalankan Migrasi via cPanel Terminal](#6-jalankan-migrasi-via-cpanel-terminal)
7. [Konfigurasi Storage & Permission](#7-konfigurasi-storage--permission)
8. [Update Aplikasi (Deploy Ulang)](#8-update-aplikasi-deploy-ulang)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Persiapan di Komputer Lokal

### 1.1 Pastikan Semua Perubahan Sudah di Git

```bash
git add .
git commit -m "siap deploy"
git push origin main
```

### 1.2 Build Vendor untuk Production

Di komputer lokal, jalankan:

```bash
composer install --no-dev --optimize-autoloader
```

> Ini menghasilkan folder `vendor/` yang dioptimalkan untuk production (tanpa package development).

### 1.3 Zip Seluruh Project

Zip seluruh folder project Pabalu dari File Explorer atau terminal:

```bash
# Windows (PowerShell)
Compress-Archive -Path * -DestinationPath pabalu.zip
```

Atau klik kanan folder → Send to → Compressed (zipped) folder.

**Yang WAJIB ada di dalam zip:**
- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/`
- `resources/`
- `routes/`
- `storage/`
- `vendor/`
- `artisan`
- `composer.json`
- `composer.lock`

**Yang TIDAK perlu di-zip (bisa diabaikan):**
- `.git/`
- `node_modules/`
- `.env` (akan dibuat manual di server)

---

## 2. Buat Database di cPanel

1. Login ke **cPanel** hosting kamu
2. Cari menu **MySQL Databases**
3. **Buat database baru:**
   - Nama database: `pabalu_db` (atau sesuai keinginan)
   - Klik **Create Database**
4. **Buat user database:**
   - Username: `pabalu_user`
   - Password: buat password kuat, **simpan baik-baik**
   - Klik **Create User**
5. **Hubungkan user ke database:**
   - Pilih user dan database yang baru dibuat
   - Centang **ALL PRIVILEGES**
   - Klik **Make Changes**

> **Catat:** Nama database lengkapnya biasanya jadi `namaakun_pabalu_db` dan user `namaakun_pabalu_user`. Ini yang akan dipakai di `.env`.

---

## 3. Upload File ke Hosting

### Opsi A: Upload via File Manager cPanel (Disarankan)

1. Buka **File Manager** di cPanel
2. Masuk ke folder `public_html`
3. **Buat folder baru** bernama `pabalu` (di luar `public_html`, di level yang sama):
   - Klik `public_html` di sidebar kiri
   - Naik satu level ke `/home/namaakun/`
   - Klik **New Folder** → beri nama `pabalu`
4. Masuk ke folder `pabalu` yang baru dibuat
5. Klik **Upload** → upload file `pabalu.zip`
6. Setelah upload selesai, klik kanan `pabalu.zip` → **Extract**
7. Pastikan semua file terekstrak langsung di dalam `/home/namaakun/pabalu/`

**Struktur folder yang benar:**
```
/home/namaakun/
├── public_html/          ← folder website utama
│   └── (nanti kita arahkan ke sini)
└── pabalu/               ← semua file Laravel ada di sini
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/           ← ini yang akan ditautkan ke public_html
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── artisan
```

### Opsi B: Clone via Git (jika hosting support SSH)

```bash
# SSH ke hosting
ssh namaakun@domainmu.com

# Masuk ke folder home
cd ~

# Clone repo
git clone https://github.com/username/pabalu.git pabalu
cd pabalu

# Install dependencies
composer install --no-dev --optimize-autoloader
```

---

## 4. Konfigurasi .env

### 4.1 Buat File .env

Di File Manager cPanel:
1. Masuk ke `/home/namaakun/pabalu/`
2. Klik **New File** → beri nama `.env`
3. Klik kanan `.env` → **Edit**

### 4.2 Isi File .env

Copy dan sesuaikan konten berikut:

```env
APP_NAME="Pabalu"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domainmu.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=namaakun_pabalu_db
DB_USERNAME=namaakun_pabalu_user
DB_PASSWORD=password_database_kamu

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file

MAIL_MAILER=log

# Midtrans (isi jika sudah punya akun)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

> **Catatan penting:**
> - `APP_KEY` dikosongkan dulu, akan di-generate via terminal
> - `DB_HOST=localhost` hampir selalu benar untuk shared hosting
> - `DB_DATABASE` dan `DB_USERNAME` gunakan nama lengkap dengan prefix akun cPanel
> - `APP_DEBUG=false` WAJIB di production — jangan sampai lupa

---

## 5. Arahkan Domain ke Folder public

Folder public Laravel ada di `/home/namaakun/pabalu/public/`, bukan di `public_html`. Ada dua cara:

### Cara A: Ganti Document Root via cPanel (Direkomendasikan)

1. Di cPanel, cari **Domains** atau **Addon Domains**
2. Klik **Manage** pada domain utama
3. Ubah **Document Root** dari `public_html` menjadi `pabalu/public`
4. Klik **Save**

### Cara B: Salin isi public/ ke public_html (Alternatif)

Jika tidak bisa ubah document root:

1. Salin semua isi folder `pabalu/public/` ke `public_html/`
2. Edit file `public_html/index.php`:

Cari baris:
```php
require __DIR__.'/../vendor/autoload.php';
```
Ubah menjadi:
```php
require __DIR__.'/../pabalu/vendor/autoload.php';
```

Cari baris:
```php
$app = require_once __DIR__.'/../bootstrap/app.php';
```
Ubah menjadi:
```php
$app = require_once __DIR__.'/../pabalu/bootstrap/app.php';
```

### Cara C: Gunakan .htaccess di public_html

Buat file `public_html/.htaccess` dengan isi:

```apache
RewriteEngine On
RewriteRule ^(.*)$ /pabalu/public/$1 [L]
```

---

## 6. Jalankan Migrasi via cPanel Terminal

### 6.1 Buka Terminal

Di cPanel, cari menu **Terminal** (Advanced → Terminal).

Jika tidak ada Terminal, gunakan **PHP Script** atau hubungi support hosting.

### 6.2 Generate APP_KEY

```bash
cd ~/pabalu
php artisan key:generate
```

Setelah dijalankan, `.env` akan terisi:
```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### 6.3 Jalankan Migrasi

```bash
php artisan migrate --force
```

### 6.4 Jalankan Seeder

```bash
php artisan db:seed --class=RolePermissionSeeder --force
```

### 6.5 Buat Storage Link

```bash
php artisan storage:link
```

### 6.6 Cache untuk Performance

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6.7 Buat Akun Admin Pertama

```bash
php artisan tinker
```

Di dalam tinker:
```php
$user = \App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@domainmu.com',
    'password' => bcrypt('passwordkuat123'),
]);
$user->assignRole('admin');
exit
```

---

## 7. Konfigurasi Storage & Permission

### 7.1 Set Permission Folder

Di cPanel Terminal:

```bash
cd ~/pabalu
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 7.2 Jika Upload Gambar Tidak Berfungsi

Pastikan folder ini ada dan writable:

```bash
mkdir -p storage/app/public/products
mkdir -p storage/app/public/expenses
mkdir -p storage/app/public/proofs
chmod -R 775 storage/app/public
```

### 7.3 Verifikasi Storage Link

```bash
ls -la public/storage
```

Harus terlihat: `public/storage -> ../storage/app/public`

Jika belum ada, jalankan ulang:
```bash
php artisan storage:link
```

---

## 8. Update Aplikasi (Deploy Ulang)

Setiap kali ada perubahan kode, ikuti langkah berikut:

### Jika Upload Manual (zip)

1. Di komputer lokal, jalankan:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. Zip ulang seluruh project
3. Upload ke hosting dan extract (timpa yang lama)
4. Di cPanel Terminal:
   ```bash
   cd ~/pabalu
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Jika via Git (SSH tersedia)

```bash
cd ~/pabalu
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Script Deploy Otomatis

Buat file `deploy.sh` di folder `pabalu`:

```bash
#!/bin/bash
echo "=== Mulai Deploy ==="

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan down --message="Sistem sedang diperbarui, harap tunggu..." --retry=60

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan up

echo "=== Deploy Selesai ==="
```

Jalankan dengan:
```bash
cd ~/pabalu
bash deploy.sh
```

---

## 9. Troubleshooting

### ❌ Error 500 (Internal Server Error)

1. Aktifkan sementara debug di `.env`:
   ```env
   APP_DEBUG=true
   ```
2. Refresh browser — baca error yang muncul
3. Setelah solved, **matikan lagi**: `APP_DEBUG=false`

### ❌ "No application encryption key has been specified"

```bash
cd ~/pabalu
php artisan key:generate
```

### ❌ Tampilan CSS/JS Tidak Muncul (404 pada asset)

Pastikan `APP_URL` di `.env` benar dan sesuai domain:
```env
APP_URL=https://domainmu.com
```
Lalu:
```bash
php artisan config:cache
```

Jika project pakai Vite (ada folder `public/build/`), pastikan folder `public/build/` ikut ter-upload.

### ❌ Gambar / Upload Tidak Berfungsi

```bash
php artisan storage:link
chmod -R 775 storage/app/public
```

### ❌ Error "SQLSTATE" saat Migrasi

Pastikan isian DB di `.env` benar — nama DB, user, dan password harus menggunakan **nama lengkap dengan prefix cPanel** (bukan hanya `pabalu`).

Contoh benar:
```env
DB_DATABASE=abdulwahid_pabalu
DB_USERNAME=abdulwahid_pabalu
```

### ❌ "Class not found" atau "Target class does not exist"

```bash
cd ~/pabalu
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### ❌ Halaman Order Online Tidak Bisa Diakses

Pastikan hosting support **URL Rewriting** (mod_rewrite Apache). Cek file `public/.htaccess` ada dan ter-upload.

### ❌ Session / Login Terus Logout

Pastikan di `.env`:
```env
SESSION_DRIVER=file
SESSION_DOMAIN=domainmu.com
```
Dan folder `storage/framework/sessions/` ada dan writable:
```bash
mkdir -p ~/pabalu/storage/framework/sessions
chmod 775 ~/pabalu/storage/framework/sessions
```

---

## CHECKLIST SEBELUM GO-LIVE

- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_ENV=production` di `.env`
- [ ] `APP_URL` sesuai domain dengan HTTPS
- [ ] Database berhasil terkoneksi
- [ ] `php artisan key:generate` sudah dijalankan
- [ ] Migrasi berhasil (`php artisan migrate --force`)
- [ ] Seeder berhasil (`RolePermissionSeeder`)
- [ ] Storage link aktif (`php artisan storage:link`)
- [ ] Akun admin sudah dibuat
- [ ] Upload foto produk berfungsi
- [ ] Login berhasil
- [ ] POS/Kasir berfungsi
- [ ] Struk bisa dicetak
- [ ] Config & route sudah di-cache

---

*Jika ada kendala, hubungi support hosting atau lihat log error di `storage/logs/laravel.log`*
