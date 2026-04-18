<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType      = 'string';

    protected $fillable = ['key', 'value', 'label', 'group', 'type', 'options', 'description'];

    // ── Defaults seeded on first install ───────────────
    public const DEFAULTS = [
        // Grup: Aplikasi
        ['key'=>'app_name',           'label'=>'Nama Aplikasi',          'group'=>'aplikasi', 'type'=>'text',     'value'=>'Pabalu',        'description'=>'Nama yang tampil di browser & struk'],
        ['key'=>'app_tagline',        'label'=>'Tagline',                 'group'=>'aplikasi', 'type'=>'text',     'value'=>'Sistem Manajemen UMKM', 'description'=>'Deskripsi singkat di bawah nama aplikasi'],
        ['key'=>'receipt_footer',     'label'=>'Footer Struk',            'group'=>'aplikasi', 'type'=>'textarea', 'value'=>'Terima kasih telah berbelanja!', 'description'=>'Teks baris terakhir di struk cetak'],

        // Grup: Stok
        ['key'=>'low_stock_threshold','label'=>'Batas Stok Kritis',       'group'=>'stok',     'type'=>'number',   'value'=>'5',             'description'=>'Produk dianggap menipis jika stok ≤ nilai ini'],

        // Grup: Keuangan
        ['key'=>'currency_symbol',    'label'=>'Simbol Mata Uang',        'group'=>'keuangan', 'type'=>'text',     'value'=>'Rp',            'description'=>'Ditampilkan di depan nominal harga'],
        ['key'=>'tax_percent',        'label'=>'Pajak (%)',               'group'=>'keuangan', 'type'=>'number',   'value'=>'0',             'description'=>'0 = tidak ada pajak. Fitur pajak belum aktif'],

        // Grup: Payment Gateway (Midtrans)
        ['key'=>'midtrans_enabled',       'label'=>'Aktifkan Midtrans',   'group'=>'payment', 'type'=>'toggle',   'value'=>'0',  'description'=>'Aktifkan integrasi Midtrans Snap untuk pembayaran online'],
        ['key'=>'midtrans_server_key',    'label'=>'Server Key',          'group'=>'payment', 'type'=>'password', 'value'=>'',   'description'=>'Mulai dengan SB- (Sandbox) atau live (Production). Jangan bagikan ke siapa pun'],
        ['key'=>'midtrans_client_key',    'label'=>'Client Key',          'group'=>'payment', 'type'=>'text',     'value'=>'',   'description'=>'Digunakan di frontend untuk membuka Snap popup'],
        ['key'=>'midtrans_is_production', 'label'=>'Mode Produksi',       'group'=>'payment', 'type'=>'toggle',   'value'=>'0',  'description'=>'Nonaktif = Sandbox (testing). Aktif = transaksi sungguhan'],

        // Panduan Penggunaan
        ['key'=>'user_guide', 'label'=>'Panduan Penggunaan', 'group'=>'panduan', 'type'=>'richtext', 'value'=>'', 'description'=>'Panduan penggunaan sistem untuk Owner dan Kasir'],
    ];

    // ── Helpers ────────────────────────────────────────
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", 3600, function () use ($key, $default) {
            $row = self::find($key);
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        $defaults = collect(self::DEFAULTS)->firstWhere('key', $key) ?? [];
        self::updateOrCreate(
            ['key' => $key],
            array_merge(array_filter($defaults, fn($v) => $v !== null), ['value' => $value])
        );
        Cache::forget("setting:{$key}");
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }
}
