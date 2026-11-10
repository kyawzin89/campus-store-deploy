<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    }

    public function test_registration_validates_input(): void
    {
        $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_a_user_can_log_in_with_correct_credentials(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret123']);

        $this->postJson('/api/login', [
            'email' => 'ada@example.com',
            'password' => 'secret123',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_a_wrong_password(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret123']);

        $this->postJson('/api/login', [
            'email' => 'ada@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_an_authenticated_user_can_fetch_their_profile(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_guests_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_api_errors_are_json_even_without_an_accept_header(): void
    {
        $this->get('/api/user')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
