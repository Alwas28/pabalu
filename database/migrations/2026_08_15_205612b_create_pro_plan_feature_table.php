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
        Schema::create('pro_plan_feature', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pro_plan_id')->constrained('pro_plans')->cascadeOnDelete();
            $table->foreignId('pro_feature_id')->constrained('pro_features')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pro_plan_id', 'pro_feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_plan_feature');
    }
};
