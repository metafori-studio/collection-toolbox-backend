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
        Schema::dropIfExists('etno_item_localities');

        Schema::table('etno_items', function (Blueprint $table) {
            $table->nullableMorphs('locality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etno_items', function (Blueprint $table) {
            $table->dropMorphs('locality');
        });

        Schema::create('etno_item_localities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('etno_items')->cascadeOnDelete();
            $table->morphs('locality');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['item_id', 'locality_id', 'locality_type']);
        });
    }
};
