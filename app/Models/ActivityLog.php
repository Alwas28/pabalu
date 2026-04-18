<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'description',
        'model_type', 'model_id', 'properties', 'ip',
    ];

    protected function casts(): array
    {
        return [
            'properties'  => 'array',
            'created_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an activity.
     */
    public static function record(
        string $action,
        string $description,
        ?Model $model = null,
        array  $properties = []
    ): self {
        return self::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'description'=> $description,
            'model_type' => $model ? class_basename($model) : null,
            'model_id'   => $model?->getKey(),
            'properties' => $properties ?: null,
            'ip'         => Request::ip(),
        ]);
    }

    // ── Label grup aksi ────────────────────────────────
    public static function actionLabel(string $action): string
    {
        return [
            'login'               => 'Login',
            'logout'              => 'Logout',
            'create_transaction'  => 'Buat Transaksi',
            'void_transaction'    => 'Void Transaksi',
            'stock_opening'       => 'Opening Stok',
            'stock_in'            => 'Tambah Stok',
            'stock_waste'         => 'Catat Waste',
            'create_expense'      => 'Buat Pengeluaran',
            'update_expense'      => 'Edit Pengeluaran',
            'delete_expense'      => 'Hapus Pengeluaran',
            'create_user'         => 'Buat User',
            'update_user'         => 'Edit User',
            'delete_user'         => 'Hapus User',
            'create_outlet'       => 'Buat Outlet',
            'update_outlet'       => 'Edit Outlet',
            'delete_outlet'       => 'Hapus Outlet',
            'create_product'      => 'Buat Produk',
            'update_product'      => 'Edit Produk',
            'delete_product'      => 'Hapus Produk',
            'create_category'     => 'Buat Kategori',
            'update_category'     => 'Edit Kategori',
            'delete_category'     => 'Hapus Kategori',
            'create_role'         => 'Buat Role',
            'update_role'         => 'Edit Role',
            'delete_role'         => 'Hapus Role',
            'update_settings'     => 'Ubah Pengaturan',
        ][$action] ?? $action;
    }

    public static function actionColor(string $action): string
    {
        if (str_contains($action, 'delete') || str_contains($action, 'void')) return 'badge-red';
        if (str_contains($action, 'create') || str_contains($action, 'opening') || $action === 'stock_in') return 'badge-green';
        if (str_contains($action, 'update') || str_contains($action, 'waste')) return 'badge-amber';
        if ($action === 'login')  return 'badge-blue';
        if ($action === 'logout') return 'badge-gray';
        return 'badge-blue';
    }
}
