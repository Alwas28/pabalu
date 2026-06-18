<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','open','preparing','served','paid','cancelled') NOT NULL DEFAULT 'open'");

        // Pindahkan self-order lama (user_id=null, status=open/preparing) ke pending
        DB::statement("UPDATE orders SET status='pending' WHERE user_id IS NULL AND status IN ('open','preparing')");
    }

    public function down(): void
    {
        DB::statement("UPDATE orders SET status='open' WHERE status='pending'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('open','preparing','served','paid','cancelled') NOT NULL DEFAULT 'open'");
    }
};
