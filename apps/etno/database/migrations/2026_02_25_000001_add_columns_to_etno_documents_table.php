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
            $table->jsonb('notations')->nullable();
            $table->jsonb('access_right_note')->nullable();
            $table->jsonb('locality_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_documents', function (Blueprint $table) {
            $table->dropColumn([
                'access_right_note',
                'locality_note',
            ]);
        });
    }
};
