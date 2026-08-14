<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah index polos pada outlet_id dulu — index unique komposit yang akan
        // dihapus di bawah ini ternyata dipakai MySQL sebagai index pendukung FK.
        Schema::table('rental_document_requirements', function (Blueprint $table) {
            $table->index('outlet_id', 'rdr_outlet_id_index');
        });

        Schema::table('rental_document_requirements', function (Blueprint $table) {
            $table->dropUnique(['outlet_id', 'document_type']);
        });

        Schema::table('rental_document_requirements', function (Blueprint $table) {
            $table->renameColumn('document_type', 'name');
        });

        Schema::table('rental_document_requirements', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->unsignedInteger('sort_order')->default(0)->after('name');
            $table->unique(['outlet_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('rental_document_requirements', function (Blueprint $table) {
            $table->dropUnique(['outlet_id', 'name']);
            $table->dropColumn('sort_order');
        });

        Schema::table('rental_document_requirements', function (Blueprint $table) {
            $table->renameColumn('name', 'document_type');
        });

        Schema::table('rental_document_requirements', function (Blueprint $table) {
            $table->string('document_type', 40)->change();
            $table->unique(['outlet_id', 'document_type']);
            $table->dropIndex('rdr_outlet_id_index');
        });
    }
};
