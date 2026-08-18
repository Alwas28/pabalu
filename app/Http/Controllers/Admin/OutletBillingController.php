<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\ProInvoicePaymentCode;
use App\Models\ProOwnerInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OutletBillingController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola tagihan outlet.');
        }
    }

    public function index(): View
    {
        $this->authorizeAdmin();

        $outlets = Outlet::with(['owner', 'outletType'])
            ->withCount(['proInvoices as unpaid_invoices_count' => function ($q) {
                $q->where('status', 'belum_lunas');
            }])
            ->orderBy('name')
            ->get();

        return view('admin.outlet-billing.index', ['tab' => 'outlet', 'outlets' => $outlets, 'summary' => $this->revenueSummary()]);
    }

    public function paymentCodes(): View
    {
        $this->authorizeAdmin();

        $codes = ProInvoicePaymentCode::with(['usages.outlet.owner'])->latest()->get();

        return view('admin.outlet-billing.index', ['tab' => 'kode', 'codes' => $codes, 'summary' => $this->revenueSummary()]);
    }

    // Tagihan yang lunas TANPA kode (ditandai admin langsung) atau lewat kode BERBAYAR
    // (is_free=false) dihitung sebagai pendapatan aplikasi. Tagihan yang lunas lewat kode
    // GRATIS (is_free=true) dihitung terpisah sebagai "digratiskan", bukan pendapatan.
    // Rincian per-tagihan (paid_invoices/free_invoices) diikutkan supaya kartu ringkasan
    // di halaman bisa dibuka detailnya (bukan cuma angka total).
    private function revenueSummary(): array
    {
        $invoices = ProOwnerInvoice::where('status', 'lunas')
            ->with(['outlet', 'paymentUsage.code'])
            ->orderByDesc('paid_at')
            ->get();

        $paidInvoices = $invoices->filter(fn ($inv) => !$inv->paymentUsage?->code?->is_free)->values();
        $freeInvoices = $invoices->filter(fn ($inv) => $inv->paymentUsage?->code?->is_free)->values();

        return [
            'paid_total'    => $paidInvoices->sum('amount'),
            'paid_count'    => $paidInvoices->count(),
            'paid_invoices' => $paidInvoices,
            'free_total'    => $freeInvoices->sum('amount'),
            'free_count'    => $freeInvoices->count(),
            'free_invoices' => $freeInvoices,
        ];
    }

    public function storePaymentCode(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'qty'                  => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_uses'             => ['required', 'integer', 'min:1'],
            'max_uses_per_outlet'  => ['nullable', 'integer', 'min:1'],
            'expires_at'           => ['nullable', 'date'],
        ]);

        $qty = $data['qty'] ?? 1;

        for ($i = 0; $i < $qty; $i++) {
            ProInvoicePaymentCode::create([
                'code'                 => $this->generateUniquePaymentCode(),
                'max_uses'             => $data['max_uses'],
                'max_uses_per_outlet'  => $data['max_uses_per_outlet'] ?? null,
                'uses_count'           => 0,
                'is_active'            => true,
                'expires_at'           => $data['expires_at'] ?? null,
                'is_free'              => $request->boolean('is_free'),
                'created_by'           => Auth::id(),
            ]);
        }

        return back()->with('success', "{$qty} kode pelunasan berhasil dibuat.");
    }

    public function revenueDetail(string $type): View
    {
        $this->authorizeAdmin();

        if (!in_array($type, ['dibayar', 'gratis'], true)) {
            abort(404);
        }

        $summary = $this->revenueSummary();

        return view('admin.outlet-billing.revenue-detail', [
            'type'     => $type,
            'invoices' => $type === 'dibayar' ? $summary['paid_invoices'] : $summary['free_invoices'],
            'total'    => $type === 'dibayar' ? $summary['paid_total'] : $summary['free_total'],
            'count'    => $type === 'dibayar' ? $summary['paid_count'] : $summary['free_count'],
        ]);
    }

    public function showPaymentCode(ProInvoicePaymentCode $paymentCode): View
    {
        $this->authorizeAdmin();

        $paymentCode->load(['creator', 'usages' => function ($q) {
            $q->with(['outlet.owner', 'invoice'])->orderByDesc('used_at');
        }]);

        return view('admin.outlet-billing.code-usage', ['code' => $paymentCode]);
    }

    public function destroyPaymentCode(ProInvoicePaymentCode $paymentCode): RedirectResponse
    {
        $this->authorizeAdmin();

        $codeText = $paymentCode->code;
        $paymentCode->delete();

        return back()->with('success', "Kode \"{$codeText}\" berhasil dihapus.");
    }

    private function generateUniquePaymentCode(): string
    {
        do {
            $code = 'LUNAS-' . Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
        } while (ProInvoicePaymentCode::where('code', $code)->exists());

        return $code;
    }

    public function show(Outlet $outlet): View
    {
        $this->authorizeAdmin();

        $outlet->load(['owner', 'outletType']);

        $invoices = $outlet->proInvoices()
            ->with('creator')
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->get();

        return view('admin.outlet-billing.show', ['outlet' => $outlet, 'invoices' => $invoices]);
    }

    // Rentang tanggal periode dari input bulan/tahun — dipakai bareng oleh endpoint hitung
    // (calcTransaction) dan penyimpanan tagihan (storeInvoice) supaya keduanya konsisten.
    private function resolvePeriod(string $periodType, int $year, ?int $month): array
    {
        if ($periodType === 'bulan') {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
        } else {
            $start = Carbon::create($year, 1, 1)->startOfYear();
            $end   = $start->copy()->endOfYear();
        }

        return [$start->toDateString(), $end->toDateString()];
    }

    private function periodValidationRules(): array
    {
        return [
            'period_type' => ['required', 'in:bulan,tahun'],
            'year'        => ['required', 'integer', 'min:2020', 'max:2100'],
            'month'       => ['required_if:period_type,bulan', 'nullable', 'integer', 'min:1', 'max:12'],
        ];
    }

    // AJAX: hitung total transaksi (omset) outlet pada periode yang dipilih, supaya admin
    // punya acuan sebelum menentukan nominal tagihan secara manual.
    public function calcTransaction(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate($this->periodValidationRules());

        [$start, $end] = $this->resolvePeriod($data['period_type'], (int) $data['year'], isset($data['month']) ? (int) $data['month'] : null);

        $query = $outlet->transactions()->where('status', 'completed')->whereBetween('date', [$start, $end]);

        return response()->json([
            'period_start'      => $start,
            'period_end'        => $end,
            'transaction_total' => (int) $query->sum('total'),
            'transaction_count' => (clone $query)->count(),
        ]);
    }

    public function storeInvoice(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate($this->periodValidationRules() + [
            'amount' => ['required', 'integer', 'min:0'],
            'note'   => ['nullable', 'string', 'max:500'],
        ]);

        [$start, $end] = $this->resolvePeriod($data['period_type'], (int) $data['year'], isset($data['month']) ? (int) $data['month'] : null);

        $transactionTotal = $outlet->transactions()->where('status', 'completed')->whereBetween('date', [$start, $end])->sum('total');

        ProOwnerInvoice::create([
            'outlet_id'         => $outlet->id,
            'period_type'       => $data['period_type'],
            'period_start'      => $start,
            'period_end'        => $end,
            'transaction_total' => $transactionTotal,
            'amount'            => $data['amount'],
            'note'              => $data['note'] ?? null,
            'status'            => 'belum_lunas',
            'created_by'        => Auth::id(),
        ]);

        return back()->with('success', "Tagihan untuk outlet \"{$outlet->name}\" berhasil dibuat.");
    }

    public function markInvoicePaid(ProOwnerInvoice $invoice): RedirectResponse
    {
        $this->authorizeAdmin();

        $invoice->update(['status' => 'lunas', 'paid_at' => now()]);

        return back()->with('success', 'Tagihan ditandai lunas.');
    }

    public function destroyInvoice(ProOwnerInvoice $invoice): RedirectResponse
    {
        $this->authorizeAdmin();

        $invoice->delete();

        return back()->with('success', 'Tagihan berhasil dihapus.');
    }
}
