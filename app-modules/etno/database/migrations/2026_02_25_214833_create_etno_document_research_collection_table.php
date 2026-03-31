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
        Schema::create('etno_document_research_collection', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('etno_documents')->cascadeOnDelete();
            $table->foreignId('research_collection_id')
                ->constrained('etno_research_collections')
                ->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['document_id', 'research_collection_id']);
        });

        Schema::table('etno_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('research_collection_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_documents', function (Blueprint $table) {
            $table->foreignId('research_collection_id')
                ->nullable()
                ->constrained('etno_research_collections')
                ->nullOnDelete();
        });

        Schema::dropIfExists('etno_document_research_collection');
    }
};
