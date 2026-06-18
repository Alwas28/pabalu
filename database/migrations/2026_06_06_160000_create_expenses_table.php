<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('category', 30)->default('lainnya');
            $table->string('description', 200);
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
