<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
        ALTER TABLE etno_item_originators
            ALTER COLUMN label
            TYPE JSONB
            USING jsonb_build_object('sk', to_jsonb(label))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
        ALTER TABLE etno_item_originators
            ALTER COLUMN label
            TYPE TEXT
            USING (label->>'sk')
        ");
    }
};
