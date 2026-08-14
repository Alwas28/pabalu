<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_item_id')->constrained()->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('condition', 100)->nullable();
            $table->enum('status', ['tersedia', 'disewa', 'maintenance', 'rusak'])->default('tersedia');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['rental_item_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_units');
    }
};
