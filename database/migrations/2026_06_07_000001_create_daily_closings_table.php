<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->date('date');
            $table->unsignedInteger('total_transactions')->default(0);
            $table->unsignedBigInteger('total_revenue')->default(0);
            $table->unsignedBigInteger('total_discount')->default(0);
            $table->unsignedBigInteger('total_expense')->default(0);
            $table->unsignedBigInteger('cash_in')->default(0);
            $table->unsignedBigInteger('qris_in')->default(0);
            $table->unsignedBigInteger('transfer_in')->default(0);
            $table->unsignedBigInteger('card_in')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['outlet_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
