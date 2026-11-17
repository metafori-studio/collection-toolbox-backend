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
        Schema::create('collection_categories', function (Blueprint $table) {
            $table->id();
            $table->jsonb('title')->nullable();
            $table->timestamps();
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->jsonb('description')->nullable();
            $table->jsonb('summary')->nullable();
            $table->jsonb('author')->nullable();
            $table->foreignId('collection_category_id')->nullable()->constrained('collection_categories')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_category_id');
            $table->dropColumn(['description', 'summary', 'author', 'published_at', 'is_featured']);
        });

        Schema::dropIfExists('collection_categories');
    }
};
