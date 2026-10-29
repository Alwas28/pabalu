<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Satu pesan di riwayat chat Asisten Pabalu milik user login (role: user | assistant).
class ChatbotMessage extends Model
{
    protected $fillable = ['user_id', 'role', 'content'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
