<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori kurasi untuk homepage (mis. "Perawatan", "Sembako") — tiap kategori kurasi
        // ini merangkum satu atau lebih kategori produk asli (dari berbagai jenis outlet) via
        // tabel pivot home_category_category, supaya bisa jadi satu label belanja lintas jenis outlet.
        Schema::create('home_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('home_category_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_category_id')->constrained('home_categories')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['home_category_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_category_category');
        Schema::dropIfExists('home_categories');
    }
};
