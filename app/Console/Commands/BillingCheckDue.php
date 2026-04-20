<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\BillingInvoice;
use App\Models\Setting;
use Illuminate\Console\Command;

class BillingCheckDue extends Command
{
    protected $signature   = 'billing:check-due';
    protected $description = 'Suspend owner accounts with overdue billing invoices past the grace period';

    public function handle(): int
    {
        $graceDays = (int) Setting::get('billing_grace_period', 7);
        $cutoff    = today()->subDays($graceDays);

        $overdueInvoices = BillingInvoice::where('status', 'unpaid')
            ->where('due_date', '<=', $cutoff)
            ->with('owner')
            ->get();

        $suspended = 0;

        foreach ($overdueInvoices as $invoice) {
            $owner = $invoice->owner;
            if (! $owner || $owner->account_type === 'inactive') {
                continue;
            }

            $owner->update(['account_type' => 'inactive']);

            ActivityLog::record('billing_account_suspended',
                "Akun owner \"{$owner->name}\" disuspend otomatis karena tagihan #{$invoice->id} belum dibayar (due: {$invoice->due_date->toDateString()})."
            );

            $this->line("Suspended: {$owner->name} ({$owner->email}) — Invoice #{$invoice->id}");
            $suspended++;
        }

        $this->info("Done. {$suspended} akun disuspend dari {$overdueInvoices->count()} tagihan overdue.");

        return self::SUCCESS;
    }
}
