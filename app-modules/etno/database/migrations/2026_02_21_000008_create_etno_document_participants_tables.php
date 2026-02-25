<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etno_document_authors', function (Blueprint $table) {
            $table->string('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('etno_documents')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('person_id')
                ->constrained('people')
                ->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['document_id', 'person_id']);
        });

        Schema::create('etno_document_researchers', function (Blueprint $table) {
            $table->id();
            $table->string('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('etno_documents')
                ->cascadeOnDelete();
            $table->foreignId('person_id')
                ->constrained('people')
                ->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['document_id', 'person_id']);
        });

        Schema::create('etno_document_originators', function (Blueprint $table) {
            $table->id();
            $table->string('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('etno_documents')
                ->cascadeOnDelete();
            $table->foreignId('person_id')
                ->nullable()
                ->constrained('people')
                ->cascadeOnDelete();
            $table->text('label')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['document_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etno_document_authors');
        Schema::dropIfExists('etno_document_researchers');
        Schema::dropIfExists('etno_document_originators');
    }
};
