<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerSetting extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function get(string $key, int $userId, mixed $default = null): mixed
    {
        $row = static::where('user_id', $userId)->where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function set(string $key, mixed $value, int $userId): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value'   => $value]
        );
    }

    public static function getForOwner(int $userId): array
    {
        return static::where('user_id', $userId)
            ->pluck('value', 'key')
            ->toArray();
    }
}
