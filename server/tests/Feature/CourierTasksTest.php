<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_search_counts_pagination_and_ownership(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);
        Shipment::factory()->assignedTo($courier)->count(3)->create(['item_summary' => 'Vaksin 50%']);
        Shipment::factory()->assignedTo($courier, 'in_transit')->create();
        Shipment::factory()->create();
        Sanctum::actingAs($courier, ['courier']);
        $this->getJson('/api/v1/courier/tasks?status=assigned&per_page=2')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)->assertJsonPath('counts.all', 4);
        $this->getJson('/api/v1/courier/tasks?search=%25')->assertOk()->assertJsonCount(3, 'data');
        $this->getJson('/api/v1/courier/tasks?search=missing')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/courier/tasks?status=bad')->assertUnprocessable();
        $this->getJson('/api/v1/courier/tasks?per_page=101')->assertUnprocessable();
    }
}
