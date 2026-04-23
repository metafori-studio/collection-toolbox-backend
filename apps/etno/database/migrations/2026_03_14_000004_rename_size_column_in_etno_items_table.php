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
            $table->renameColumn('size', 'extent');
            $table->renameColumn('size_type', 'extent_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->renameColumn('extent', 'size');
            $table->renameColumn('extent_unit', 'size_type');
        });
    }
};
