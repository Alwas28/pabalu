<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalary extends Model
{
    protected $fillable = [
        'employee_id', 'amount', 'notes', 'effective_date', 'is_active', 'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'amount'         => 'integer',
        'is_active'      => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
