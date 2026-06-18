<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure default roles exist before referencing them
        $now = now();
        DB::table('roles')->insertOrIgnore([
            ['name' => 'Administrator',  'slug' => 'admin',        'description' => 'Akses penuh ke seluruh sistem',                   'is_system' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pemilik Toko',   'slug' => 'owner',        'description' => 'Kelola outlet, produk, laporan, dan user kasir', 'is_system' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Admin Outlet',   'slug' => 'admin_outlet', 'description' => 'Operasional harian dan laporan outlet sendiri',  'is_system' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kasir',          'slug' => 'kasir',        'description' => 'POS, stok harian, pengeluaran, antrian order',   'is_system' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('role')
                ->constrained('roles')
                ->nullOnDelete();
        });

        // Populate role_id from existing string values
        DB::statement('UPDATE users SET role_id = (SELECT id FROM roles WHERE roles.slug = users.role) WHERE users.role IS NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('email_verified_at');
        });

        DB::statement('UPDATE users SET role = (SELECT slug FROM roles WHERE roles.id = users.role_id) WHERE users.role_id IS NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
