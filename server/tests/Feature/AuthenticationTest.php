<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_issues_expiring_token_without_leaking_password(): void
    {
        User::factory()->create(['email' => 'vendor@example.test', 'password' => 'secret-test']);
        $response = $this->postJson('/api/v1/auth/login', ['email' => 'vendor@example.test', 'password' => 'secret-test']);
        $response->assertOk()->assertJsonStructure(['token', 'expires_at', 'data' => ['role']])->assertJsonMissingPath('data.password');
        $token = $response->json('token');
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk();
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_password_and_unknown_email_have_same_error(): void
    {
        User::factory()->create(['email' => 'vendor@example.test', 'password' => 'secret-test']);
        foreach (['vendor@example.test', 'unknown@example.test'] as $email) {
            $this->postJson('/api/v1/auth/login', ['email' => $email, 'password' => 'wrong'])
                ->assertUnprocessable()->assertJsonPath('errors.email.0', 'Email atau kata sandi tidak cocok.');
        }
    }

    public function test_login_is_rate_limited_and_credentials_are_validated(): void
    {
        $this->postJson('/api/v1/auth/login', [])->assertUnprocessable();
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'limited@example.test', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/api/v1/auth/login', ['email' => 'limited@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_expired_tokens_cannot_access_vendor_data(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('expired', ['vendor'], now()->subMinute())->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/vendor/packages')->assertUnauthorized();
    }
}
