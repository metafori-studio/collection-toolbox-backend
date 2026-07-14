<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE etno_items ALTER COLUMN language TYPE jsonb USING CASE WHEN language IS NULL THEN NULL ELSE json_build_array(language)::jsonb END');
        DB::statement('ALTER TABLE etno_items RENAME COLUMN language TO languages');

        DB::statement('ALTER TABLE etno_documents ALTER COLUMN language TYPE jsonb USING CASE WHEN language IS NULL THEN NULL ELSE json_build_array(language)::jsonb END');
        DB::statement('ALTER TABLE etno_documents RENAME COLUMN language TO languages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE etno_items RENAME COLUMN languages TO language');
        DB::statement('ALTER TABLE etno_items ALTER COLUMN language TYPE varchar USING language->>0');

        DB::statement('ALTER TABLE etno_documents RENAME COLUMN languages TO language');
        DB::statement('ALTER TABLE etno_documents ALTER COLUMN language TYPE varchar USING language->>0');
    }
};
