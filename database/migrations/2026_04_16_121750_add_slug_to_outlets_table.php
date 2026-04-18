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
        Schema::table('outlets', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('nama');
        });

        // Auto-fill slug for existing outlets
        foreach (\App\Models\Outlet::all() as $outlet) {
            $base = \Illuminate\Support\Str::slug($outlet->nama);
            $slug = $base;
            $i = 1;
            while (\App\Models\Outlet::where('slug', $slug)->where('id', '!=', $outlet->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $outlet->updateQuietly(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
