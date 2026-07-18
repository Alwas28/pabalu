<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('outlet_types')
            ->where('slug', 'jasa_sewa')
            ->where('route_prefix', 'outlets')
            ->update(['route_prefix' => 'sewa']);
    }

    public function down(): void
    {
        DB::table('outlet_types')
            ->where('slug', 'jasa_sewa')
            ->where('route_prefix', 'sewa')
            ->update(['route_prefix' => 'outlets']);
    }
};
