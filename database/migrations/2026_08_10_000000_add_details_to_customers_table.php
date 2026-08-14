<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email', 150)->nullable()->after('phone');
            $table->string('address', 255)->nullable()->after('email');
            $table->string('city', 100)->nullable()->after('address');
            $table->date('birth_date')->nullable()->after('city');
            $table->enum('gender', ['L', 'P'])->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['email', 'address', 'city', 'birth_date', 'gender']);
        });
    }
};
