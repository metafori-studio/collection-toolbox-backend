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
            $table->decimal('coordinate_x', 15, 6)->nullable()->change();
            $table->decimal('coordinate_y', 15, 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->decimal('coordinate_x', 10, 6)->nullable()->change();
            $table->decimal('coordinate_y', 10, 6)->nullable()->change();
        });
    }
};
