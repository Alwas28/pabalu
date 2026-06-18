<?php

namespace App\Http\Controllers\Retail;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\RequiresActiveShift;
use App\Http\Controllers\Concerns\ResolvesBusinessDate;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PosController extends Controller
{
    use RequiresActiveShift, ResolvesBusinessDate;

    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }

        return $user;
    }

    public function index(Outlet $outlet): View|RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('pos.access')) {
            abort(403);
        }

        if ($this->shiftRequired($outlet, $user)) {
            return redirect($outlet->route('shifts.index'))
                ->with('error', 'Buka shift terlebih dahulu sebelum melakukan transaksi.');
        }

        $outlet->load(['outletType', 'dailyClosings']);

        // Retail tidak menggunakan opening stok
        $requiresOpening = false;
        $openingDone     = true;

        $products = $outlet->products()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = $outlet->categories()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $orderMode  = $outlet->order_mode ?? 'quick';
        $storeUrl   = $orderMode === 'kitchen'
            ? $outlet->route('orders.store')
            : $outlet->route('pos.store');
        $csrfToken  = csrf_token();

        return view('retail.pos.index', compact(
            'outlet', 'user', 'products', 'categories',
            'requiresOpening', 'openingDone',
            'orderMode', 'storeUrl', 'csrfToken'
        ));
    }

    public function store(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('pos.access')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($this->shiftRequired($outlet, $user)) {
            return response()->json([
                'message' => 'Shift belum dibuka. Buka shift terlebih dahulu sebelum melakukan transaksi.',
            ], 422);
        }

        $request->validate([
            'items'            => ['required', 'array', 'min:1'],
            'items.*.id'       => ['required', 'integer'],
            'items.*.qty'      => ['required', 'integer', 'min:1'],
            'payment_method'   => ['required', 'in:cash,qris,transfer,card'],
            'payment_amount'   => ['required', 'integer', 'min:0'],
            'discount_type'    => ['nullable', 'in:percent,amount'],
            'discount_value'   => ['nullable', 'integer', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        // Tanggal bisnis aktif (bisa kemarin jika outlet buka melewati tengah malam)
        $businessDate = $this->getBusinessDate($outlet);

        $receipt     = null;
        $transaction = null;

        DB::transaction(function () use ($request, $outlet, $user, $businessDate, &$receipt, &$transaction) {
            $subtotal  = 0;
            $validated = [];

            foreach ($request->items as $item) {
                $product = $outlet->products()
                    ->where('id', $item['id'])
                    ->where('is_active', true)
                    ->first();

                if (!$product) {
                    throw new \Exception("Produk tidak ditemukan atau tidak aktif.");
                }

                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Tersisa: {$product->stock}.");
                }

                $lineTotal  = $product->price * $item['qty'];
                $subtotal  += $lineTotal;

                $validated[] = [
                    'product'  => $product,
                    'qty'      => $item['qty'],
                    'subtotal' => $lineTotal,
                ];
            }

            // Hitung diskon
            $discountType  = $request->input('discount_type', 'percent');
            $discountValue = (int) ($request->input('discount_value', 0));
            $discountPct   = 0;
            $discountAmt   = 0;

            if ($discountValue > 0) {
                if ($discountType === 'percent') {
                    $discountPct = min($discountValue, 100);
                    $discountAmt = (int) round($subtotal * $discountPct / 100);
                } else {
                    $discountAmt = min($discountValue, $subtotal);
                }
            }

            $total         = $subtotal - $discountAmt;
            $paymentAmount = (int) $request->payment_amount;
            $changeAmount  = max(0, $paymentAmount - $total);

            // Format: TRXPB{typeCode}{outletCode}{YYMMDD}{HHMMSS}{SEQ:03}
            // Contoh: TRXPB01ABCD260607210234001
            $outlet->loadMissing('outletType');
            $typeCode   = $outlet->outletType?->type_code ?? '00';
            $outletCode = $outlet->code ?? 'XXXX';
            $dateLabel  = \Carbon\Carbon::parse($businessDate)->format('ymd');
            $timeLabel  = now()->format('His');
            $dailyCount = Transaction::where('outlet_id', $outlet->id)
                ->where('date', $businessDate)
                ->lockForUpdate()
                ->count();
            $txNumber = 'TRXPB' . $typeCode . $outletCode . $dateLabel . $timeLabel . str_pad($dailyCount + 1, 3, '0', STR_PAD_LEFT);

            $transaction = Transaction::create([
                'outlet_id'          => $outlet->id,
                'user_id'            => $user->id,
                'transaction_number' => $txNumber,
                'date'               => $businessDate,
                'subtotal'           => $subtotal,
                'discount_percent'   => $discountPct,
                'discount_amount'    => $discountAmt,
                'total'              => $total,
                'payment_method'     => $request->payment_method,
                'payment_amount'     => $paymentAmount,
                'change_amount'      => $changeAmount,
                'status'             => 'completed',
                'notes'              => $request->notes,
            ]);

            $receiptItems = [];
            foreach ($validated as $v) {
                $transaction->items()->create([
                    'product_id'    => $v['product']->id,
                    'product_name'  => $v['product']->name,
                    'product_price' => $v['product']->price,
                    'qty'           => $v['qty'],
                    'subtotal'      => $v['subtotal'],
                ]);
                $v['product']->decrement('stock', $v['qty']);

                $receiptItems[] = [
                    'name'     => $v['product']->name,
                    'price'    => $v['product']->price,
                    'qty'      => $v['qty'],
                    'subtotal' => $v['subtotal'],
                ];
            }

            $receipt = [
                'transaction_number' => $txNumber,
                'outlet_name'        => $outlet->name,
                'cashier'            => $user->name,
                'created_at'         => now()->format('d/m/Y H:i'),
                'items'              => $receiptItems,
                'subtotal'           => $subtotal,
                'discount_amount'    => $discountAmt,
                'discount_percent'   => $discountPct,
                'total'              => $total,
                'payment_method'     => $request->payment_method,
                'payment_label'      => Transaction::$paymentLabels[$request->payment_method] ?? $request->payment_method,
                'payment_amount'     => $paymentAmount,
                'change_amount'      => $changeAmount,
                'notes'              => $request->notes,
            ];
        });

        $receiptUrl = $outlet->route('transactions.receipt', [$transaction->id]) . '?autoprint=1';

        return response()->json(['success' => true, 'receipt' => $receipt, 'receipt_url' => $receiptUrl]);
    }
}
