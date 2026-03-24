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
        $activitiesTable = config('archeo.table_name', 'archeo_activities');
        $galleriesTable = config('archeo.galleries_table_name', 'archeo_galleries');
        $assignmentsTable = config('archeo.assignments_table_name', 'archeo_activity_assignments');

        Schema::create('activity_imports', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->nullable()->index();
            $table->string('file_name');
            $table->foreignId('user_id')->constrained('users');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create($activitiesTable, function (Blueprint $table) {
            $table->id();
            $table->text('activity_number')->unique();
            $table->smallInteger('activity_year_start');
            $table->smallInteger('activity_year_end');
            $table->text('activity_type');

            $table->text('action_number')->nullable();
            $table->smallInteger('registration_year')->nullable();

            $table->text('cadastral_area')->nullable();
            $table->text('municipality')->nullable();
            $table->text('position')->nullable();
            $table->text('district')->nullable();
            $table->integer('localization_degree')->nullable();
            $table->decimal('coordinate_x', 20, 6)->nullable();
            $table->decimal('coordinate_y', 20, 6)->nullable();
            $table->boolean('has_gis_link')->default(false);

            $table->integer('cvs_number');
            $table->text('research_leader');
            $table->json('author_ns')->nullable();
            $table->text('institution')->nullable();

            $table->json('dating_ns')->nullable();
            $table->json('dating_ceans')->nullable();
            $table->json('dating_site_type')->nullable();
            $table->text('site_type_original')->nullable();

            $table->text('size_category');
            $table->foreignId('import_id')->nullable()->constrained('activity_imports');

            $table->timestamps();
        });

        Schema::create($galleriesTable, function (Blueprint $table) use ($activitiesTable) {
            $table->id();
            $table->foreignId('activity_id')->constrained($activitiesTable)->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create($assignmentsTable, function (Blueprint $table) use ($activitiesTable) {
            $table->id();
            $table->foreignId('activity_id')->constrained($activitiesTable)->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['activity_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('archeo.assignments_table_name', 'archeo_activity_assignments'));
        Schema::dropIfExists(config('archeo.galleries_table_name', 'archeo_galleries'));
        Schema::dropIfExists(config('archeo.table_name', 'archeo_activities'));
        Schema::dropIfExists('activity_imports');
    }
};
