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
            $table->unsignedBigInteger('import_id')->nullable()->after('activity_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->dropColumn('import_id');
        });
    }
};
