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
            $table->renameColumn('access_right_note', 'terms_of_use');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->renameColumn('terms_of_use', 'access_right_note');
        });
    }
};
