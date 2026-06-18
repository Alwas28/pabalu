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
            $table->char('type_code', 2)->nullable()->unique()->after('slug');
        });

        Schema::table('outlets', function (Blueprint $table) {
            $table->char('code', 4)->nullable()->unique()->after('name');
        });

        // Assign type codes in sort_order sequence
        DB::table('outlet_types')->orderBy('sort_order')->get()->each(function ($type, $i) {
            DB::table('outlet_types')
                ->where('id', $type->id)
                ->update(['type_code' => str_pad($i + 1, 2, '0', STR_PAD_LEFT)]);
        });

        // Backfill code for existing outlets
        DB::table('outlets')->whereNull('code')->orderBy('id')->get()->each(function ($outlet) {
            DB::table('outlets')->where('id', $outlet->id)->update([
                'code' => self::generateUniqueCode(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('outlet_types', function (Blueprint $table) {
            $table->dropColumn('type_code');
        });
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }

    private static function generateUniqueCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (DB::table('outlets')->where('code', $code)->exists());

        return $code;
    }
};
