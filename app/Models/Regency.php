<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regency extends Model
{
    protected $fillable = ['province_id', 'name', 'type'];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function getFullNameAttribute(): string
    {
        return ($this->type === 'kota' ? 'Kota ' : 'Kabupaten ') . $this->name;
    }
}
