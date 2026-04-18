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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('account_type', ['trial', 'premium', 'inactive'])->default('trial')->after('outlet_id');
            $table->timestamp('trial_ends_at')->nullable()->after('account_type');
        });

        // Set existing admin/kasir as premium (no expiry), existing owners as trial with 30 days from now
        \App\Models\User::role('admin')->update(['account_type' => 'premium']);
        \App\Models\User::role('kasir')->update(['account_type' => 'premium']);
        \App\Models\User::role('owner')->update([
            'account_type'   => 'trial',
            'trial_ends_at'  => now()->addDays(30),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'trial_ends_at']);
        });
    }
};
