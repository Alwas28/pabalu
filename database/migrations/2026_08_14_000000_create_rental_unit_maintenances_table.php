<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_unit_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_unit_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 255);
            $table->unsignedInteger('cost')->nullable();
            $table->date('started_at');
            $table->date('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_unit_maintenances');
    }
};
