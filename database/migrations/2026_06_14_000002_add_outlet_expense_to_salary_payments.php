<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->foreignId('outlet_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->after('created_by')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            $table->dropForeign(['expense_id']);
            $table->dropColumn(['outlet_id', 'expense_id']);
        });
    }
};
