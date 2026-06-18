<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number', 30)->unique();
            $table->string('table_number', 30)->nullable();
            $table->enum('status', ['open', 'preparing', 'served', 'paid', 'cancelled'])->default('open');
            $table->integer('subtotal')->default(0);
            $table->integer('discount_percent')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->integer('total')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
