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
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->renameColumn('paid_at', 'effective_date');
            $table->boolean('is_active')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->enum('type', ['gaji', 'bonus', 'thr', 'potongan', 'lainnya'])->default('gaji')->after('employee_id');
            $table->renameColumn('effective_date', 'paid_at');
            $table->dropColumn('is_active');
        });
    }
};
