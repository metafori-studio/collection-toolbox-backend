<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Metafori\Core\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_imports', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->nullable();
            $table->string('file_name');
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('status')->default('processing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_imports');
    }
};
