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
        Schema::create('etno_documents', function (Blueprint $table) {
            $table->id();
            $table->string('doi')->nullable();
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->string('size_type')->nullable();
            $table->string('format')->nullable();
            $table->string('language')->nullable();
            $table->string('collection_method')->nullable();
            $table->string('acquisition_method')->nullable();
            $table->string('access_right')->nullable();
            $table->string('license')->nullable();
            $table->jsonb('title')->nullable();
            $table->jsonb('subtitle')->nullable();
            $table->jsonb('abstract')->nullable();
            $table->jsonb('note')->nullable();
            $table->date('study_period_start')->nullable();
            $table->date('study_period_end')->nullable();
            $table->jsonb('study_period_settings')->nullable();
            $table->date('submission_date_start')->nullable();
            $table->date('submission_date_end')->nullable();
            $table->jsonb('submission_date_settings')->nullable();
            $table->foreignId('research_collection_id')
                ->nullable()
                ->constrained('etno_research_collections')
                ->nullOnDelete();
            $table->foreignId('project_id')
                ->nullable()
                ->constrained('etno_projects')
                ->nullOnDelete();
            $table->foreignId('institution_id')
                ->nullable()
                ->constrained('organizations')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etno_documents');
    }
};
