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
        Schema::create('etno_document_authors', function (Blueprint $table) {
            $table->string('document_id');
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['document_id', 'person_id'], 'etno_doc_authors_pkey');
            $table->foreign('document_id')->references('id')->on('etno_documents')->cascadeOnDelete();
        });

        Schema::create('etno_document_keyword', function (Blueprint $table) {
            $table->string('document_id');
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['document_id', 'keyword_id'], 'etno_doc_keyword_pkey');
            $table->foreign('document_id')->references('id')->on('etno_documents')->cascadeOnDelete();
        });

        Schema::create('etno_document_originators', function (Blueprint $table) {
            $table->id();
            $table->string('document_id');
            $table->foreignId('person_id')->nullable()->constrained('people')->cascadeOnDelete();
            $table->json('label')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['document_id', 'person_id'], 'etno_doc_orig_unique');
            $table->foreign('document_id')->references('id')->on('etno_documents')->cascadeOnDelete();
        });

        Schema::create('etno_document_research_collection', function (Blueprint $table) {
            $table->string('document_id');
            $table->foreignId('research_collection_id')->constrained('etno_research_collections')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['document_id', 'research_collection_id'], 'etno_doc_rc_primary');
            $table->foreign('document_id')->references('id')->on('etno_documents')->cascadeOnDelete();
        });

        Schema::create('etno_document_researchers', function (Blueprint $table) {
            $table->string('document_id');
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['document_id', 'person_id'], 'etno_doc_res_pkey');
            $table->foreign('document_id')->references('id')->on('etno_documents')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etno_document_researchers');
        Schema::dropIfExists('etno_document_research_collection');
        Schema::dropIfExists('etno_document_originators');
        Schema::dropIfExists('etno_document_keyword');
        Schema::dropIfExists('etno_document_authors');
    }
};
