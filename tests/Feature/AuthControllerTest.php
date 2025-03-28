<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_auth_controller_login_success(): void
    {
        User::factory()->create([
            'email' => 'email@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $loginData = [
            'email' => 'email@gmail.com',
            'password' => 'password',
        ];
        $response = $this->postJson('/api/login', $loginData);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'token');
            });
    }

    public function test_auth_controller_login_fails_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'email@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $loginData = [
            'email' => 'email@gmail.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
            ->assertJson(function (AssertableJson $response) {
                $response->where('message', 'login failed');
            });
    }

    public function test_auth_controller_login_fails_with_nonexistent_email(): void
    {
        $loginData = [
            'email' => 'nonexistent@gmail.com',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $response) {
                $response->has('message')
                    ->where('message', 'login credentials incorrect');
            });
    }

    public function test_auth_controller_login_fails_with_missing_credentials(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $response) {
                $response->has('message')
                    ->where('message', 'login credentials incorrect');
            });
    }

    public function test_auth_controller_register_success(): void
    {
        $data = [
            'register_name' => 'John Doe',
            'register_email' => 'johndoe@gmail.com',
            'register_password' => Hash::make('password'),
        ];
        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message');
            });
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
        ]);
    }
}
