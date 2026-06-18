<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'user_id', 'date', 'category', 'description', 'amount', 'notes'])]
class Expense extends Model
{
    protected function casts(): array
    {
        return [
            'date'   => 'date',
            'amount' => 'decimal:2',
        ];
    }

    // System default categories (slug => label)
    public static array $categories = [
        'bahan_baku'  => 'Bahan Baku / Belanja',
        'operasional' => 'Biaya Operasional',
        'gaji'        => 'Gaji Karyawan',
        'listrik'     => 'Listrik',
        'air'         => 'Air',
        'internet'    => 'Internet / Telepon',
        'sewa'        => 'Sewa Tempat',
        'peralatan'   => 'Peralatan / Inventaris',
        'lainnya'     => 'Lain-lain',
        // backward compat — lama sebelum dipisah
        'utilitas'    => 'Listrik / Air / Internet',
    ];

    // Icon + warna per slug untuk tampilan badge
    public static array $categoryMeta = [
        'bahan_baku'  => ['icon' => 'fa-basket-shopping',    'color' => '#34d399', 'bg' => 'rgba(16,185,129,.15)'],
        'operasional' => ['icon' => 'fa-gears',              'color' => '#60a5fa', 'bg' => 'rgba(99,162,241,.15)'],
        'gaji'        => ['icon' => 'fa-users',              'color' => '#a78bfa', 'bg' => 'rgba(139,92,246,.15)'],
        'listrik'     => ['icon' => 'fa-bolt',               'color' => '#fbbf24', 'bg' => 'rgba(245,158,11,.15)'],
        'air'         => ['icon' => 'fa-droplet',            'color' => '#38bdf8', 'bg' => 'rgba(56,189,248,.15)'],
        'internet'    => ['icon' => 'fa-wifi',               'color' => '#818cf8', 'bg' => 'rgba(129,140,248,.15)'],
        'sewa'        => ['icon' => 'fa-building',           'color' => '#f97316', 'bg' => 'rgba(249,115,22,.15)'],
        'peralatan'   => ['icon' => 'fa-screwdriver-wrench', 'color' => '#fb923c', 'bg' => 'rgba(251,146,60,.15)'],
        'lainnya'     => ['icon' => 'fa-ellipsis',           'color' => '#94a3b8', 'bg' => 'rgba(148,163,184,.15)'],
        'utilitas'    => ['icon' => 'fa-bolt',               'color' => '#fbbf24', 'bg' => 'rgba(245,158,11,.15)'],
    ];

    public static function getMeta(string $slug): array
    {
        return static::$categoryMeta[$slug]
            ?? ['icon' => 'fa-tag', 'color' => '#94a3b8', 'bg' => 'rgba(148,163,184,.15)'];
    }

    public function categoryLabel(): string
    {
        return static::$categories[$this->category] ?? $this->category;
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
