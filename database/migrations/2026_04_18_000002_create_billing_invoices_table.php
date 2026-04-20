<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 0);
            $table->string('description', 255);
            $table->string('period_label', 50)->nullable();
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->string('payment_ref', 100)->nullable()->unique();
            $table->text('snap_token')->nullable();
            $table->timestamp('snap_token_expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_invoices');
    }
};
