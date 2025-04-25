<?php

namespace Tests\Feature;

use App\Http\Requests\BowelWellnessTrackerCreateRequest;
use App\Models\User;
use App\Providers\BowelWellnessTrackerService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BowelWellnessTrackerServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function test_create_entry_saves_valid_data()
    {
        $user = User::factory()->create();

        $requestData = [
            'user_id' => $user->id,
            'date' => '2025-04-25',
            'time' => '14:30',
            'stool_type' => 4,
            'urgency' => 3,
            'pain' => 2,
            'blood' => false,
            'blood_amount' => null,
            'stress_level' => 6,
            'hydration_level' => 7,
            'recent_meal' => true,
            'color' => 'brown',
            'additional_notes' => 'Mild cramp before movement.',
        ];

        // Create a fake FormRequest instance with validated data
        $request = new BowelWellnessTrackerCreateRequest();
        $request->merge($requestData);

        $entry = BowelWellnessTrackerService::createEntry($request);

        $this->assertDatabaseHas('bowel_wellness_trackers', [
            'id' => $entry->id,
            'user_id' => $user->id,
            'stool_type' => 4,
            'color' => 'brown',
        ]);

        $this->assertEquals('2025-04-25', $entry->date);
        $this->assertEquals('14:30', $entry->time);
    }

    public function test_create_entry_with_only_required_fields()
    {
        $user = User::factory()->create();

        $requestData = [
            'user_id' => $user->id,
            'date' => '2025-04-25',
            'time' => '08:00',
            'stool_type' => 3,
        ];

        $request = new BowelWellnessTrackerCreateRequest();
        $request->merge($requestData);

        $entry = BowelWellnessTrackerService::createEntry($request);

        $this->assertDatabaseHas('bowel_wellness_trackers', [
            'id' => $entry->id,
            'user_id' => $user->id,
            'stool_type' => 3,
            'urgency' => null,
        ]);
    }

    public function test_create_entry_with_edge_values()
    {
        $user = User::factory()->create();

        $requestData = [
            'user_id' => $user->id,
            'date' => '2025-04-25',
            'time' => '06:45',
            'stool_type' => 7,
            'urgency' => 10,
            'pain' => 10,
            'blood' => true,
            'blood_amount' => 10000,
            'stress_level' => 1,
            'hydration_level' => 1,
            'recent_meal' => false,
            'color' => 'dark brown',
            'additional_notes' => 'Severe discomfort',
        ];

        $request = new BowelWellnessTrackerCreateRequest();
        $request->merge($requestData);

        $entry = BowelWellnessTrackerService::createEntry($request);

        $this->assertDatabaseHas('bowel_wellness_trackers', [
            'id' => $entry->id,
            'pain' => 10,
            'hydration_level' => 1,
            'blood' => true,
        ]);
    }


}
