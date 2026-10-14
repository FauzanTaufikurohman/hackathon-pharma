<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Shipment> */
class ShipmentFactory extends Factory
{
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
