<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_transactions', function (Blueprint $table) {
            $table->renameColumn('start_date', 'start_at');
            $table->renameColumn('end_date', 'end_at');
        });

        Schema::table('rental_transactions', function (Blueprint $table) {
            $table->dateTime('start_at')->change();
            $table->dateTime('end_at')->change();
            $table->dateTime('returned_at')->nullable()->change();
        });

        Schema::table('rental_extensions', function (Blueprint $table) {
            $table->renameColumn('previous_end_date', 'previous_end_at');
            $table->renameColumn('new_end_date', 'new_end_at');
        });

        Schema::table('rental_extensions', function (Blueprint $table) {
            $table->dateTime('previous_end_at')->change();
            $table->dateTime('new_end_at')->change();
        });
    }

    public function down(): void
    {
        Schema::table('rental_extensions', function (Blueprint $table) {
            $table->date('previous_end_at')->change();
            $table->date('new_end_at')->change();
        });

        Schema::table('rental_extensions', function (Blueprint $table) {
            $table->renameColumn('previous_end_at', 'previous_end_date');
            $table->renameColumn('new_end_at', 'new_end_date');
        });

        Schema::table('rental_transactions', function (Blueprint $table) {
            $table->date('start_at')->change();
            $table->date('end_at')->change();
            $table->date('returned_at')->nullable()->change();
        });

        Schema::table('rental_transactions', function (Blueprint $table) {
            $table->renameColumn('start_at', 'start_date');
            $table->renameColumn('end_at', 'end_date');
        });
    }
};
