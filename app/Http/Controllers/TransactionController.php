<?php

namespace App\Http\Controllers;

use App\Http\Controllers\OwnerPaymentMethodController;
use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TransactionController extends Controller
{
    // ── POS UI ──────────────────────────────────────────────
    public function pos(Request $request): View
    {
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $tanggal          = today()->toDateString();

        $products = collect();
        $categories = collect();

        if ($outletId) {
            $products = Product::where('outlet_id', $outletId)
                ->where('is_active', true)
                ->with('category')
                ->orderBy('nama')
                ->get()
                ->map(function ($p) use ($outletId, $tanggal) {
                    $p->stok = StockMovement::currentStock($outletId, $p->id, $tanggal);
                    return $p;
                });

            $categories = $products->pluck('category')->filter()->unique('id')->values();
        }

        // Metode pembayaran aktif berdasarkan outlet owner
        $outlet          = $outletId ? Outlet::find($outletId) : null;
        $activeMethods   = $outlet
            ? OwnerPaymentMethodController::activeFor($outlet->owner_id)
            : ['tunai', 'qris', 'transfer'];

        return view('transactions.pos', compact('outlets', 'outletId', 'assignedOutletId', 'products', 'categories', 'tanggal', 'activeMethods'));
    }

    // ── Simpan Transaksi ──────────────────────────────────
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        // Force outlet if user is bound to one
        if ($assignedOutletId = auth()->user()->assignedOutletId()) {
            $request->merge(['outlet_id' => $assignedOutletId]);
        }

        $request->validate([
            'outlet_id'    => ['required', 'exists:outlets,id'],
            'metode_bayar' => ['required', 'in:tunai,qris,transfer'],
            'bayar'        => ['required_if:metode_bayar,tunai', 'nullable', 'numeric', 'min:0'],
            'items'        => ['required', 'string'],
            'keterangan'   => ['nullable', 'string', 'max:500'],
            'bukti_bayar'  => ['required_if:metode_bayar,qris', 'required_if:metode_bayar,transfer',
                               'nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $items = json_decode($request->items, true);

        if (empty($items)) {
            return back()->with('error', 'Keranjang belanja kosong.');
        }

        $tanggal = today()->toDateString();

        // ── Validasi stok setiap item ──────────────────
        foreach ($items as $item) {
            $stokTersedia = StockMovement::currentStock(
                $request->outlet_id,
                $item['product_id'],
                $tanggal
            );
            if ($item['qty'] > $stokTersedia) {
                return back()->with('error',
                    "Stok \"{$item['nama']}\" tidak mencukupi. Tersedia: {$stokTersedia}, diminta: {$item['qty']}."
                );
            }
        }

        $total   = collect($items)->sum(fn($i) => $i['subtotal']);
        $metode  = $request->metode_bayar;

        if ($metode === 'tunai') {
            $bayar = (float) $request->bayar;
            if ($bayar < $total) {
                return back()->with('error', 'Jumlah bayar kurang dari total transaksi.');
            }
        } else {
            $bayar = $total;
        }

        $buktiBayar = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiBayar = $request->file('bukti_bayar')->store('bukti-bayar', 'public');
        }

        [$transaction, $nomor] = DB::transaction(function () use ($request, $tanggal, $total, $bayar, $metode, $buktiBayar, $items) {
            $nomor = Transaction::generateNomor($request->outlet_id, $tanggal);

            $trx = Transaction::create([
                'outlet_id'       => $request->outlet_id,
                'kasir_id'        => auth()->id(),
                'nomor_transaksi' => $nomor,
                'tanggal'         => $tanggal,
                'total'           => $total,
                'bayar'           => $bayar,
                'kembalian'       => $bayar - $total,
                'keterangan'      => $request->keterangan,
                'status'          => 'paid',
                'metode_bayar'    => $metode,
                'bukti_bayar'     => $buktiBayar,
            ]);

            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $item['product_id'],
                    'nama_produk'    => $item['nama'],
                    'harga_satuan'   => $item['harga'],
                    'qty'            => $item['qty'],
                    'subtotal'       => $item['subtotal'],
                ]);
            }

            return [$trx, $nomor];
        });

        ActivityLog::record('create_transaction',
            "Transaksi {$nomor} dibuat. Total: Rp " . number_format($total, 0, ',', '.'),
            $transaction
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'id'          => $transaction->id,
                'nomor'       => $nomor,
                'total'       => $total,
                'bayar'       => $transaction->bayar,
                'kembalian'   => $transaction->kembalian,
                'metode'      => $metode,
                'receipt_url' => route('transactions.show', $transaction),
            ]);
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Transaksi {$nomor} berhasil disimpan.");
    }

    // ── Detail / Struk ───────────────────────────────────
    public function show(Transaction $transaction): View
    {
        $transaction->load(['outlet', 'kasir', 'items']);
        return view('transactions.receipt', compact('transaction'));
    }

    // ── Riwayat Transaksi ────────────────────────────────
    public function index(Request $request): View
    {
        $user             = auth()->user();
        $outlets          = $user->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = $user->assignedOutletId();
        $kasirId          = $user->isKasir() ? $user->id : null;

        $accessibleOutletIds = $user->isAdmin()
            ? null
            : $user->accessibleOutlets()->pluck('id');

        $query = Transaction::with(['outlet', 'kasir', 'items'])
            ->withCount('items')
            ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId))
            ->when($accessibleOutletIds, fn($q) => $q->whereIn('outlet_id', $accessibleOutletIds));

        $outletId = $assignedOutletId ?? $request->get('outlet_id');
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        if ($tanggal = $request->get('tanggal')) {
            $query->where('tanggal', $tanggal);
        } else {
            $query->where('tanggal', today()->toDateString());
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($metode = $request->get('metode_bayar')) {
            $query->where('metode_bayar', $metode);
        }

        $statsQuery   = clone $query;
        $transactions = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total_transaksi' => $statsQuery->count(),
            'total_omzet'     => (clone $statsQuery)->where('status', 'paid')->sum('total'),
        ];

        return view('transactions.index', compact('transactions', 'outlets', 'outletId', 'assignedOutletId', 'stats'));
    }

    // ── Void Transaksi ───────────────────────────────────
    public function void(Transaction $transaction): RedirectResponse
    {
        if ($transaction->status === 'void') {
            return back()->with('error', 'Transaksi sudah divoid.');
        }

        $transaction->update(['status' => 'void']);

        ActivityLog::record('void_transaction',
            "Transaksi {$transaction->nomor_transaksi} divoid.",
            $transaction
        );

        return back()->with('success', "Transaksi {$transaction->nomor_transaksi} berhasil divoid.");
    }
}
