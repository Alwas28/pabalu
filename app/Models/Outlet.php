<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['owner_id', 'outlet_type_id', 'name', 'bidang_usaha', 'code', 'address', 'phone', 'is_active', 'order_mode', 'enable_opening_shift', 'enable_barcode_scanner', 'enable_self_order', 'province_id', 'regency_id', 'district_id', 'kelurahan', 'enable_qris_transfer', 'enable_qris_pay', 'enable_transfer', 'enable_card', 'midtrans_server_key', 'midtrans_client_key', 'midtrans_is_production'])]
class Outlet extends Model
{
    protected function casts(): array
    {
        return [
            'is_active'              => 'boolean',
            'enable_opening_shift'   => 'boolean',
            'enable_barcode_scanner' => 'boolean',
            'enable_self_order'      => 'boolean',
            'enable_qris_transfer'   => 'boolean',
            'enable_qris_pay'        => 'boolean',
            'enable_transfer'        => 'boolean',
            'enable_card'            => 'boolean',
            'midtrans_is_production' => 'boolean',
        ];
    }

    /** Metode pembayaran non-tunai yang aktif untuk outlet ini: [code => [label, icon]]. */
    public function activePaymentMethods(): array
    {
        $methods = [];
        if ($this->enable_qris_transfer) $methods['qris_transfer'] = ['QRIS Transfer', 'fa-qrcode'];
        if ($this->enable_qris_pay)      $methods['qris_pay']      = ['QRIS Pay', 'fa-bolt'];
        if ($this->enable_transfer)      $methods['transfer']      = ['Transfer', 'fa-building-columns'];
        if ($this->enable_card)          $methods['card']          = ['Kartu', 'fa-credit-card'];

        return $methods;
    }

    public function isMidtransConfigured(): bool
    {
        return filled($this->midtrans_server_key) && filled($this->midtrans_client_key);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(\App\Models\District::class);
    }

    public function outletType(): BelongsTo
    {
        return $this->belongsTo(OutletType::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('sort_order')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order')->orderBy('name');
    }

    public function stockOpnames(): HasMany
    {
        return $this->hasMany(StockOpname::class);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function dailyClosings(): HasMany
    {
        return $this->hasMany(DailyClosing::class);
    }

    public function wastes(): HasMany
    {
        return $this->hasMany(Waste::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class)->orderBy('label');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'outlet_employees');
    }

    /** Route prefix for this outlet type, e.g. 'fnb', 'retail'. */
    public function rp(): string
    {
        return $this->outletType?->route_prefix ?? 'fnb';
    }

    /** Generate a URL for a named route under this outlet's type prefix. */
    public function route(string $name, array $extra = []): string
    {
        return route($this->rp() . '.' . $name, array_merge([$this], $extra));
    }

    /** Check if the current request matches a route pattern under this outlet's prefix. */
    public function routeIs(string $pattern): bool
    {
        return request()->routeIs($this->rp() . '.' . $pattern);
    }

    public function isKitchenMode(): bool
    {
        return $this->order_mode === 'kitchen';
    }

    public function isRetailFlow(): bool
    {
        return ($this->outletType?->track_cogs ?? false)
            && !($this->outletType?->requires_opening_stock ?? true);
    }

    public function isAccessibleBy(User $user): bool
    {
        if ($user->role === 'admin') return true;
        if ((int) $this->owner_id === (int) $user->id) return true;
        return $this->employees()->where('user_id', $user->id)->exists();
    }
}
