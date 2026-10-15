<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckerQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_is_scoped_and_filters_only_open_inspections(): void
    {
        $checker = User::factory()->create(['role' => 'checker']);
        Shipment::factory()->create(['inspector_id' => $checker->id]);
        Shipment::factory()->create(['inspector_id' => $checker->id, 'status' => 'completed']);
        Shipment::factory()->create();
        Sanctum::actingAs($checker, ['checker']);
        $this->getJson('/api/v1/checker/queue')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/checker/queue?search=DOES-NOT-EXIST')->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/checker/queue?per_page=999')->assertUnprocessable();
        Sanctum::actingAs(User::factory()->create(['role' => 'buyer']), ['vendor']);
        $this->getJson('/api/v1/checker/queue')->assertForbidden();
    }
}
