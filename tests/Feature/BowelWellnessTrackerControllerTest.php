<?php

namespace Tests\Feature;

use App\Models\BowelWellnessTracker;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BowelWellnessTrackerControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_bowel_wellness_tracker_user_return_entries_success_with_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        BowelWellnessTracker::factory()->count(10)->create(['user_id' => $user->id]);
        $this->assertDatabaseCount('bowel_wellness_trackers', 10);

        $response = $this->getJson("/api/bowel-wellness-tracker/{$user->id}");
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll([
                    'message',
                    'data',
                ])
                    ->whereAllType([
                        'message' => 'string',
                        'data' => 'array',
                    ])
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll([
                            'entries',
                            'pagination',
                        ])
                            ->whereAllType([
                                'entries' => 'array',
                                'pagination' => 'array',
                            ])
                            ->has('pagination', function (AssertableJson $pagination) {
                                $pagination->hasAll([
                                    'current_page',
                                    'results_per_page',
                                    'total_results',
                                    'next_page_url',
                                    'previous_page_url',
                                    'all_page_urls',
                                ]);
                            })
                            ->has('entries', 5, function (AssertableJson $entries) {
                                $entries->hasAll([
                                    'id',
                                    'user_id',
                                    'date',
                                    'time',
                                ])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'user_id' => 'integer',
                                        'date' => 'string',
                                        'time' => 'string',
                                    ]);
                            });
                    });
            });
    }

    public function test_bowel_wellness_tracker_user_cannot_access_another_users_entries(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson("/api/bowel-wellness-tracker/{$otherUser->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized - can only access your own entries',
            ]);
    }

    public function test_bowel_wellness_tracker_user_can_define_custom_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        BowelWellnessTracker::factory()->count(20)->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/bowel-wellness-tracker/{$user->id}?pagination=10");

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data.entries');
    }

    public function test_bowel_wellness_tracker_user_returns_validation_error_on_invalid_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson("/api/bowel-wellness-tracker/{$user->id}?pagination=100"); // invalid max = 50

        $response->assertInvalid('pagination');
    }

    public function test_bowel_wellness_tracker_user_returns_empty_entries_when_none_exist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson("/api/bowel-wellness-tracker/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll([
                    'message',
                    'data',
                ])
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll([
                            'entries',
                            'pagination',
                        ])
                            ->where('entries', []);
                    });
            });
    }

    public function test_bowel_wellness_tracker_single_entry_correct_data_response(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        BowelWellnessTracker::factory()->create(['id' => 1, 'user_id' => $user->id]);

        $response = $this->getJson('/api/bowel-wellness-tracker/entry/1');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll([
                    'message',
                    'data',
                ])
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll([
                            'id',
                            'user_id',
                            'date',
                            'time',
                            'stool_type',
                            'urgency',
                            'pain',
                            'blood',
                            'blood_amount',
                            'stress_level',
                            'hydration_level',
                            'recent_meal',
                            'color',
                            'additional_notes',
                        ]);
                    });
            });
    }

    public function test_bowel_wellness_tracker_single_entry_doesnt_exist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->getJson('/api/bowel-wellness-tracker/entry/1');
        $response->assertStatus(404);
    }

    public function test_bowel_wellness_tracker_cannot_see_other_users_entries(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);
        BowelWellnessTracker::factory()->create(['id' => 1, 'user_id' => $otherUser->id]);
        $response = $this->getJson("/api/bowel-wellness-tracker/{$otherUser->id}");
        $response->assertStatus(401);
    }

    public function test_create_entry_with_medication_successfully()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'time' => '12:30',
            'stool_type' => 4,
            'urgency' => 3,
            'pain' => 2,
            'blood' => false,
            'hydration_level' => 7,
            'color' => 'medium brown',
            'additional_notes' => 'Mild cramps',

            'medication_name' => ['Paracetamol'],
            'medication_strength' => ['500mg'],
            'medication_form' => ['tablet'],
            'medication_route' => ['oral'],
            'medication_notes' => ['headache'],
            'medication_prescribed' => [true],
            'medication_taken_at' => ['08:00'],
        ];

        $response = $this->postJson('/api/bowel-wellness-tracker/create', $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Bowel Wellness Tracker entry created successfully',
            ]);

        $this->assertDatabaseHas('bowel_wellness_trackers', [
            'user_id' => $user->id,
            'stool_type' => 4,
        ]);

        $this->assertDatabaseHas('medications', [
            'name' => 'Paracetamol',
            'strength' => '500mg',
        ]);

        $this->assertDatabaseHas('bowel_wellness_tracker_medication', [
            'bowel_wellness_tracker_id' => 1,
            'medication_id' => 1,
            'prescribed' => true,
            'taken_at' => "08:00",
        ]);
    }

}
