<?php

namespace App\Models;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'owner_id', 'user_id', 'outlet_id', 'name', 'position', 'whatsapp',
        'address', 'education_level', 'photo', 'join_date', 'notes', 'is_active',
    ];

    protected $casts = [
        'join_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(EmployeeSalaryPayment::class);
    }
}
