<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourierDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Seeder courier hanya local/testing.');
        }
        $this->call(DatabaseSeeder::class);
        $courier = User::where('email', 'satria@pharma.test')->firstOrFail();
        if (! $courier->courier_profile) {
            $courier->courier_profile = ['courier_code' => 'SATRIA-DEMO', 'capacity_kg' => 30, 'hub_base' => 'Hub demo',
                'shift_name' => 'Shift demo', 'cooler_box_code' => 'BOX-DEMO'];
            $courier->save();
        }
        foreach (['assigned', 'accepted', 'in_transit', 'deviation_hold', 'completed'] as $index => $status) {
            $awb = 'PHA-COUR-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            if (! Shipment::where('tracking_number', $awb)->exists()) {
                Shipment::factory()->assignedTo($courier, $status)->create([
                    'tracking_number' => $awb, 'is_demo' => true, 'completed_at' => $status === 'completed' ? now() : null,
                ]);
            }
        }
    }
}
