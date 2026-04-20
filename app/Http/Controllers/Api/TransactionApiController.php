<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OwnerPaymentMethodController;
use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\OwnerSetting;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionApiController extends Controller
{
    // ── Config pembayaran untuk outlet ──────────────────
    public function config(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
        ]);

        $outlet        = Outlet::findOrFail($request->outlet_id);
        $activeMethods = OwnerPaymentMethodController::activeFor($outlet->owner_id);

        $midtransClientKey = null;
        $midtransSnapUrl   = null;

        if (in_array('gateway', $activeMethods)) {
            $os = OwnerSetting::getForOwner($outlet->owner_id);
            $midtransClientKey = $os['midtrans_client_key'] ?? null;
            $isProd            = ($os['midtrans_is_production'] ?? '0') === '1';
            $midtransSnapUrl   = $isProd
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js';
        }

        return response()->json([
            'active_methods'      => $activeMethods,
            'midtrans_client_key' => $midtransClientKey,
            'midtrans_snap_url'   => $midtransSnapUrl,
        ]);
    }

    // ── Snap Token untuk gateway ─────────────────────────
    public function snapToken(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'items'     => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.nama'       => ['required', 'string'],
            'items.*.harga'      => ['required', 'numeric'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.subtotal'   => ['required', 'numeric'],
        ]);

        $outlet  = Outlet::findOrFail($request->outlet_id);
        $os      = OwnerSetting::getForOwner($outlet->owner_id);

        if (empty($os['midtrans_server_key']) || ($os['midtrans_enabled'] ?? '0') !== '1') {
            return response()->json(['message' => 'Payment gateway tidak aktif untuk outlet ini.'], 422);
        }

        $items   = $request->items;
        $total   = (int) collect($items)->sum('subtotal');
        $orderId = 'POS-' . date('YmdHis') . '-' . rand(1000, 9999);

        try {
            \Midtrans\Config::$serverKey    = $os['midtrans_server_key'];
            \Midtrans\Config::$isProduction = ($os['midtrans_is_production'] ?? '0') === '1';
            \Midtrans\Config::$isSanitized  = true;
            \Midtrans\Config::$is3ds        = true;

            $snapToken = \Midtrans\Snap::getSnapToken([
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $total,
                ],
                'item_details'  => collect($items)->map(fn($i) => [
                    'id'       => (string) $i['product_id'],
                    'price'    => (int) $i['harga'],
                    'quantity' => (int) $i['qty'],
                    'name'     => mb_substr($i['nama'], 0, 50),
                ])->toArray(),
                'custom_field1' => (string) $outlet->id,
                'custom_field2' => (string) $request->user()->id,
                'custom_field3' => json_encode($items),
            ]);

            $isProd      = ($os['midtrans_is_production'] ?? '0') === '1';
            $snapRedirectUrl = ($isProd
                ? 'https://app.midtrans.com/snap/v2/vtweb/'
                : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/') . $snapToken;

            return response()->json([
                'snap_token'       => $snapToken,
                'order_id'         => $orderId,
                'snap_redirect_url'=> $snapRedirectUrl, // buka ini di WebView pada mobile
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat sesi pembayaran: ' . $e->getMessage()], 500);
        }
    }

    // ── QRIS via Core API (mobile native — tanpa WebView) ──
    public function qrisCharge(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'items'     => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.nama'       => ['required', 'string'],
            'items.*.harga'      => ['required', 'numeric'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.subtotal'   => ['required', 'numeric'],
        ]);

        $outlet = Outlet::findOrFail($request->outlet_id);
        $os     = OwnerSetting::getForOwner($outlet->owner_id);

        if (empty($os['midtrans_server_key']) || ($os['midtrans_enabled'] ?? '0') !== '1') {
            return response()->json(['message' => 'Payment gateway tidak aktif untuk outlet ini.'], 422);
        }

        $items   = $request->items;
        $total   = (int) collect($items)->sum('subtotal');
        $orderId = 'QRIS-' . date('YmdHis') . '-' . rand(1000, 9999);

        try {
            \Midtrans\Config::$serverKey    = $os['midtrans_server_key'];
            \Midtrans\Config::$isProduction = ($os['midtrans_is_production'] ?? '0') === '1';
            \Midtrans\Config::$isSanitized  = true;
            \Midtrans\Config::$is3ds        = true;

            $response = \Midtrans\CoreApi::charge([
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $total,
                ],
                'item_details' => collect($items)->map(fn($i) => [
                    'id'       => (string) $i['product_id'],
                    'price'    => (int) $i['harga'],
                    'quantity' => (int) $i['qty'],
                    'name'     => mb_substr($i['nama'], 0, 50),
                ])->toArray(),
                'qris' => ['acquirer' => 'gopay'],
                'custom_field1' => (string) $outlet->id,
                'custom_field2' => (string) $request->user()->id,
                'custom_field3' => json_encode($items),
            ]);

            // Ambil URL gambar QR dari actions
            $qrImageUrl = collect($response->actions ?? [])
                ->firstWhere('name', 'generate-qr-code')['url']
                ?? null;

            return response()->json([
                'order_id'       => $orderId,
                'transaction_id' => $response->transaction_id ?? null,
                'qr_string'      => $response->qr_string      ?? null, // raw QRIS string
                'qr_image_url'   => $qrImageUrl,                       // URL gambar QR siap tampil
                'total'          => $total,
                'expired_at'     => $response->expiry_time    ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat QRIS: ' . $e->getMessage()], 500);
        }
    }

    // ── Cek status pembayaran (polling setelah QRIS/VA) ──
    public function checkPaymentStatus(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $outlet = Outlet::findOrFail($request->outlet_id ?? 0)
            ?? Outlet::whereHas('owner', fn($q) =>
                $q->whereIn('id', $request->user()->accessibleOutlets()->pluck('owner_id'))
            )->first();

        // Cari outlet dari order_id prefix jika tidak dikirim
        $outletId = $request->outlet_id;
        if (! $outletId && $assignedId = $request->user()->assignedOutletId()) {
            $outletId = $assignedId;
        }

        if (! $outletId) {
            return response()->json(['message' => 'outlet_id diperlukan.'], 422);
        }

        $outletModel = Outlet::findOrFail($outletId);
        $os          = OwnerSetting::getForOwner($outletModel->owner_id);

        if (empty($os['midtrans_server_key'])) {
            return response()->json(['message' => 'Konfigurasi gateway tidak ditemukan.'], 422);
        }

        try {
            \Midtrans\Config::$serverKey    = $os['midtrans_server_key'];
            \Midtrans\Config::$isProduction = ($os['midtrans_is_production'] ?? '0') === '1';

            $status = \Midtrans\Transaction::status($request->order_id);

            return response()->json([
                'order_id'          => $status->order_id,
                'transaction_status'=> $status->transaction_status, // pending/settlement/expire/cancel
                'payment_type'      => $status->payment_type,
                'gross_amount'      => $status->gross_amount,
                'transaction_time'  => $status->transaction_time,
                'is_paid'           => in_array($status->transaction_status, ['settlement', 'capture']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal cek status: ' . $e->getMessage()], 500);
        }
    }

    // ── Simpan Transaksi ─────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Force outlet jika user terikat ke satu outlet
        if ($assignedOutletId = $user->assignedOutletId()) {
            $request->merge(['outlet_id' => $assignedOutletId]);
        }

        $request->validate([
            'outlet_id'    => ['required', 'exists:outlets,id'],
            'metode_bayar' => ['required', 'in:tunai,qris,transfer,gateway'],
            'bayar'        => ['required_if:metode_bayar,tunai', 'nullable', 'numeric', 'min:0'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.nama'       => ['required', 'string'],
            'items.*.harga'      => ['required', 'numeric'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.subtotal'   => ['required', 'numeric'],
            'keterangan'   => ['nullable', 'string', 'max:500'],
            'payment_ref'  => ['required_if:metode_bayar,gateway', 'nullable', 'string', 'max:100'],
        ]);

        $items   = $request->items;
        $tanggal = today()->toDateString();

        // Validasi stok
        foreach ($items as $item) {
            $stok = StockMovement::currentStock($request->outlet_id, $item['product_id'], $tanggal);
            if ($item['qty'] > $stok) {
                return response()->json([
                    'message' => "Stok \"{$item['nama']}\" tidak mencukupi. Tersedia: {$stok}, diminta: {$item['qty']}.",
                ], 422);
            }
        }

        $total  = collect($items)->sum('subtotal');
        $metode = $request->metode_bayar;
        $bayar  = $metode === 'tunai' ? (float) $request->bayar : $total;

        if ($metode === 'tunai' && $bayar < $total) {
            return response()->json(['message' => 'Jumlah bayar kurang dari total transaksi.'], 422);
        }

        [$transaction, $nomor] = DB::transaction(function () use ($request, $user, $tanggal, $total, $bayar, $metode, $items) {
            $nomor = Transaction::generateNomor($request->outlet_id, $tanggal);

            $trx = Transaction::create([
                'outlet_id'       => $request->outlet_id,
                'kasir_id'        => $user->id,
                'nomor_transaksi' => $nomor,
                'tanggal'         => $tanggal,
                'total'           => $total,
                'bayar'           => $bayar,
                'kembalian'       => $bayar - $total,
                'keterangan'      => $request->keterangan,
                'status'          => 'paid',
                'metode_bayar'    => $metode,
                'payment_ref'     => $request->payment_ref,
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
            "Transaksi {$nomor} dibuat via API mobile. Total: Rp " . number_format($total, 0, ',', '.'),
            $transaction
        );

        return response()->json([
            'success'   => true,
            'id'        => $transaction->id,
            'nomor'     => $nomor,
            'total'     => (int) $total,
            'bayar'     => (int) $transaction->bayar,
            'kembalian' => (int) $transaction->kembalian,
            'metode'    => $metode,
            'tanggal'   => $tanggal,
        ], 201);
    }

    // ── Detail Transaksi (untuk struk) ───────────────────
    public function show(Transaction $transaction): JsonResponse
    {
        $user = request()->user();

        // Kasir hanya bisa lihat transaksi miliknya
        if ($user->isKasir() && $transaction->kasir_id !== $user->id) {
            return response()->json(['message' => 'Tidak punya akses ke transaksi ini.'], 403);
        }

        $transaction->load(['outlet:id,nama,alamat,telepon', 'kasir:id,name', 'items.product:id,gambar']);

        return response()->json([
            'id'              => $transaction->id,
            'nomor_transaksi' => $transaction->nomor_transaksi,
            'tanggal'         => $transaction->tanggal,
            'outlet'          => $transaction->outlet,
            'kasir'           => $transaction->kasir,
            'metode_bayar'    => $transaction->metode_bayar,
            'total'           => (int) $transaction->total,
            'bayar'           => (int) $transaction->bayar,
            'kembalian'       => (int) $transaction->kembalian,
            'keterangan'      => $transaction->keterangan,
            'status'          => $transaction->status,
            'items_count'     => $transaction->items->count(),
            'items'           => $transaction->items->map(fn($i) => [
                'id'           => $i->id,
                'product_id'   => $i->product_id,
                'nama_produk'  => $i->nama_produk,
                'harga_satuan' => (int) $i->harga_satuan,
                'qty'          => $i->qty,
                'subtotal'     => (int) $i->subtotal,
                'foto'         => $i->product?->gambar ? asset('storage/' . $i->product->gambar) : null,
            ]),
        ]);
    }

    // ── Riwayat Transaksi kasir ──────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'outlet_id'  => ['nullable', 'exists:outlets,id'],
            'tanggal'    => ['nullable', 'date'],
            'with_items' => ['nullable', 'boolean'],
        ]);

        $outletId = $user->assignedOutletId() ?? $request->outlet_id;
        $tanggal  = $request->tanggal ?? today()->toDateString();

        $withItems = $request->boolean('with_items', false);

        $query = Transaction::with(['outlet:id,nama', 'items'])
            ->where('tanggal', $tanggal)
            ->when($user->isKasir(), fn($q) => $q->where('kasir_id', $user->id))
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->latest()
            ->limit(50);

        return response()->json($query->get()->map(fn($t) => [
            'id'              => $t->id,
            'nomor_transaksi' => $t->nomor_transaksi,
            'tanggal'         => $t->tanggal,
            'outlet'          => $t->outlet?->nama,
            'metode_bayar'    => $t->metode_bayar,
            'total'           => (int) $t->total,
            'bayar'           => (int) $t->bayar,
            'kembalian'       => (int) $t->kembalian,
            'status'          => $t->status,
            'keterangan'      => $t->keterangan,
            'items_count'     => $t->items->count(),
            'items'           => $withItems
                ? $t->items->map(fn($i) => [
                    'id'           => $i->id,
                    'product_id'   => $i->product_id,
                    'nama_produk'  => $i->nama_produk,
                    'harga_satuan' => (int) $i->harga_satuan,
                    'qty'          => $i->qty,
                    'subtotal'     => (int) $i->subtotal,
                ])
                : null,
        ]));
    }
}
