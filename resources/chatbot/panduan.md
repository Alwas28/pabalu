# Panduan Penggunaan Pabalu

Sumber: dokumen "Panduan Penggunaan Pabalu untuk Owner". Perbarui file ini setiap kali panduan berubah — isinya dipakai chatbot untuk user yang sudah login.

## Pendahuluan

Sebagai Owner (Pemilik Toko), Anda mengelola semua outlet milik Anda dari satu akun web Pabalu: membuat outlet, mengatur karyawan dan kasir, memantau penjualan, dan berlangganan Paket Pro.

Pabalu punya 5 modul jenis outlet. Menu di dalam outlet berbeda menurut jenisnya.

| Jenis outlet | Cocok untuk | Cara kerja kasir |
| --- | --- | --- |
| Warung Makan, Kafe / Coffee Shop (F&B) | Warteg, rumah makan, kedai kopi | Bayar langsung (Quick Pay) atau pesanan ke dapur dulu (Kitchen Order); ada Opening Stok & Closing Harian |
| Retail | Kelontong, pakaian, elektronik | Keranjang lalu bayar; stok dilacak terus; harga jual punya riwayat perubahan |
| Salon & Barbershop | Salon, barbershop, spa | Keranjang layanan lalu bayar; stok hanya jika jenisnya melacak HPP |
| Laundry | Laundry kiloan & satuan | Pesanan bertahap: Masuk → Diproses → Selesai → Diambil & dibayar |
| Jasa Sewa | Rental barang/kendaraan/alat | Sewa per jam/hari/bulan dengan deposit, dokumen pelanggan, perpanjangan, pengembalian, denda |

Peran lain: Admin Outlet (operasional + produk & laporan), Kasir (POS, stok harian, pengeluaran), dan Administrator Pabalu (pengelola sistem, paket Pro, dan tagihan).

## Memulai

Akun baru langsung memakai paket Free (1 outlet, 1 akun kasir).

### Daftar dan setup pertama

1. Buka halaman depan Pabalu, pilih daftar sebagai Owner, lalu isi nama, email, dan password.
2. Buka email dan klik tautan verifikasi. Akun belum bisa dipakai sebelum email terverifikasi.
3. Login. Wizard setup terbuka otomatis:
   1. Info Bisnis Anda — isi Nama Bisnis / Toko. Nama ini muncul di laporan dan struk.
   2. Outlet Pertama Anda — isi Nama Outlet, pilih Jenis Usaha, dan isi Alamat.
   3. Klik Selesaikan Setup. Anda masuk ke Dashboard.

### Menambah outlet berikutnya

Buka Kelola Outlet → Tambah Outlet. Formulirnya punya tiga tahap:

1. Jenis & Info — pilih jenis outlet; untuk Retail, pilih atau ketik Bidang Usaha (wajib). Isi lokasi (Provinsi, Kabupaten/Kota, Kecamatan, Desa/Kelurahan), geser pin di peta atau klik Lokasi Saya, lalu isi Nama Outlet, No. Telepon, dan Alamat.
2. Operasional — pilih Mode Transaksi: Quick Pay (bayar saat order) atau Kitchen Order (pesan dulu, bayar nanti). Jenis outlet yang tidak mendukung Kitchen Order otomatis memakai Quick Pay.
3. Fitur — nyalakan Opening Shift (kasir wajib input kas awal) dan Barcode Scanner bila perlu, lalu klik Buat Outlet.

Jumlah outlet dibatasi paket: Free 1 outlet, Pro hingga 3 outlet. Batas ini diatur Administrator; cek di menu Paket Pro.

### Masuk ke dalam outlet

Di Dashboard, klik kartu outlet di bagian Outlet Saya (atau buka dari Kelola Outlet). Sidebar berganti menjadi menu khusus outlet itu. Tombol kembali di atas sidebar membawa Anda ke Kelola Outlet.

## Fitur Umum Owner

Menu utama Owner di sidebar: Dashboard, Paket Pro, Kelola Outlet, Karyawan, Kelola User, Akun Saya.

### Menu utama (di luar outlet)

Dashboard — ringkasan semua outlet: Pendapatan & Transaksi Hari Ini, Pendapatan Bulan Ini, Jumlah Outlet, Karyawan Aktif, grafik penjualan 7 hari, daftar Outlet Saya dengan omzet hari ini, transaksi terbaru, dan peringatan tagihan yang belum lunas.

