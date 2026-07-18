<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // kasir penerima
            $table->string('order_number', 40)->unique();
            $table->string('customer_name', 120);
            $table->string('customer_phone', 30)->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('estimated_done_at')->nullable();
            $table->enum('status', ['masuk', 'proses', 'selesai', 'diambil'])->default('masuk');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->string('payment_method', 30)->nullable();
            $table->unsignedBigInteger('payment_amount')->nullable();
            $table->unsignedBigInteger('change_amount')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'status']);
            $table->index(['outlet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_orders');
    }
};
