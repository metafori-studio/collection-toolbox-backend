<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Archeo\Models\Activity;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an activity can be created and stored in the database.
     */
    public function test_activity_can_be_created(): void
    {
        $Activity = new Activity;
        $Activity->setTable('archeo_activities');

        $data = [
            'activity_number' => 'č.a. 9999',
            'activity_year_start' => 2024,
            'activity_year_end' => 2024,
            'activity_type' => 'nálezová správa',
            'action_number' => '123/2024',
            'cvs_number' => 12345,
            'research_leader' => 'Dr. Jakub Okneb',
            'author_ns' => 'Jano Doe',
            'institution' => 'Jano Doe s.r.o.',
            'size_category' => 'malá',
            'registration_year' => 2024,
            'cadastral_area' => 'Nové Zámky',
            'municipality' => 'Nové Zámky',
            'position' => 'Pri mori',
            'district' => 'Nové Zámky',
            'localization_degree' => 1,
            'coordinate_x' => 47.9862,
            'coordinate_y' => 18.1637,
            'has_gis_link' => true,
        ];

        $Activity->forceFill($data);
        $Activity->save();

        $this->assertDatabaseHas('archeo_activities', [
            'id' => $Activity->id,
            'activity_number' => 'č.a. 9999',
            'cvs_number' => 12345,
            'registration_year' => 2024,
            'cadastral_area' => 'Nové Zámky',
        ]);

        $this->assertEquals('č.a. 9999', $Activity->activity_number);
        $this->assertEquals(12345, $Activity->cvs_number);
        $this->assertEquals(2024, $Activity->registration_year);
    }
}
