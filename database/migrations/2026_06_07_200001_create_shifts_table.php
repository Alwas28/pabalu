<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('opening_cash')->default(0);
            $table->unsignedBigInteger('closing_cash')->nullable();
            $table->unsignedBigInteger('expected_cash')->nullable();
            $table->unsignedBigInteger('cash_in')->nullable();
            $table->unsignedInteger('total_transactions')->nullable();
            $table->unsignedBigInteger('total_revenue')->nullable();
            $table->unsignedBigInteger('total_expense')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
