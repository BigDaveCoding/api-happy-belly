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
            'register_password' => 'Password1!',
            'register_password_confirmation' => 'Password1!',
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

    public function test_auth_controller_register_fails_with_missing_fields(): void
    {
        $data = [
            'register_name' => 'John Doe',
            'register_email' => 'johndoe@gmail.com',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertInvalid('register_password', 'register_password_confirmation');
    }

    public function test_auth_controller_register_fails_with_invalid_email(): void
    {
        $data = [
            'register_name' => 'John Doe',
            'register_email' => 'invalid-email',
            'register_password' => 'Password1!',
            'register_password_confirmation' => 'Password1!',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertInvalid('register_email');
    }

    public function test_auth_controller_register_fails_with_weak_password(): void
    {
        $data = [
            'register_name' => 'John Doe',
            'register_email' => 'johndoe@gmail.com',
            'register_password' => 'password',
            'register_password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertInvalid('register_password');
    }

    public function test_auth_controller_register_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'name' => 'Existing User',
            'email' => 'johndoe@gmail.com',
            'password' => Hash::make('Password1!'),
        ]);

        $data = [
            'register_name' => 'John Doe',
            'register_email' => 'johndoe@gmail.com',
            'register_password' => 'Password1!',
            'register_password_confirmation' => 'Password1!',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertInvalid('register_email');
    }

    public function test_auth_controller_register_fails_with_password_mismatch(): void
    {
        $data = [
            'register_name' => 'John Doe',
            'register_email' => 'johndoe@gmail.com',
            'register_password' => 'Password1!',
            'register_password_confirmation' => 'DifferentPassword!',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertInvalid('register_password');
    }

    public function test_auth_controller_register_fails_with_short_name(): void
    {
        $data = [
            'register_name' => 'J', // Too short
            'register_email' => 'johndoe@gmail.com',
            'register_password' => 'Password1!',
            'register_password_confirmation' => 'Password1!',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertInvalid('register_name');
    }

    public function test_auth_controller_logout_success(): void
    {
        $user = User::factory()->create([
            'email' => 'email@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->where('message', 'Logout successful');
            });

        $this->assertCount(0, $user->tokens);
    }

    public function test_auth_controller_logout_without_token(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson(function (AssertableJson $json) {
                $json->where('message', 'Unauthenticated.');
            });
    }

}
