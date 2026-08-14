<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesOutlet;
use App\Http\Controllers\Concerns\RequiresActiveShift;
use App\Http\Controllers\Concerns\ResolvesBusinessDate;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\TransactionResource;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Mendukung outlet F&B (mode "quick" saja), Retail, dan Salon — ketiganya berbagi bentuk
// transaksi "keranjang produk -> checkout" yang identik di web (Fnb\, Retail\, Salon\ PosController).
// Laundry & Sewa/Rental TIDAK didukung di sini — alurnya beda total (order bertahap/rental),
// lihat Api\V1\LaundryController untuk Laundry.
class PosController extends Controller
{
    use AuthorizesOutlet, RequiresActiveShift, ResolvesBusinessDate;

    private const SUPPORTED_PREFIXES = ['fnb', 'retail', 'salon'];

    private function ensureSupported(Outlet $outlet): void
    {
        $outlet->loadMissing('outletType');

        if (!in_array($outlet->rp(), self::SUPPORTED_PREFIXES, true) || $outlet->order_mode === 'kitchen') {
            abort(501, 'Jenis outlet atau mode order ini belum didukung API mobile.');
        }
    }

    // Mirip Fnb\PosController (requires_opening_stock) & Salon\PosController (requires_opening_stock
    // untuk gate alur opening-stok) — tapi Retail\PosController TIDAK PERNAH memakai alur ini sama sekali.
    private function usesOpeningStockWorkflow(Outlet $outlet): bool
    {
        if ($outlet->rp() === 'retail') {
            return false;
        }

        return $outlet->outletType?->requires_opening_stock ?? false;
    }

    // Menentukan apakah stok produk dicek & dikurangi saat checkout.
    // Fnb: ikut requires_opening_stock. Retail: SELALU (Retail\PosController tanpa syarat).
    // Salon: ikut track_cogs (Salon\PosController).
    private function tracksStock(Outlet $outlet): bool
    {
        return match ($outlet->rp()) {
            'retail' => true,
            'salon'  => $outlet->outletType?->track_cogs ?? false,
            default  => $outlet->outletType?->requires_opening_stock ?? false,
        };
    }

    public function categories(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorizeOutlet($request, $outlet);
        $this->ensureSupported($outlet);

        $categories = $outlet->categories()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => CategoryResource::collection($categories)]);
    }

    public function products(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorizeOutlet($request, $outlet);
        $this->ensureSupported($outlet);

        $products = $outlet->products()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => ProductResource::collection($products)]);
    }

    public function index(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);

        $transactions = $outlet->transactions()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }

    public function show(Request $request, Outlet $outlet, Transaction $transaction): JsonResponse
    {
        $this->authorizeOutlet($request, $outlet);

        if ($transaction->outlet_id !== $outlet->id) {
            abort(404);
        }

        $transaction->load(['items', 'user']);

        return response()->json(['data' => new TransactionResource($transaction)]);
    }

    // Cermin logika Fnb\/Retail\/Salon\ PosController::store() (web) — lihat file-file itu untuk versi kanonis.
    public function store(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);
        $this->ensureSupported($outlet);

        if ($this->shiftRequired($outlet, $user)) {
            return response()->json(['message' => 'Shift belum dibuka. Buka shift terlebih dahulu.'], 422);
        }

        if ($this->usesOpeningStockWorkflow($outlet) && !$this->isBusinessDayOpen($outlet)) {
            return response()->json(['message' => 'Opening stok belum dilakukan untuk hari ini.'], 422);
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
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $businessDate = $this->getBusinessDate($outlet);
        $tracksStock  = $this->tracksStock($outlet);
        $transaction  = null;

        try {
            $this->createTransaction($request, $outlet, $user, $businessDate, $tracksStock, $transaction);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        \assert($transaction instanceof Transaction);
        $transaction->load(['items', 'user']);

        return response()->json(['data' => new TransactionResource($transaction)], 201);
    }

    private function createTransaction(Request $request, Outlet $outlet, User $user, string $businessDate, bool $tracksStock, ?Transaction &$transaction): void
    {
        DB::transaction(function () use ($request, $outlet, $user, $businessDate, $tracksStock, &$transaction) {
            $subtotal  = 0;
            $validated = [];

            foreach ($request->items as $item) {
                $product = $outlet->products()->where('id', $item['id'])->where('is_active', true)->first();

                if (!$product) {
                    throw new \Exception('Produk tidak ditemukan atau tidak aktif.');
                }
                if ($tracksStock && $product->stock < $item['qty']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Tersisa: {$product->stock}.");
                }

                $lineTotal = $product->price * $item['qty'];
                $subtotal += $lineTotal;
                $validated[] = ['product' => $product, 'qty' => $item['qty'], 'subtotal' => $lineTotal];
            }

            $discountType  = $request->input('discount_type', 'percent');
            $discountValue = (int) $request->input('discount_value', 0);
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
                'reference_number'   => $request->input('reference_number'),
            ]);

            foreach ($validated as $v) {
                $transaction->items()->create([
                    'product_id'    => $v['product']->id,
                    'product_name'  => $v['product']->name,
                    'product_price' => $v['product']->price,
                    'qty'           => $v['qty'],
                    'subtotal'      => $v['subtotal'],
                ]);

                if ($tracksStock) {
                    $v['product']->decrement('stock', $v['qty']);
                }
            }
        });
    }
}
