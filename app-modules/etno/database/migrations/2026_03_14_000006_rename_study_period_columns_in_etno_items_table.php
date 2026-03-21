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
            $table->renameColumn('study_period_start', 'time_period_start');
            $table->renameColumn('study_period_end', 'time_period_end');
            $table->renameColumn('study_period_settings', 'time_period_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->renameColumn('time_period_start', 'study_period_start');
            $table->renameColumn('time_period_end', 'study_period_end');
            $table->renameColumn('time_period_settings', 'study_period_settings');
        });
    }
};
