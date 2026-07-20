<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('laundry_order_id')->nullable()->after('order_id')
                ->constrained('laundry_orders')->nullOnDelete();
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['laundry_order_id']);
            $table->dropColumn('laundry_order_id');
        });
    }

    // Sebelum fitur ini ada, pesanan laundry yang sudah dibayar tidak pernah tercatat
    // di `transactions` — jadi tidak muncul di menu Transaksi maupun dashboard.
    // Backfill sekali di sini supaya riwayat lama ikut tampil.
    private function backfill(): void
    {
        $orders = DB::table('laundry_orders')
            ->where('status', 'diambil')
            ->whereNotNull('paid_at')
            ->get();

        foreach ($orders as $order) {
            if (DB::table('transactions')->where('laundry_order_id', $order->id)->exists()) {
                continue;
            }

            $outlet     = DB::table('outlets')->where('id', $order->outlet_id)->first();
            $outletType = $outlet ? DB::table('outlet_types')->where('id', $outlet->outlet_type_id)->first() : null;
            $userId     = $order->user_id ?? $outlet?->owner_id;

            if (!$outlet || !$userId) continue; // tidak ada FK user_id yang valid (NOT NULL)

            $businessDate = \Carbon\Carbon::parse($order->paid_at)->format('Y-m-d');
            $typeCode     = $outletType->type_code ?? '08';
            $outletCode   = $outlet->code ?? 'XXXX';
            $dateLabel    = \Carbon\Carbon::parse($order->paid_at)->format('ymd');
            $timeLabel    = \Carbon\Carbon::parse($order->paid_at)->format('His');

            $dailyCount = DB::table('transactions')
                ->where('outlet_id', $order->outlet_id)
                ->where('date', $businessDate)
                ->count();

            $txNumber = 'TRXPB' . $typeCode . $outletCode . $dateLabel . $timeLabel
                . str_pad($dailyCount + 1, 3, '0', STR_PAD_LEFT);

            $transactionId = DB::table('transactions')->insertGetId([
                'outlet_id'          => $order->outlet_id,
                'laundry_order_id'   => $order->id,
                'user_id'            => $userId,
                'transaction_number' => $txNumber,
                'date'               => $businessDate,
                'subtotal'           => $order->subtotal,
                'discount_percent'   => 0,
                'discount_amount'    => $order->discount_amount,
                'total'              => $order->total,
                'payment_method'     => $order->payment_method ?? 'cash',
                'payment_amount'     => $order->payment_amount ?? $order->total,
                'change_amount'      => $order->change_amount ?? 0,
                'status'             => 'completed',
                'notes'              => $order->notes,
                'created_at'         => $order->paid_at,
                'updated_at'         => $order->paid_at,
            ]);

            $items = DB::table('laundry_order_items')->where('laundry_order_id', $order->id)->get();
            foreach ($items as $item) {
                // transaction_items.qty integer, laundry_order_items.qty desimal (mis. berat kg) —
                // dibulatkan ke atas supaya item kecil (0.5kg) tidak hilang jadi qty 0.
                DB::table('transaction_items')->insert([
                    'transaction_id' => $transactionId,
                    'product_id'     => $item->product_id,
                    'product_name'   => $item->product_name . ($item->unit ? " ({$item->unit})" : ''),
                    'product_price'  => $item->product_price,
                    'qty'            => max(1, (int) ceil($item->qty)),
                    'subtotal'       => $item->subtotal,
                    'created_at'     => $order->paid_at,
                    'updated_at'     => $order->paid_at,
                ]);
            }
        }
    }
};
