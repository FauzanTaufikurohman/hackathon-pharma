<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorPackagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_is_real_pdf_scoped_to_vendor(): void
    {
        $vendor = User::factory()->create();
        Shipment::factory()->create(['buyer_id' => $vendor->id]);
        Shipment::factory()->create();
        Sanctum::actingAs($vendor);
        $response = $this->get('/api/v1/vendor/packages/export', ['Accept' => 'application/pdf']);
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertSame(hash('sha256', $response->getContent()), $response->headers->get('X-Report-SHA256'));
    }

    public function test_vendor_list_is_scoped_and_paginates_with_global_counts(): void
    {
        $vendor = User::factory()->create();
        Shipment::factory()->count(3)->create(['buyer_id' => $vendor->id, 'status' => 'in_transit']);
        Shipment::factory()->create(['buyer_id' => $vendor->id, 'status' => 'inspection']);
        Shipment::factory()->count(2)->create();
        Sanctum::actingAs($vendor);
        $this->getJson('/api/v1/vendor/packages?category=transit&per_page=2')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.total', 3)
            ->assertJsonPath('counts.all', 4)->assertJsonPath('counts.qc', 1);
        $this->getJson('/api/v1/vendor/packages?category=transit&per_page=2&page=2')
            ->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_guest_and_non_vendor_are_rejected(): void
    {
        $this->getJson('/api/v1/vendor/packages')->assertUnauthorized();
        foreach (['admin', 'checker', 'courier'] as $role) {
            Sanctum::actingAs(User::factory()->create(['role' => $role]));
            $this->getJson('/api/v1/vendor/packages')->assertForbidden();
        }
    }

    public function test_filters_validate_and_wildcards_are_literal(): void
    {
        $vendor = User::factory()->create();
        Shipment::factory()->create(['buyer_id' => $vendor->id, 'item_summary' => 'Vaksin 50%']);
        Shipment::factory()->create(['buyer_id' => $vendor->id, 'item_summary' => 'Insulin']);
        Sanctum::actingAs($vendor);
        $this->getJson('/api/v1/vendor/packages?search=%25')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/vendor/packages?search=INSULIN')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/vendor/packages?search=unknown')->assertOk()->assertJsonCount(0, 'data');
        foreach (['per_page=101', 'page=0', 'category=bogus', 'status=bogus', 'sort=bogus'] as $query) {
            $this->getJson('/api/v1/vendor/packages?'.$query)->assertUnprocessable();
        }
    }

    public function test_missing_telemetry_is_not_reported_safe(): void
    {
        $vendor = User::factory()->create();
        Shipment::factory()->create(['buyer_id' => $vendor->id]);
        Sanctum::actingAs($vendor);
        $this->getJson('/api/v1/vendor/packages')->assertOk()
            ->assertJsonPath('data.0.current_temperature', null)
            ->assertJsonPath('data.0.temp_label', 'Belum ada data sensor');
    }

    public function test_seed_is_idempotent_and_other_vendor_is_not_exposed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('shipments', 6);
        Sanctum::actingAs(User::where('email', 'vendor@pharma.test')->firstOrFail());
        $this->getJson('/api/v1/vendor/packages')->assertOk()->assertJsonCount(5, 'data');
    }
}
