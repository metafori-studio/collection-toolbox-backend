<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL requires a USING clause to convert text to json
        // We also wrap existing text in a JSON array to prevent "invalid JSON" errors
        DB::statement('ALTER TABLE archeo_activities ALTER COLUMN author_ns TYPE JSON USING json_build_array(author_ns)');

        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->json('author_ns')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE archeo_activities ALTER COLUMN author_ns TYPE TEXT USING author_ns->>0');

        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->text('author_ns')->nullable()->change();
        });
    }
};
