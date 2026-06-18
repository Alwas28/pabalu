<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Expense;
use App\Models\Outlet;

class EmployeeSalaryPayment extends Model
{
    protected $fillable = [
        'employee_id', 'outlet_id', 'amount', 'period_month', 'paid_at', 'notes', 'created_by', 'expense_id',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'amount'  => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPeriodLabelAttribute(): string
    {
        [$y, $m] = explode('-', $this->period_month);
        $months  = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return ($months[(int) $m] ?? $m) . ' ' . $y;
    }
}
