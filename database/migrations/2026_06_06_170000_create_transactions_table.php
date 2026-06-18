<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_number', 30)->unique();
            $table->date('date');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total');
            $table->string('payment_method', 20)->default('cash'); // cash, qris, transfer, card
            $table->unsignedBigInteger('payment_amount');
            $table->unsignedBigInteger('change_amount')->default(0);
            $table->string('status', 20)->default('completed'); // completed, voided
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'date']);
            $table->index(['outlet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
