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
            $table->foreignId('import_id')->nullable()->constrained('activity_imports');
            $table->dropColumn('file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->string('file_name')->nullable()->index();
            $table->dropConstrainedForeignId('import_id');
        });
    }
};
