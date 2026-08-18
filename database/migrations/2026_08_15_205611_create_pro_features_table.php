<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pro_features', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            // route_prefix-style key: kafe, warung_makan, retail, salon, laundry, sewa.
            // Setiap fitur SELALU milik satu jenis outlet — tidak ada lagi fitur "umum".
            $table->string('outlet_type', 30);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('outlet_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_features');
    }
};
