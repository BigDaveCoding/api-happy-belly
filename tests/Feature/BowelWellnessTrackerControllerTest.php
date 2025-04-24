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

    public function test_BowelWellnessTracker_user_cannot_access_another_users_entries(): void
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

    public function test_BowelWellnessTracker_user_can_define_custom_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        BowelWellnessTracker::factory()->count(20)->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/bowel-wellness-tracker/{$user->id}?pagination=10");

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data.entries');
    }

    public function test_BowelWellnessTracker_user_returns_validation_error_on_invalid_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson("/api/bowel-wellness-tracker/{$user->id}?pagination=100"); // invalid max = 50

        $response->assertInvalid('pagination');
    }

}
