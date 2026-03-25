<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Metafori\Archeo\Models\ActivityImport;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->foreignIdFor(ActivityImport::class, 'import_id')
                ->nullable()
                ->after('activity_number')
                ->constrained('activity_imports')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archeo_activities', function (Blueprint $table) {
            $table->dropForeign(['import_id']);
            $table->dropColumn('import_id');
        });
    }
};
