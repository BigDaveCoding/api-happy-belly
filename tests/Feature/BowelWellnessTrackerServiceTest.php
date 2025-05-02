<?php

namespace Tests\Feature;

use App\Http\Requests\BowelWellnessTrackerCreateRequest;
use App\Http\Requests\BowelWellnessTrackerUpdateRequest;
use App\Models\BowelWellnessTracker;
use App\Models\User;
use App\Providers\BowelWellnessTrackerService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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
        $request = new BowelWellnessTrackerCreateRequest;
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

        $request = new BowelWellnessTrackerCreateRequest;
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

        $request = new BowelWellnessTrackerCreateRequest;
        $request->merge($requestData);

        $entry = BowelWellnessTrackerService::createEntry($request);

        $this->assertDatabaseHas('bowel_wellness_trackers', [
            'id' => $entry->id,
            'pain' => 10,
            'hydration_level' => 1,
            'blood' => true,
        ]);
    }

    public function test_medication_is_attached_to_tracker_correctly()
    {
        $this->withoutExceptionHandling();
        $this->artisan('migrate');

        $user = User::factory()->create();
        $tracker = BowelWellnessTracker::factory()->create(['user_id' => $user->id]);

        $request = new BowelWellnessTrackerCreateRequest([
            'medication_name' => ['Paracetamol'],
            'medication_strength' => ['500mg'],
            'medication_form' => ['tablet'],
            'medication_route' => ['oral'],
            'medication_notes' => ['For headache'],
            'medication_prescribed' => [true],
            'medication_taken_at' => ['08:00'],
        ]);

        BowelWellnessTrackerService::medicationPivotData($request, $tracker);

        $this->assertDatabaseHas('medications', [
            'name' => 'Paracetamol',
            'strength' => '500mg',
        ]);

        $this->assertDatabaseHas('bowel_wellness_tracker_medication', [
            'bowel_wellness_tracker_id' => $tracker->id,
            'prescribed' => true,
            'taken_at' => '08:00',
        ]);
    }

    public function test_multiple_medications_can_be_attached()
    {
        $user = User::factory()->create();
        $tracker = BowelWellnessTracker::factory()->create(['user_id' => $user->id]);

        $request = new BowelWellnessTrackerCreateRequest([
            'medication_name' => ['Paracetamol', 'Ibuprofen'],
            'medication_strength' => ['500mg', '200mg'],
            'medication_form' => ['tablet', 'capsule'],
            'medication_route' => ['oral', 'oral'],
            'medication_notes' => ['Headache', 'Pain relief'],
            'medication_prescribed' => [true, false],
            'medication_taken_at' => ['08:00', '12:00'],
        ]);

        BowelWellnessTrackerService::medicationPivotData($request, $tracker);

        $this->assertDatabaseCount('bowel_wellness_tracker_medication', 2);
    }

    public function test_no_medications_attached_if_none_provided()
    {
        $user = User::factory()->create();
        $tracker = BowelWellnessTracker::factory()->create(['user_id' => $user->id]);

        $request = new BowelWellnessTrackerCreateRequest([]); // No meds

        BowelWellnessTrackerService::medicationPivotData($request, $tracker);

        $this->assertDatabaseCount('bowel_wellness_tracker_medication', 0);
    }

    public function test_nullable_medication_fields_are_handled()
    {
        $user = User::factory()->create();
        $tracker = BowelWellnessTracker::factory()->create(['user_id' => $user->id]);

        $request = new BowelWellnessTrackerCreateRequest([
            'medication_name' => ['Paracetamol'],
            'medication_strength' => [null],
            'medication_form' => [null],
            'medication_route' => [null],
            'medication_notes' => [null],
            'medication_prescribed' => [null],
            'medication_taken_at' => [null],
        ]);

        BowelWellnessTrackerService::medicationPivotData($request, $tracker);

        $this->assertDatabaseHas('medications', ['name' => 'Paracetamol']);
    }

    public function test_updates_single_field_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $entry = BowelWellnessTracker::factory()->create(['user_id' => $user->id, 'stool_type' => 3]);

        $data = [
            'stool_type' => 5,
        ];

        $request = new BowelWellnessTrackerUpdateRequest($data);

        BowelWellnessTrackerService::updateBowelWellnessTrackerEntry($request, $entry);
        $entry->save();

        $this->assertDatabaseHas('bowel_wellness_trackers', [
            'id' => $entry->id,
            'stool_type' => 5,
        ]);
    }

    public function test_updates_multiple_fields()
    {
        $user = User::factory()->create();
        $entry = BowelWellnessTracker::factory()->create([
            'user_id' => $user->id,
            'urgency' => 2,
            'pain' => 3,
        ]);

        $data = [
            'urgency' => 8,
            'pain' => 5,
        ];

        $request = new BowelWellnessTrackerUpdateRequest($data);

        BowelWellnessTrackerService::updateBowelWellnessTrackerEntry($request, $entry);
        $entry->save();

        $this->assertDatabaseHas('bowel_wellness_trackers', [
            'id' => $entry->id,
            'urgency' => 8,
            'pain' => 5,
        ]);
    }
}
