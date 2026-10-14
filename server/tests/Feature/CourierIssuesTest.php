<?php

namespace Tests\Feature;

use App\Models\Decision;
use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierIssuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_locks_cargo_retries_once_and_waits_for_real_admin_decision(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'in_transit')->create();
        Sanctum::actingAs($courier, ['courier']);
        $base = '/api/v1/courier/tasks/'.$shipment->tracking_number;
        $payload = ['version' => 1, 'request_id' => (string) Str::uuid(), 'category' => 'seal', 'description' => 'Segel box ditemukan robek.'];
        $this->postJson($base.'/issues', $payload)->assertOk();
        $this->postJson($base.'/issues', $payload)->assertOk()->assertJsonPath('replayed', true);
        $this->assertDatabaseCount('issues', 1);
        $this->postJson($base.'/arrive', ['version' => 2, 'request_id' => (string) Str::uuid()])->assertStatus(409);
        $return = ['version' => 2, 'request_id' => (string) Str::uuid()];
        $this->postJson($base.'/confirm-return', $return)->assertStatus(409);
        $issue = ShipmentIssue::firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);
        $issue->update(['status' => 'resolved']);
        Decision::create(['issue_id' => $issue->id, 'user_id' => $admin->id, 'action' => 'return', 'reason' => 'Retur karena segel rusak.']);
        $this->postJson($base.'/confirm-return', $return)->assertOk()->assertJsonPath('data.workflow_status', 'returning');
    }

    public function test_photo_is_encrypted_private_and_ownership_is_checked(): void
    {
        Storage::fake('local');
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'in_transit')->create();
        Sanctum::actingAs($courier, ['courier']);
        $response = $this->post('/api/v1/courier/tasks/'.$shipment->tracking_number.'/issues', [
            'version' => 1, 'request_id' => (string) Str::uuid(), 'category' => 'packaging',
            'description' => 'Kemasan luar mengalami kebocoran.', 'photo' => UploadedFile::fake()->image('proof.png'),
        ], ['Accept' => 'application/json'])->assertOk();
        $issue = ShipmentIssue::findOrFail($response->json('data.issue_id'));
        $stored = Storage::disk('local')->get($issue->photo_path);
        $this->assertFalse(str_starts_with($stored, hex2bin('89504e47')));
        $this->get('/api/v1/courier/issues/'.$issue->id.'/photo')->assertOk()->assertHeader('Content-Type', 'image/png');
        Sanctum::actingAs(User::factory()->create(['role' => 'courier']), ['courier']);
        $this->getJson('/api/v1/courier/issues/'.$issue->id.'/photo')->assertNotFound();
    }
}
