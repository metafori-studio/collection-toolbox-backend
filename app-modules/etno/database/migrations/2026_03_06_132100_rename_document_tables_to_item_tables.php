<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('etno_documents', 'etno_items');
        Schema::rename('etno_document_locality', 'etno_item_locality');
        Schema::rename('etno_document_keyword', 'etno_item_keyword');
        Schema::rename('etno_document_authors', 'etno_item_authors');
        Schema::rename('etno_document_researchers', 'etno_item_researchers');
        Schema::rename('etno_document_originators', 'etno_item_originators');
        Schema::rename('etno_document_research_collection', 'etno_item_research_collection');

        Schema::table('etno_item_locality', function (Blueprint $table) {
            $table->renameColumn('document_id', 'item_id');
        });

        Schema::table('etno_item_keyword', function (Blueprint $table) {
            $table->renameColumn('document_id', 'item_id');
        });

        Schema::table('etno_item_authors', function (Blueprint $table) {
            $table->renameColumn('document_id', 'item_id');
        });

        Schema::table('etno_item_researchers', function (Blueprint $table) {
            $table->renameColumn('document_id', 'item_id');
        });

        Schema::table('etno_item_originators', function (Blueprint $table) {
            $table->renameColumn('document_id', 'item_id');
        });

        Schema::table('etno_item_research_collection', function (Blueprint $table) {
            $table->renameColumn('document_id', 'item_id');
        });
    }

    public function down(): void
    {
        Schema::table('etno_item_research_collection', function (Blueprint $table) {
            $table->renameColumn('item_id', 'document_id');
        });

        Schema::table('etno_item_originators', function (Blueprint $table) {
            $table->renameColumn('item_id', 'document_id');
        });

        Schema::table('etno_item_researchers', function (Blueprint $table) {
            $table->renameColumn('item_id', 'document_id');
        });

        Schema::table('etno_item_authors', function (Blueprint $table) {
            $table->renameColumn('item_id', 'document_id');
        });

        Schema::table('etno_item_keyword', function (Blueprint $table) {
            $table->renameColumn('item_id', 'document_id');
        });

        Schema::table('etno_item_locality', function (Blueprint $table) {
            $table->renameColumn('item_id', 'document_id');
        });

        Schema::rename('etno_item_research_collection', 'etno_document_research_collection');
        Schema::rename('etno_item_originators', 'etno_document_originators');
        Schema::rename('etno_item_researchers', 'etno_document_researchers');
        Schema::rename('etno_item_authors', 'etno_document_authors');
        Schema::rename('etno_item_keyword', 'etno_document_keyword');
        Schema::rename('etno_item_locality', 'etno_document_locality');
        Schema::rename('etno_items', 'etno_documents');
    }
};