Paket Pro — melihat paket aktif, batas jumlah outlet dan akun kasir, tanggal berlaku, dan sisa waktu.
1. Tab Upgrade Paket: klik Aktifkan Sekarang bila paket bisa diaktifkan tanpa kode (tagihan dihitung belakangan per outlet), atau Upgrade via WhatsApp untuk menghubungi admin.
2. Setelah membayar, admin memberi kode. Buka tab Aktivasi / Perpanjang, masukkan Kode/PIN, klik Aktifkan Kode. Kode baru memperpanjang masa aktif yang masih berjalan.
3. Tab Riwayat mencatat semua kode yang pernah dipakai.

Kelola Outlet — daftar outlet beserta kode, jenis, dan status. Bisa menambah, mengedit (nama, jenis, mode transaksi, lokasi), menonaktifkan, atau menghapus outlet. Hapus bersifat permanen.

Karyawan — data karyawan dan penggajian.
1. Klik Tambah Karyawan; isi Nama Lengkap, Outlet Tempat Bertugas (gaji dikeluarkan dari outlet ini), Jabatan, No. WhatsApp, Pendidikan, Tanggal Masuk, Alamat, dan foto (maks. 2 MB).
2. Buka Detail karyawan. Di Master Gaji, klik Tambah, isi Nominal dan Berlaku Sejak, centang Jadikan gaji aktif.
3. Klik Catat Pembayaran setiap menggaji: pilih outlet, bulan & tahun periode, tanggal bayar. Pembayaran otomatis dicatat sebagai pengeluaran outlet dengan kategori Gaji Karyawan.
4. Di Akun Sistem, klik Buat Akun agar karyawan bisa login. Pilih role (Kasir atau Admin Outlet), email login, password, dan outlet yang boleh diakses.

Kelola User — cara lain membuat akun login: Tambah User, cari karyawan, isi email, role, dan password. User baru wajib verifikasi email sebelum bisa masuk. Jumlah akun kasir per outlet dibatasi paket.

Akun Saya — ubah nama, No. HP, email (mengubah email mereset verifikasi), dan password.

Chat & Laporan ke Admin — tombol mengambang di pojok kanan bawah membuka tab Chat (percakapan langsung dengan Administrator), Laporan (kirim keluhan atau bug), dan Riwayat (balasan admin).

Kasir dan Admin Outlet juga punya menu Riwayat Gaji untuk melihat gaji yang sudah dibayarkan.

### Fitur di dalam setiap outlet

Pengaturan Outlet (menu Konfigurasi) — hanya Owner dan Administrator yang bisa membukanya.
- Mode Transaksi: Quick Pay atau Kitchen Order / Antrian Layanan (lihat per jenis).
- Opening Shift: kasir wajib mencatat kas awal dan kas akhir; sistem menghitung selisih kas dan menyimpan riwayat shift per kasir.
- Scanner Barcode / QR: input barcode di POS dan Tambah Stok, lewat scanner USB/Bluetooth atau kamera. Produk harus punya SKU / Barcode.
- Self Order: pelanggan memesan lewat QR code; pesanan ditinjau staf dulu.
- Metode Pembayaran: Tunai selalu aktif. Bisa mengaktifkan QRIS Transfer, Transfer Bank, atau Kartu (EDC) — kasir mengisi nomor referensi dan foto bukti. QRIS Pay (QRIS dinamis via Midtrans) hanya bisa diaktifkan Administrator, lalu Owner mengisi Server Key dan Client Key Midtrans.
- Klik Simpan Pengaturan.

Opening Shift — riwayat shift kasir: kas awal, kas akhir, dan selisih. Menu muncul bila Opening Shift aktif.

Produk / Layanan — klik Tambah Produk; isi foto, nama, kategori, satuan, SKU/Barcode, Harga Jual, HPP/Harga Modal, Min. Stok Kritis (peringatan bila stok ≤ nilai ini), dan deskripsi. Produk bisa diaktif/nonaktifkan tanpa dihapus.

Kategori — daftar kategori produk. Kategori disediakan Administrator per jenis outlet dan dipakai bersama.

Tambah Stok — catat barang masuk dari supplier; riwayatnya bisa dilihat dan diedit.

Waste / Barang Rusak — catat barang terbuang, rusak, atau kedaluwarsa beserta alasannya.

Stok & Pergerakan — posisi stok tiap produk dan pergerakannya. Stok tidak diedit manual; stok hanya berubah lewat opening, tambah stok, waste, dan penjualan.

