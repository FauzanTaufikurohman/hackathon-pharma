<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierWorkflowSafetyTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['version' => 1, 'request_id' => (string) Str::uuid(),
            'pre_trip' => ['seal' => true, 'icepack' => true, 'manifest' => true, 'temperature' => 4]];
    }

    public function test_non_demo_requires_real_inspection_photo_and_safe_checklist(): void
    {
        Storage::fake('local');
        $courier = User::factory()->create(['role' => 'courier', 'courier_profile' => ['capacity_kg' => 10]]);
        $shipment = Shipment::factory()->assignedTo($courier)->create(['is_demo' => false]);
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number.'/accept';
        $this->postJson($url, $this->payload())->assertStatus(409);
        $file = UploadedFile::fake()->image('inspection.png');
        $path = $file->store('inspections', 'local');
        $inspection = $shipment->latestInspection;
        $inspection->update(['photo_path' => $path, 'seal' => false]);
        $this->postJson($url, $this->payload())->assertStatus(409);
        $inspection->update(['seal' => true, 'temperature' => 12]);
        $this->postJson($url, $this->payload())->assertStatus(409);
        $inspection->update(['temperature' => 4]);
        $this->postJson($url, $this->payload())->assertOk();
    }

    public function test_assignment_owner_must_match_even_when_shipment_owner_matches(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'courier_profile' => ['capacity_kg' => 10]]);
        $shipment = Shipment::factory()->assignedTo($courier)->create();
        $shipment->latestAssignment->update(['courier_id' => User::factory()->create(['role' => 'courier'])->id]);
        Sanctum::actingAs($courier, ['courier']);
        $this->postJson('/api/v1/courier/tasks/'.$shipment->tracking_number.'/accept', $this->payload())->assertStatus(409);
        $this->assertDatabaseCount('courier_action_receipts', 0);
    }
}
