<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etno_document_locality', function (Blueprint $table) {
            $table->id();
            $table->string('document_id');
            $table->foreign('document_id')->references('id')->on('etno_documents')->cascadeOnDelete();
            $table->foreignId('locality_id')->constrained('localities')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etno_document_locality');
    }
};
