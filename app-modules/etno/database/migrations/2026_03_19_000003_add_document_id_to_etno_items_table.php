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
            $table->string('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('etno_documents')
                ->cascadeOnDelete();
            $table->string('suffix');
            $table->unique(['document_id', 'suffix', 'deleted_at']);
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
