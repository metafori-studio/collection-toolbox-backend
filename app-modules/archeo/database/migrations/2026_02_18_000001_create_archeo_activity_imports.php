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
        Schema::dropIfExists('archeo_activity_imports');
        Schema::create('archeo_activity_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('path');
            $table->string('disk');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing', 'failed', 'complete'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archeo_activity_imports');
    }
};
