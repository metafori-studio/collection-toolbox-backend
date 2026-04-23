<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etno_document_locality', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('etno_documents')->cascadeOnDelete();
            $table->foreignId('locality_id')->constrained('localities')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['document_id', 'locality_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etno_document_locality');
    }
};
