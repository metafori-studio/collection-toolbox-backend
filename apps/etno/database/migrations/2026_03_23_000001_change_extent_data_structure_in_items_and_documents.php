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
        Schema::table('etno_items', function (Blueprint $table) {
            $table->dropColumn(['extent', 'extent_unit']);
        });

        Schema::table('etno_items', function (Blueprint $table) {
            $table->json('extents')->nullable();
        });

        Schema::table('etno_documents', function (Blueprint $table) {
            $table->dropColumn(['extent', 'extent_unit']);
        });

        Schema::table('etno_documents', function (Blueprint $table) {
            $table->json('extents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->dropColumn('extents');
        });

        Schema::table('etno_items', function (Blueprint $table) {
            $table->string('extent')->nullable();
            $table->string('extent_unit')->nullable();
        });

        Schema::table('etno_documents', function (Blueprint $table) {
            $table->dropColumn('extents');
        });

        Schema::table('etno_documents', function (Blueprint $table) {
            $table->string('extent')->nullable();
            $table->string('extent_unit')->nullable();
        });
    }
};
