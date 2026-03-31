<?php

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->string('suffix');
            $table->string('identifier')
                ->storedAs("document_id || ':' || suffix");
            $table->uniqueIndex('identifier')
                ->where('deleted_at IS NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->dropUniqueIndex(['identifier']);
            $table->dropColumn(['identifier', 'suffix']);
        });
    }
};
