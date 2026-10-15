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

class CheckerInspectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ownership_required_photo_and_immutable_submission_with_retry(): void
    {
        Storage::fake('local');
        $checker = User::factory()->create(['role' => 'checker']);
        $shipment = Shipment::factory()->create(['inspector_id' => $checker->id]);
        $foreign = Shipment::factory()->create();
        Sanctum::actingAs($checker, ['checker']);
        $this->getJson('/api/v1/checker/shipments/'.$foreign->tracking_number)->assertNotFound();
        $this->getJson('/api/v1/checker/queue')->assertOk()->assertJsonPath('meta.total', 1);
        $url = '/api/v1/checker/shipments/'.$shipment->tracking_number.'/inspection';
        $body = ['version' => 1, 'request_id' => (string) Str::uuid(), 'packaging' => true,
            'seal' => false, 'label' => true, 'temperature' => 4, 'note' => 'Segel rusak; perlu keputusan Admin.'];
        $this->postJson($url, $body)->assertUnprocessable();
        $body['photo'] = UploadedFile::fake()->image('inspection.png');
        $first = $this->post($url, $body, ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.passed', false);
        $this->post($url, $body, ['Accept' => 'application/json'])->assertOk()->assertJsonPath('replayed', true);
        $body['version'] = 2;
        $body['request_id'] = (string) Str::uuid();
        $this->post($url, $body, ['Accept' => 'application/json'])->assertStatus(409);
        $this->assertDatabaseCount('inspections', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles());
        $this->get('/api/v1/checker/inspections/'.$first->json('data.inspection_id').'/photo')->assertOk();
        Sanctum::actingAs(User::factory()->create(['role' => 'checker']), ['checker']);
        $this->getJson('/api/v1/checker/inspections/'.$first->json('data.inspection_id').'/photo')->assertNotFound();
    }

    public function test_non_checker_and_inactive_account_cannot_use_checker_api(): void
    {
        $this->getJson('/api/v1/checker/queue')->assertUnauthorized();
        foreach (['admin', 'buyer', 'courier'] as $role) {
            Sanctum::actingAs(User::factory()->create(['role' => $role]), [$role]);
            $this->getJson('/api/v1/checker/queue')->assertForbidden();
        }
        Sanctum::actingAs(User::factory()->create(['role' => 'checker', 'is_active' => false]), ['checker']);
        $this->getJson('/api/v1/checker/queue')->assertForbidden();
    }
}
