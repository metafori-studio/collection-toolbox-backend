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
            $table->string('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('etno_documents')
                ->cascadeOnDelete();
            $table->string('suffix');
            $table->string('identifier')
                ->storedAs("document_id || ':' || suffix");
            $table->unique(['identifier', 'deleted_at'])
                ->nullsNotDistinct();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_id');
        });
    }
};
