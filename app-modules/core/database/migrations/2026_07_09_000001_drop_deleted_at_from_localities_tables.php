<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('regions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('districts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('municipalities', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('municipality_parts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('regions', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('districts', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('municipalities', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('municipality_parts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
