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
        Schema::dropIfExists('archeo_activities');
        Schema::create('archeo_activities', function (Blueprint $table) {
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
            $table->decimal('coordinate_x', 15, 6)->nullable();
            $table->decimal('coordinate_y', 15, 6)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->boolean('has_gis_link')->default(false);

            $table->integer('cvs_number')->nullable();
            $table->text('research_leader');
            $table->json('author_ns')->nullable();
            $table->text('institution')->nullable();

            $table->json('dating_ns')->nullable();
            $table->json('dating_ceans')->nullable();
            $table->json('dating_site_type')->nullable();
            $table->text('site_type_original')->nullable();

            $table->text('size_category');
            $table->unsignedBigInteger('import_id')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archeo_activities');
    }
};
