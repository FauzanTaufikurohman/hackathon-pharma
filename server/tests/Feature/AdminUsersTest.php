<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_directory_is_admin_only_and_hides_credentials(): void
    {
        $this->getJson('/api/v1/admin/users')->assertUnauthorized();
        Sanctum::actingAs(User::factory()->create(['role' => 'buyer']), ['vendor']);
        $this->getJson('/api/v1/admin/users')->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']), ['admin']);
        $response = $this->getJson('/api/v1/admin/users')->assertOk();
        $this->assertArrayNotHasKey('password', $response->json('data.0'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data.0'));
    }

    public function test_admin_creates_hashed_account_and_validates_email_and_capacity(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']), ['admin']);
        $payload = ['name' => 'Kurir Baru', 'email' => 'kurir.new@example.test', 'role' => 'courier', 'password' => 'LocalTestOnly!2026', 'capacity_kg' => 30];
        $this->postJson('/api/v1/admin/users', collect($payload)->except('capacity_kg')->all())->assertUnprocessable();
        $this->postJson('/api/v1/admin/users', $payload)->assertCreated()->assertJsonMissing(['password' => $payload['password']]);
        $user = User::where('email', $payload['email'])->firstOrFail();
        $this->assertTrue(Hash::check($payload['password'], $user->password));
        $this->assertSame(30.0, (float) $user->courier_profile['capacity_kg']);
        $this->postJson('/api/v1/admin/users', $payload)->assertUnprocessable();
    }

    public function test_access_change_revokes_tokens_and_rejects_stale_or_self_change(): void
    {
        $actor = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'checker']);
        $target->createToken('old', ['checker']);
        Sanctum::actingAs($actor, ['admin']);
        $payload = ['role' => 'checker', 'is_active' => false, 'expected_role' => 'checker', 'expected_active' => true];
        $this->putJson('/api/v1/admin/users/'.$target->id, $payload)->assertOk()->assertJsonPath('data.is_active', false);
        $this->assertSame(0, $target->tokens()->count());
        $this->putJson('/api/v1/admin/users/'.$target->id, $payload)->assertConflict();
        $this->putJson('/api/v1/admin/users/'.$actor->id, ['role' => 'checker', 'is_active' => true, 'expected_role' => 'admin', 'expected_active' => true])->assertUnprocessable();
    }

    public function test_assigned_account_cannot_be_disabled_or_demoted(): void
    {
        $actor = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'checker']);
        Shipment::factory()->create(['inspector_id' => $target->id, 'status' => 'inspection']);
        Sanctum::actingAs($actor, ['admin']);
        $this->putJson('/api/v1/admin/users/'.$target->id, ['role' => 'buyer', 'is_active' => false,
            'expected_role' => 'checker', 'expected_active' => true])->assertConflict();
        $this->assertTrue($target->fresh()->is_active);
    }
}
