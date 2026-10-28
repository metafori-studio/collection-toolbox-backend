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
        Schema::create('artworks', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->nullable();
            $table->string('slug')->unique();
            $table->jsonb('title')->nullable();
            $table->jsonb('description')->nullable();
            $table->date('creation_date_start')->nullable();
            $table->date('creation_date_end')->nullable();
            $table->jsonb('creation_date_settings')->nullable();
            $table->jsonb('dating')->nullable();
            $table->jsonb('work_types')->nullable();
            $table->jsonb('object_types')->nullable();
            $table->jsonb('mediums')->nullable();
            $table->jsonb('techniques')->nullable();
            $table->string('acquisition_method')->nullable();
            $table->date('acquisition_date_start')->nullable();
            $table->date('acquisition_date_end')->nullable();
            $table->jsonb('acquisition_date_settings')->nullable();
            $table->jsonb('measurements')->nullable();
            $table->timestamps();
        });

        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->jsonb('title')->nullable();
            $table->timestamps();
        });

        Schema::create('artwork_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained('artworks')->cascadeOnDelete();
            $table->foreignId('person_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('artwork_collection', function (Blueprint $table) {
            $table->foreignId('artwork_id')->constrained('artworks')->cascadeOnDelete();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['artwork_id', 'collection_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artwork_collection');
        Schema::dropIfExists('artwork_authors');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('artworks');
    }
};
