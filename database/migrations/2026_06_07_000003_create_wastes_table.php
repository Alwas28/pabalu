<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'date']);
        });

        Schema::create('waste_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 200);
            $table->unsignedInteger('qty');
            $table->string('reason', 30)->default('lainnya');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_items');
        Schema::dropIfExists('wastes');
    }
};
