<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('role_id');
            $table->string('business_name', 150)->nullable()->after('phone');
            $table->timestamp('setup_completed_at')->nullable()->after('business_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'business_name', 'setup_completed_at']);
        });
    }
};
