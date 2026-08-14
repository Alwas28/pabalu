<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->enum('status', ['opsional', 'wajib']);
            $table->timestamps();

            $table->unique(['outlet_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_document_requirements');
    }
};
