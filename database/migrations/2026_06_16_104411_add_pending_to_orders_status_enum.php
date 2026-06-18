<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'open', 'preparing', 'served', 'paid', 'cancelled'])
                ->default('open')
                ->change();
        });

        // Pindahkan self-order lama (user_id=null, status=open/preparing) ke pending
        DB::statement("UPDATE orders SET status='pending' WHERE user_id IS NULL AND status IN ('open','preparing')");
    }

    public function down(): void
    {
        DB::statement("UPDATE orders SET status='open' WHERE status='pending'");

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['open', 'preparing', 'served', 'paid', 'cancelled'])
                ->default('open')
                ->change();
        });
    }
};
