<?php

namespace Tests\Feature;

use App\Models\BowelWellnessTracker;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BowelWellnessTrackerControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_BowelWellnessTracker_user_return_entries_success_with_pagination(): void
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
                    'data'
                ])
                    ->whereAllType([
                        'message' => 'string',
                        'data' => 'array',
                    ])
                ->has('data', function (AssertableJson $data) {
                    $data->hasAll([
                        'entries',
                        'pagination'
                    ])
                    ->whereAllType([
                        'entries' => 'array',
                        'pagination' => 'array',
                    ])
                        ->has('pagination', function (AssertableJson $pagination) {
                            $pagination->hasAll([
                               'current_page',
                               'total_on_page',
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
                            'stool_type',
                            'urgency',
                            'pain',
                            'blood',
                            'blood_amount',
                            'stress_level',
                            'hydration_level',
                            'recent_meal',
                            'color',
                            'additional_notes'
                        ])
                        ->whereAllType([
                            'id' => 'integer',
                            'user_id' => 'integer',
                            'date' => 'string',
                            'time' => 'string',
                            'stool_type' => 'integer',
                            'urgency' => 'integer',
                            'pain' => 'integer',
                            'blood' => 'integer',
                            'blood_amount' => 'integer',
                            'stress_level' => 'integer',
                            'hydration_level' => 'integer',
                            'recent_meal' => 'integer',
                            'color' => 'string',
                            'additional_notes' => 'string',
                        ]);
                    });
                });
            });
    }
}