Pengeluaran — catat biaya operasional per kategori; bisa menambah kategori sendiri. Pengeluaran otomatis masuk ke Laba & Rugi.

Riwayat Transaksi — daftar penjualan, detail, dan cetak struk. Transaksi yang salah bisa di-void.

Laporan — Laporan Penjualan (bisa diekspor) dan Laba & Rugi (omzet − HPP − pengeluaran).

Tagihan — tagihan outlet yang diinput Administrator. Tagihan lunas bila admin menandainya, atau bila Owner klik Bayar dengan Kode dan memasukkan kode pelunasan dari admin.

## Panduan per Jenis Outlet

### 1. Warung Makan & Kafe / Coffee Shop (F&B)

Menu outlet: Dashboard Outlet · Operasional Harian (Opening Stok, POS / Kasir, Antrian Order, Pesanan Masuk, Tambah Stok, Pengeluaran, Waste / Rusak, Opening Shift, Closing Harian) · Transaksi · Produk & Stok · Laporan (Penjualan, Laba & Rugi) · Konfigurasi (Pengaturan Outlet, Tagihan).

Atur sekali di Pengaturan Outlet:
- Quick Pay untuk warung (pelanggan langsung bayar di kasir) atau Kitchen Order untuk resto/kafe dengan dapur (pesanan ke dapur dulu, bayar setelah selesai).
- Opening Stok Harian: bila aktif, kasir wajib mencatat stok awal tiap hari sebelum bisa transaksi.
- Self Order: pelanggan scan QR meja dan memesan dari ponsel. Bila nonaktif, QR hanya menampilkan menu.

Alur harian:
1. Opening Stok (pagi) — hitung stok yang tersedia, isi jumlahnya (isi 0 bila habis), klik Simpan Opening Stok. Bisa dibatalkan dan diisi ulang sebelum closing.
2. POS / Kasir — pilih menu, atur jumlah, pilih metode bayar, simpan, cetak struk.
3. Antrian Order (khusus Kitchen Order) — order bergerak dari Menunggu → Diproses → Diantarkan → Lunas. Tandai item yang sudah disajikan, cetak tiket dapur dan bill, lalu proses bayar. Gunakan Scan Struk untuk membuka order dari QR di struk.
4. Pesanan Masuk — pesanan dari Self Order berstatus Menunggu Review; klik Konfirmasi agar masuk antrian, atau Tolak.
5. Waste / Rusak dan Pengeluaran — catat sepanjang hari.
6. Closing Harian (malam) — periksa total transaksi, pendapatan, waste, pengeluaran, kas tunai bersih, penjualan per metode bayar, dan ringkasan stok (disiapkan, terjual, waste, sisa). Isi Catatan Penutupan, klik Lakukan Closing. Penjualan hari itu dikunci sebagai laporan harian dan stok direset untuk opening besok. Closing tidak tersedia bila belum ada transaksi hari itu.

QR menu meja: buka QR outlet, salin atau Cetak QR, lalu tempel di meja. Pelanggan memantau status pesanan dari ponselnya.

### 2. Retail

Menu outlet: Dashboard Toko · Operasional Toko (POS / Kasir, Pesanan Masuk, Tambah Stok, Pengeluaran, Barang Rusak, Opening Shift) · Transaksi · Produk & Stok · Laporan (Penjualan, Laba & Rugi (HPP)) · Konfigurasi (Pengaturan Toko, Tagihan).

Retail tidak punya opening/closing harian. Stok dihitung kumulatif: total masuk − rusak − terjual.

Langkah awal:
1. Saat membuat outlet, isi Bidang Usaha (misalnya kelontong, pakaian, elektronik).
2. Tambahkan produk lengkap dengan SKU / Barcode dan HPP / Harga Modal; HPP dipakai untuk laporan Laba & Rugi.
3. Di Pengaturan Toko, nyalakan Scanner Barcode / QR bila kasir memakai scanner.
4. Isi stok awal lewat Tambah Stok.

Operasional:
1. POS / Kasir — scan barcode atau pilih produk, atur jumlah, bayar, cetak struk.
2. Ubah Harga Jual — dari daftar Produk, buka harga produk, isi Harga Jual Baru dan alasan (opsional), klik Simpan Harga Baru. Harga baru hanya berlaku untuk penjualan berikutnya; semua perubahan tercatat di Riwayat Perubahan Harga.
3. Tambah Stok saat barang datang; Barang Rusak untuk barang cacat atau kedaluwarsa.
4. Pesanan Masuk — bila Self Order aktif, tinjau pesanan dari QR pelanggan sebelum diproses.

