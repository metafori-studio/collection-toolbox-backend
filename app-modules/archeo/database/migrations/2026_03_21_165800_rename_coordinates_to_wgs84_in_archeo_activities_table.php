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
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->renameColumn('coordinate_x', 'wgs84_coordinate_x');
            $table->renameColumn('coordinate_y', 'wgs84_coordinate_y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->renameColumn('wgs84_coordinate_x', 'coordinate_x');
            $table->renameColumn('wgs84_coordinate_y', 'coordinate_y');
        });
    }
};
