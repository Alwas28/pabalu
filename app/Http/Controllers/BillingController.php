<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BillingInvoice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $query = BillingInvoice::with(['owner', 'creator'])->latest();

        if ($ownerId = $request->get('owner_id')) {
            $query->where('user_id', $ownerId);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(25)->withQueryString();
        $owners   = User::role('owner')->orderBy('name')->get();

        $stats = [
            'total'   => BillingInvoice::count(),
            'unpaid'  => BillingInvoice::where('status', 'unpaid')->count(),
            'paid'    => BillingInvoice::where('status', 'paid')->count(),
            'overdue' => BillingInvoice::where('status', 'unpaid')->where('due_date', '<', today())->count(),
        ];

        return view('admin.billing.index', compact('invoices', 'owners', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'      => ['required', 'exists:users,id'],
            'amount'       => ['required', 'numeric', 'min:1000'],
            'description'  => ['required', 'string', 'max:255'],
            'period_label' => ['nullable', 'string', 'max:50'],
            'due_date'     => ['required', 'date', 'after_or_equal:today'],
        ]);

        $owner = User::findOrFail($request->user_id);
        abort_unless($owner->isOwner(), 422, 'Pengguna bukan owner.');

        $invoice = BillingInvoice::create([
            'user_id'      => $request->user_id,
            'created_by'   => auth()->id(),
            'amount'       => $request->amount,
            'description'  => $request->description,
            'period_label' => $request->period_label,
            'due_date'     => $request->due_date,
            'status'       => 'unpaid',
        ]);

        ActivityLog::record('billing_invoice_created',
            "Tagihan Rp " . number_format($request->amount, 0, ',', '.') . " dibuat untuk owner \"{$owner->name}\".",
            $invoice
        );

        return back()->with('success', "Tagihan untuk \"{$owner->name}\" berhasil dibuat.");
    }

    public function cancel(BillingInvoice $invoice): RedirectResponse
    {
        abort_unless($invoice->status === 'unpaid', 422, 'Hanya tagihan unpaid yang bisa dibatalkan.');

        $invoice->update(['status' => 'cancelled']);

        ActivityLog::record('billing_invoice_cancelled',
            "Tagihan #{$invoice->id} untuk owner \"{$invoice->owner->name}\" dibatalkan.",
            $invoice
        );

        return back()->with('success', "Tagihan #{$invoice->id} berhasil dibatalkan.");
    }

    public function destroy(BillingInvoice $invoice): RedirectResponse
    {
        abort_unless($invoice->status === 'cancelled', 403, 'Hanya tagihan yang sudah dibatalkan yang bisa dihapus.');

        $ownerName = $invoice->owner->name ?? '—';
        $invoice->delete();

        ActivityLog::record('billing_invoice_deleted',
            "Tagihan #{$invoice->id} (owner: \"{$ownerName}\") dihapus permanen."
        );

        return back()->with('success', "Tagihan berhasil dihapus.");
    }
}
