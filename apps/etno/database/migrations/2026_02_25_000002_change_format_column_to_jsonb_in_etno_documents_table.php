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
        Schema::table('etno_documents', function (Blueprint $table) {
            $table->jsonb('formats')->nullable();
            $table->dropColumn('format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_documents', function (Blueprint $table) {
            $table->string('format')->nullable();
            $table->dropColumn('formats');
        });
    }
};