Katalog publik: cetak QR outlet, laminating, dan tempel di etalase atau kasir. Pelanggan scan dan langsung melihat daftar produk & harga.

Catatan: menu Tambah Stok, Pengeluaran, Barang Rusak, dan Laporan di Retail hanya tampil bila fiturnya termasuk dalam paket.

### 3. Salon & Barbershop

Menu outlet: Dashboard Salon · Operasional Salon (POS / Kasir, Pesanan Masuk, Pengeluaran, Opening Shift) · Transaksi · Produk & Layanan (Layanan, Kategori) · Laporan (Laporan Transaksi, Laba & Rugi (HPP)) · Konfigurasi (Pengaturan Salon, Tagihan).

Yang dijual di salon umumnya layanan (potong rambut, creambath, facial). Menu stok (Tambah Stok, Barang Rusak, Stok & Pergerakan) hanya muncul bila jenis outlet Salon diset melacak HPP oleh Administrator; saat itu menu berganti nama menjadi Produk & Stok.

Langkah awal:
1. Buka Layanan → Tambah Produk; isi nama layanan, kategori, dan harga.
2. Di Pengaturan Salon, pilih Mode Transaksi: Quick Pay (bayar langsung) atau Antrian Layanan (pelanggan masuk antrian dulu, bayar setelah layanan selesai).
3. Aktifkan Self Order bila pelanggan boleh memesan layanan lewat QR; pesanan ditinjau staf dulu.

Operasional:
1. POS / Kasir — pilih layanan yang diambil pelanggan, bayar, cetak struk.
2. Pesanan Masuk — konfirmasi atau tolak pesanan dari QR.
3. Pengeluaran — catat pembelian bahan, listrik, dan biaya lain.
4. Laporan Transaksi untuk melihat omzet per periode.

### 4. Laundry

Menu outlet: Dashboard Toko · Operasional Toko (Pesanan Laundry, Pengeluaran, Opening Shift) · Transaksi · Produk & Layanan (Layanan, Kategori) · Laporan (Laporan Pendapatan, Laba & Rugi) · Konfigurasi (Pengaturan, Tagihan).

Laundry tidak memakai POS biasa. Semua transaksi berjalan lewat Pesanan Laundry yang bertahap: Masuk → Sedang Diproses → Selesai → Diambil. Pesanan yang sudah dibayar otomatis masuk ke Riwayat Transaksi.

Langkah awal:
1. Buka Layanan → Tambah Produk; buat layanan seperti Cuci Kering (satuan kg), Cuci Setrika, Bed Cover (satuan pcs), lengkap dengan harga per unit.
2. Di Pengaturan, aktifkan metode pembayaran yang diterima.

Alur pesanan:
1. Klik Terima Pesanan Baru. Ketik nama pelanggan; pelanggan terdaftar muncul otomatis. Centang Simpan pelanggan agar datanya tersimpan untuk pesanan berikutnya.
2. Isi Nomor HP, Estimasi Selesai, catatan, lalu tambahkan item: pilih layanan dan isi jumlah (berat atau pcs). Total terhitung otomatis.
3. Klik Terima Pesanan, lalu Cetak Struk Masuk untuk pelanggan. Struk memuat QR yang bisa dipindai pelanggan untuk mengecek status cucian tanpa login.
4. Ubah status menjadi Sedang Diproses saat dicuci, lalu Selesai saat siap diambil.
5. Saat pelanggan datang, proses pembayaran (bisa juga dibayar di awal) dan tandai Diambil.

Daftar pesanan bisa difilter per status; angka di menu Pesanan Laundry menunjukkan pesanan yang masih aktif.

Catatan: fitur Laundry seperti Export Excel, laporan lanjutan, simpan pelanggan, metode pembayaran tambahan, dan akses aplikasi kasir mobile hanya aktif bila termasuk dalam paket.

### 5. Jasa Sewa

Menu outlet: Dashboard Toko · Booking · Sewa (Sewa Baru, Sewa Aktif, Perpanjangan, Pengembalian) · Barang (Barang Sewa, Unit, Ketersediaan, Maintenance) · Transaksi (Pembayaran, Deposit, Denda, Refund, Pengeluaran) · Pelanggan (Semua Pelanggan, Tambah Pelanggan, Dokumen) · Laporan (Pendapatan, Rental, Barang, Denda, Deposit, Laba & Rugi) · Lainnya (Pengaturan Rental, Tagihan).

