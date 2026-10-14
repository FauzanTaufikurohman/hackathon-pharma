<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_scoped_and_does_not_fake_sensor_data(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);
        Shipment::factory()->assignedTo($courier)->create();
        Shipment::factory()->assignedTo($courier, 'in_transit')->create();
        Shipment::factory()->assignedTo($courier, 'completed')->create(['completed_at' => now()]);
        Shipment::factory()->create(['status' => 'in_transit']);
        Sanctum::actingAs($courier, ['courier']);
        $this->getJson('/api/v1/courier/dashboard')->assertOk()->assertJsonPath('data.metrics.new_tasks_count', 1)
            ->assertJsonPath('data.metrics.on_route_count', 1)->assertJsonPath('data.metrics.completed_today_count', 1)
            ->assertJsonPath('data.profile.current_cooler_temp', null)->assertJsonPath('data.counts.all', 3);
    }

    public function test_guest_wrong_role_inactive_and_wrong_ability_are_rejected(): void
    {
        $this->getJson('/api/v1/courier/dashboard')->assertUnauthorized();
        foreach (['buyer', 'checker', 'admin'] as $role) {
            Sanctum::actingAs(User::factory()->create(['role' => $role]), ['courier']);
            $this->getJson('/api/v1/courier/dashboard')->assertForbidden();
        }
        Sanctum::actingAs(User::factory()->create(['role' => 'courier', 'is_active' => false]), ['courier']);
        $this->getJson('/api/v1/courier/dashboard')->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['role' => 'courier']), ['vendor']);
        $this->getJson('/api/v1/courier/dashboard')->assertForbidden();
    }

    public function test_empty_dashboard_and_courier_login_ability(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'email' => 'courier@example.test', 'password' => 'secret-test']);
        $login = $this->postJson('/api/v1/auth/login', ['email' => $courier->email, 'password' => 'secret-test'])->assertOk();
        auth()->forgetGuards();
        $this->withToken($login->json('token'))->getJson('/api/v1/courier/dashboard')->assertOk()->assertJsonPath('data.priority_task', null);
        $this->withToken($login->json('token'))->getJson('/api/v1/vendor/packages')->assertForbidden();
    }
}
