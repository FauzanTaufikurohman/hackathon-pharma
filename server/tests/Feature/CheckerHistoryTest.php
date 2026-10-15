<?php

namespace Tests\Feature;

use App\Models\Inspection;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckerHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_preserves_versions_and_does_not_attach_old_approval_to_new_exam(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier)->create();
        $original = $shipment->latestInspection;
        Sanctum::actingAs(User::findOrFail($original->user_id), ['checker']);
        $this->getJson('/api/v1/checker/history')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.approval.decision', 'approved');
        Inspection::create(['shipment_id' => $shipment->id, 'user_id' => $original->user_id, 'packaging' => true, 'seal' => true,
            'label' => true, 'passed' => true, 'temperature' => 4, 'note' => 'Pemeriksaan ulang uji.', 'inspected_at' => now()]);
        $this->getJson('/api/v1/checker/history')->assertOk()->assertJsonPath('meta.total', 2)->assertJsonPath('data.0.approval', null);
        Sanctum::actingAs(User::factory()->create(['role' => 'checker']), ['checker']);
        $this->getJson('/api/v1/checker/history')->assertOk()->assertJsonPath('meta.total', 0);
    }
}