Modul Sewa belum termasuk paket Pro bawaan; tanyakan ke Administrator paket yang membukanya. Menu Booking (Booking Baru, Booking Aktif, Kalender Rental, Booking Selesai/Batal) masih bertanda "Sedang dikembangkan".

Langkah awal:
1. Pengaturan Rental:
   1. Tab Persyaratan Dokumen — klik Tambah Persyaratan (misalnya KTP, SIM). Wajib berarti pelanggan tidak bisa menyewa sebelum dokumen itu terverifikasi; Opsional tidak menghalangi.
   2. Tab Pembayaran — aktifkan metode bayar; perubahan tersimpan otomatis.
   3. Tab Struk — pilih thermal 80 mm atau surat A4.
2. Barang Sewa — klik Tambah Barang; isi nama, kategori, deskripsi, dan hingga 4 foto.
3. Unit — tambahkan unit fisik per barang, isi Kode Unit dan Kondisi. Status unit: Tersedia, Disewa, Maintenance, Rusak.
4. Pelanggan — daftarkan pelanggan, unggah dokumennya (foto atau PDF), lalu buka Dokumen dan klik Verifikasi atau Tolak (dengan alasan).

Alur sewa:
1. Sewa Baru — pilih pelanggan (dokumen wajibnya harus terverifikasi) dan unit berstatus Tersedia. Pilih Jenis Sewa (Per Jam, Per Hari, Per Bulan), isi Biaya Sewa, Deposit, dan jumlah Dibayar Sekarang, pilih metode bayar, lalu klik Buat Sewa.
2. Sewa Aktif — pantau barang yang sedang di tangan pelanggan, termasuk yang Jatuh Tempo Hari Ini dan Terlambat. Klik Proses Bayar untuk melunasi sisa tagihan.
3. Perpanjangan — tambah durasi sewa yang sedang berjalan.
4. Pengembalian — klik Proses Pengembalian, isi catatan kondisi barang dan denda bila ada. Sisa tagihan dan denda bisa dipotong dari deposit; sisa deposit ditampilkan untuk dikembalikan. Klik Selesaikan Pengembalian.
5. Refund — tandai deposit yang sudah dikembalikan ke pelanggan. Denda yang belum lunas bisa dibayar dari menu Denda.
6. Maintenance — catat perbaikan unit; klik selesai agar unit kembali Tersedia. Ketersediaan menampilkan status semua unit dan bisa diubah manual.

Laporan Sewa terpisah per topik: Pendapatan, Rental, Barang, Denda, Deposit, dan Laba & Rugi.

## Tips & FAQ

- Tidak bisa menambah outlet atau kasir baru: batas paket tercapai. Cek batasnya di Paket Pro, lalu upgrade atau aktifkan kode baru.
- Kasir baru tidak bisa login: user baru wajib verifikasi email. Pastikan juga akunnya sudah diberi akses outlet di Karyawan → Detail → Akun Sistem.
- Menu Opening Stok / Closing Harian tidak muncul: hanya ada di outlet F&B, dan Opening Stok Harian harus aktif di Pengaturan Outlet.
- Kasir tidak bisa transaksi di pagi hari (F&B): Opening Stok hari itu belum diisi. Isi dulu, atau matikan Opening Stok Harian.
- Stok tidak sesuai: stok tidak diedit langsung. Koreksi lewat Tambah Stok, Waste / Barang Rusak, atau batalkan opening lalu isi ulang.
- QRIS Pay tidak bisa dinyalakan: metode ini hanya bisa diaktifkan Administrator. Hubungi admin lewat tombol Chat.
- Transaksi salah input: buka Riwayat Transaksi, pilih transaksinya, lalu void.
- Gaji karyawan belum masuk laporan: gunakan Catat Pembayaran di detail karyawan; pembayaran otomatis menjadi pengeluaran outlet.
- Ada tagihan outlet di Dashboard: buka Tagihan di outlet itu, bayar ke admin, lalu masukkan kode pelunasan lewat Bayar dengan Kode.
- Pelanggan sewa tidak muncul di Sewa Baru: dokumen wajibnya belum terverifikasi. Verifikasi di menu Dokumen.

Tips: aktifkan Opening Shift di setiap outlet agar selisih kas per kasir terpantau, dan periksa Laporan Laba & Rugi tiap akhir bulan.
