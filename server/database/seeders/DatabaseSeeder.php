<?php

namespace Database\Seeders;

use App\Models\Inspection;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ShipmentReport;
use App\Models\TelemetrySample;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Seeder demo hanya untuk local/testing.');
        }
        DB::transaction(function () {
            $admin = User::firstOrCreate(['email' => 'admin@pharma.test'], ['name' => 'Admin Demo', 'role' => 'admin', 'password' => 'PharmaAdmin!2026']);
            $checker = User::firstOrCreate(['email' => 'checker@pharma.test'], ['name' => 'Checker Demo', 'role' => 'checker', 'password' => 'PharmaChecker!2026']);
            $courier = User::firstOrCreate(['email' => 'satria@pharma.test'], ['name' => 'Satria Demo', 'role' => 'courier', 'password' => 'PharmaKurir!2026']);
            $vendor = User::firstOrCreate(['email' => 'vendor@pharma.test'], [
                'name' => 'Sarah Nadia', 'role' => 'buyer', 'password' => 'PharmaVendor!2026',
                'facility' => [
                    'facility_id' => 'RSMS-FARM-09', 'name' => 'RS Medika Sehat Jakarta',
                    'department' => 'Instalasi Farmasi Pusat', 'verified_status' => 'DEMO FACILITY',
                    'pharmacist_in_charge' => 'Apt. Sarah Nadia, S.Farm',
                    'pharmacist_license' => null, 'cdob_certification' => null,
                ],
            ]);
            $other = User::firstOrCreate(['email' => 'vendor.lain@pharma.test'], [
                'name' => 'Vendor lain', 'role' => 'buyer', 'password' => 'PharmaVendor!2026',
            ]);
            $rows = [
                ['PHA-260104', 'pending_approval', 'Vaksin Meningococcal', 4.2],
                ['#100098421088712', 'in_transit', 'Insulin Reguler & Biologis (50 Vial)', 4.5],
                ['#100098421099415', 'inspection', 'Vaksin Polio Oral & Pediatrik (120 Botol)', 4.2],
                ['#100098421077321', 'in_transit', 'Serum Anti-Bisa Ular & Plasma Dingin (20 Pack)', 3.9],
                ['#100098421066109', 'completed', 'Vaksin Influenza Flubio Monovalen (80 Vial)', 4.1],
                ['PHA-OTHER-001', 'in_transit', 'Kiriman vendor lain', 4.0],
            ];
            foreach ($rows as $index => [$awb, $status, $item, $temp]) {
                $shipment = Shipment::firstOrCreate(['tracking_number' => $awb], [
                    'buyer_id' => $index === 5 ? $other->id : $vendor->id,
                    'created_by' => $admin->id, 'inspector_id' => $checker->id,
                    'courier_id' => in_array($status, ['in_transit', 'completed']) ? $courier->id : null,
                    'sender_name' => 'Distributor Pharma Demo', 'recipient_name' => 'RS Medika Sehat',
                    'recipient_phone' => '+628110000000', 'address' => 'Jl. Rasuna Said, Jakarta Selatan (demo)',
                    'origin_city' => 'Bandung', 'destination_city' => 'Jakarta Selatan',
                    'item_summary' => $item, 'package_count' => 2, 'weight_kg' => 4.8,
                    'package_type' => 'Insulated shipper', 'batch_number' => 'BATCH-DEMO-'.$index,
                    'handling_instructions' => 'Simpan 2–8°C. Jangan dibalik.',
                    'temperature_profile' => 'controlled', 'temperature_min' => 2, 'temperature_max' => 8,
                    'scheduled_at' => now()->addMinutes(30 + $index * 10), 'status' => $status,
                    'last_location' => $status === 'completed' ? 'Faskes penerima demo' : 'Hub Bandung demo',
                    'recipient_ready' => false, 'is_demo' => true,
                    'completed_at' => $status === 'completed' ? now()->subHour() : null,
                ]);
                if (! $shipment->wasRecentlyCreated) {
                    continue;
                }
                if ($status !== 'inspection') {
                    Inspection::create([
                        'shipment_id' => $shipment->id, 'user_id' => $checker->id,
                        'packaging' => true, 'seal' => true, 'label' => true, 'passed' => true,
                        'temperature' => $temp, 'note' => 'Contoh pemeriksaan demo; bukan pemeriksaan nyata.',
                        'sensor_device' => 'Demo sensor', 'hub_name' => 'Hub Bandung demo', 'inspected_at' => now()->subHours(2),
                    ]);
                }
                TelemetrySample::create([
                    'shipment_id' => $shipment->id, 'temperature' => $temp,
                    'latitude' => -6.2088, 'longitude' => 106.8456, 'speed_kmh' => 0,
                    'recorded_at' => now(), 'is_simulated' => true,
                ]);
                ShipmentEvent::create([
                    'shipment_id' => $shipment->id, 'user_id' => $admin->id,
                    'type' => 'demo_seed', 'title' => 'Data contoh dibuat', 'to_status' => $status,
                    'metadata' => ['is_demo' => true], 'created_at' => now(),
                ]);
            }
            foreach (Shipment::where('buyer_id', $vendor->id)->where('is_demo', true)->where('status', 'completed')->get() as $completed) {
                $snapshot = [
                    'tracking_number' => $completed->tracking_number,
                    'status' => $completed->status, 'is_demo' => true,
                    'sender_name' => $completed->sender_name, 'recipient_name' => $completed->recipient_name,
                    'item_summary' => $completed->item_summary,
                    'temperature_min' => $completed->temperature_min, 'temperature_max' => $completed->temperature_max,
                    'inspection' => $completed->latestInspection?->only(['packaging', 'seal', 'label', 'passed', 'temperature', 'note']),
                    'telemetry' => $completed->telemetry()->get(['temperature', 'latitude', 'longitude', 'is_simulated', 'recorded_at'])->toArray(),
                    'pod' => null, 'note' => 'Contoh laporan demo. Tidak ada bukti serah terima nyata.',
                ];
                ShipmentReport::firstOrCreate(['shipment_id' => $completed->id, 'revision' => 1], [
                    'title' => 'Laporan Pengiriman Demo', 'snapshot' => $snapshot,
                    'sha256' => ShipmentReport::hashSnapshot($snapshot), 'is_demo' => true, 'published_at' => now(),
                ]);
            }
        });
    }
}
