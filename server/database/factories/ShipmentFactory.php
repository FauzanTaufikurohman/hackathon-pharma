<?php

namespace Database\Factories;

use App\Models\Approval;
use App\Models\Assignment;
use App\Models\Inspection;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Shipment> */
class ShipmentFactory extends Factory
{
    public function assignedTo(User $courier, string $status = 'assigned'): static
    {
        return $this->state(['courier_id' => $courier->id, 'status' => $status])->afterCreating(function (Shipment $shipment) use ($courier, $status) {
            $checker = User::factory()->create(['role' => 'checker']);
            $admin = User::factory()->create(['role' => 'admin']);
            $inspection = Inspection::create(['shipment_id' => $shipment->id, 'user_id' => $checker->id,
                'packaging' => true, 'seal' => true, 'label' => true, 'passed' => true,
                'temperature' => 4, 'note' => 'Fixture demo/test; bukan pemeriksaan nyata.', 'inspected_at' => now()]);
            Approval::create(['shipment_id' => $shipment->id, 'inspection_id' => $inspection->id,
                'user_id' => $admin->id, 'decision' => 'approved', 'reason' => 'Persetujuan fixture demo/test.']);
            Assignment::create(['shipment_id' => $shipment->id, 'courier_id' => $courier->id,
                'assigned_by' => $admin->id, 'status' => $status === 'assigned' ? 'pending' : 'accepted',
                'pre_trip' => $status === 'assigned' ? null : ['seal' => true, 'icepack' => true, 'manifest' => true, 'temperature' => 4]]);
        });
    }

    public function definition(): array
    {
        return [
            'tracking_number' => 'PHA-'.fake()->unique()->numerify('########'),
            'buyer_id' => User::factory(),
            'sender_name' => 'Distributor Demo', 'recipient_name' => 'Faskes Demo',
            'recipient_phone' => '+628110000000', 'address' => 'Alamat demo Jakarta',
            'origin_city' => 'Bandung', 'destination_city' => 'Jakarta',
            'item_summary' => 'Vaksin demo', 'handling_instructions' => 'Simpan 2–8°C.',
            'temperature_profile' => 'controlled', 'temperature_min' => 2, 'temperature_max' => 8,
            'scheduled_at' => now()->addHour(), 'status' => 'inspection',
            'last_location' => 'Hub Bandung', 'is_demo' => true,
        ];
    }
}
