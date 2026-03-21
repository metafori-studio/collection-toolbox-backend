<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('archeo.assignments_table_name', 'archeo_activity_assignments');
        $activitiesTable = config('archeo.table_name', 'archeo_activities');

        Schema::create($tableName, function (Blueprint $table) use ($activitiesTable) {
            $table->id();
            $table->foreignId('activity_id')->constrained($activitiesTable)->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['activity_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('archeo.assignments_table_name', 'archeo_activity_assignments'));
    }
};
