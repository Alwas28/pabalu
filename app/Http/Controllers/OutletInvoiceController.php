<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\ProInvoicePaymentCode;
use App\Models\ProInvoicePaymentUsage;
use App\Models\ProOwnerInvoice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutletInvoiceController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }
        if ($user->role === 'kasir') {
            abort(403, 'Kasir tidak memiliki akses ke halaman tagihan.');
        }

        return $user;
    }

    public function index(Outlet $outlet): View
    {
        $this->authorizeOutlet($outlet);

        $invoices = $outlet->proInvoices()
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->get();

        return view('outlet.invoices', ['outlet' => $outlet, 'invoices' => $invoices]);
    }

    public function redeem(Request $request, Outlet $outlet, ProOwnerInvoice $invoice): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        if ((int) $invoice->outlet_id !== $outlet->id) {
            abort(404);
        }
        if ($invoice->status === 'lunas') {
            return back()->with('error', 'Tagihan ini sudah lunas.');
        }

        $data = $request->validate(['code' => ['required', 'string', 'max:40']]);

        $code = ProInvoicePaymentCode::where('code', strtoupper(trim($data['code'])))->first();

        if (!$code || !$code->isRedeemable()) {
            return back()->with('error', 'Kode tidak ditemukan, nonaktif, atau sudah habis dipakai.');
        }
        if (!$code->isRedeemableByOutlet($outlet->id)) {
            return back()->with('error', "Kode ini sudah mencapai batas pemakaian ({$code->max_uses_per_outlet}x) untuk outlet ini.");
        }

        DB::transaction(function () use ($code, $invoice, $outlet) {
            $invoice->update(['status' => 'lunas', 'paid_at' => now()]);

            ProInvoicePaymentUsage::create([
                'pro_invoice_payment_code_id' => $code->id,
                'outlet_id'                   => $outlet->id,
                'pro_owner_invoice_id'        => $invoice->id,
                'used_by'                     => Auth::id(),
                'used_at'                     => now(),
            ]);

            $code->increment('uses_count');
        });

        return back()->with('success', 'Tagihan berhasil dilunasi dengan kode.');
    }
}
