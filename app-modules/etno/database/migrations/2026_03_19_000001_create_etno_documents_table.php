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
            $table->string('id')->primary();
            $table->string('doi')->nullable();
            $table->string('type')->nullable();
            $table->string('extent')->nullable();
            $table->string('extent_unit')->nullable();
            $table->string('language')->nullable();
            $table->string('collection_method')->nullable();
            $table->string('accrual_method')->nullable();
            $table->string('access_rights')->nullable();
            $table->string('license')->nullable();

            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->json('abstract')->nullable();
            $table->json('general_note')->nullable();
            $table->json('terms_of_use')->nullable();
            $table->json('location_note')->nullable();
            $table->json('content_note')->nullable();
            $table->json('technical_note')->nullable();
            $table->json('production_methods')->nullable();

            $table->date('time_period_start')->nullable();
            $table->date('time_period_end')->nullable();
            $table->json('time_period_settings')->nullable();

            $table->date('submission_date_start')->nullable();
            $table->date('submission_date_end')->nullable();
            $table->json('submission_date_settings')->nullable();

            $table->date('publication_date_start')->nullable();
            $table->date('publication_date_end')->nullable();
            $table->json('publication_date_settings')->nullable();

            $table->foreignId('project_id')->nullable()->constrained('etno_projects')->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->nullableMorphs('locality');

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
