<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['opening', 'closing']);
            $table->enum('status', ['draft', 'submitted'])->default('submitted');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['outlet_id', 'date', 'type']);
            $table->index(['outlet_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
