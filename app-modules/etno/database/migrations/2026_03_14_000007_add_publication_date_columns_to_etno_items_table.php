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
            $table->date('publication_date_start')->nullable();
            $table->date('publication_date_end')->nullable();
            $table->jsonb('publication_date_settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->dropColumn([
                'publication_date_start',
                'publication_date_end',
                'publication_date_settings',
            ]);
        });
    }
};
