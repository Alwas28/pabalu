<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('rental_unit_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 40)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_amount')->default(0);
            $table->unsignedInteger('deposit_amount')->default(0);
            $table->unsignedInteger('fine_amount')->default(0);
            $table->text('condition_note')->nullable();
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->text('notes')->nullable();
            $table->date('returned_at')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'status']);
        });

        Schema::create('rental_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_transaction_id')->constrained()->cascadeOnDelete();
            $table->date('previous_end_date');
            $table->date('new_end_date');
            $table->unsignedInteger('additional_amount')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_extensions');
        Schema::dropIfExists('rental_transactions');
    }
};
