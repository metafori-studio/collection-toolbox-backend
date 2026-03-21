<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('archeo.galleries_table_name', 'archeo_galleries');
        $activitiesTable = config('archeo.table_name', 'archeo_activities');

        Schema::create($tableName, function (Blueprint $table) use ($activitiesTable) {
            $table->id();
            $table->foreignId('activity_id')->constrained($activitiesTable)->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('archeo.galleries_table_name', 'archeo_galleries'));
    }
};
