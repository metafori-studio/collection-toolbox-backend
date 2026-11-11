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
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->jsonb('author_ns')->nullable()->change();
            $table->jsonb('dating_ns')->nullable()->change();
            $table->jsonb('dating_ceans')->nullable()->change();
            $table->jsonb('dating_site_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->json('author_ns')->nullable()->change();
            $table->json('dating_ns')->nullable()->change();
            $table->json('dating_ceans')->nullable()->change();
            $table->json('dating_site_type')->nullable()->change();
        });
    }
};
