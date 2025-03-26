<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RecipeApiControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_recipe_api_controller_all_recipes_returned_successfully(): void
    {
        Recipe::factory()->count(5)->create();
        $response = $this->get('/api/recipes');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->whereAllType([
                        'message' => 'string',
                        'data' => 'array',
                    ])
                    ->has('data', 5, function (AssertableJson $data) {
                        $data->hasAll('id', 'name', 'image', 'cooking_time', 'serves')
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'image' => 'string',
                                'cooking_time' => 'integer',
                                'serves' => 'integer',
                            ]);
                    });
            });
    }

    public function test_recipe_api_controller_all_recipes_empty_response(): void
    {
        $response = $this->get('/api/recipes');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->whereAllType([
                        'message' => 'string',
                        'data' => 'array',
                    ])
                    ->where('data', []);
            });
    }
}
