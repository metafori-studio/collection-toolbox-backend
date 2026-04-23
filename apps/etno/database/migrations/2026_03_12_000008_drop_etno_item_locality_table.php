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
        Schema::dropIfExists('etno_item_locality');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('etno_item_locality', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained('etno_items')->cascadeOnDelete();
            $table->foreignId('locality_id')->constrained('localities')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['item_id', 'locality_id']);
        });
    }
};
