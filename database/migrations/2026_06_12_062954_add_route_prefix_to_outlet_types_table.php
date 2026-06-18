<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlet_types', function (Blueprint $table) {
            $table->string('route_prefix', 50)->default('fnb')->after('slug');
        });

        // Assign correct route prefixes to existing types by slug
        DB::table('outlet_types')->whereIn('slug', ['kelontong', 'pakaian', 'elektronik'])->update(['route_prefix' => 'retail']);
        DB::table('outlet_types')->where('slug', 'salon')->update(['route_prefix' => 'salon']);
        DB::table('outlet_types')->where('slug', 'laundry')->update(['route_prefix' => 'laundry']);
        // warung_makan, kafe stay at 'fnb' (the column default)
    }

    public function down(): void
    {
        Schema::table('outlet_types', function (Blueprint $table) {
            $table->dropColumn('route_prefix');
        });
    }
};
