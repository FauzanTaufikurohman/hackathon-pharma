<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ThermalCheck;
use App\Models\ThermalIncident;
use App\Jobs\AnalyzeThermalRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ThermalInspectionController extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'temperature_celsius' => [
                'required',
                'numeric',
                'between:-100,100',
            ],
            'packaging_condition' => [
                'required',
                'string',
                'in:good,damaged,wet,opened',
            ],
            'physical_condition' => [
                'required',
                'string',
                'in:good,damaged,suspected',
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $user = $request->user();

        $result = DB::transaction(function () use (
            $validated,
            $request,
            $user,
            $id
        ) {
            $shipment = Shipment::with('product')
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $shipment->checker_id !== (int) $user->id) {
                abort(403, 'Pengiriman ini tidak ditugaskan kepada Anda.');
            }

            if ($shipment->status !== 'checker_assigned') {
                throw ValidationException::withMessages([
                    'status' => [
                        'Pengiriman belum siap diperiksa atau sudah diproses.',
                    ],
                ]);
            }

            $product = $shipment->product;

            if (!$product) {
                throw ValidationException::withMessages([
                    'product' => ['Produk pengiriman tidak ditemukan.'],
                ]);
            }

            if (!$product->is_active) {
                throw ValidationException::withMessages([
                    'product' => ['Produk sudah tidak aktif.'],
                ]);
            }

            $temperature = (float) $validated['temperature_celsius'];
            $min = (float) $product->min_temperature;
            $max = (float) $product->max_temperature;

            $outsideRange = $temperature < $min
                || $temperature > $max;

            $photoPath = null;

            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')
                    ->store('thermal-checks', 'public');
            }

            $check = ThermalCheck::create([
                'shipment_id' => $shipment->id,
                'checker_id' => $user->id,
                'temperature_celsius' => $temperature,
                'packaging_condition' =>
                $validated['packaging_condition'],
                'physical_condition' =>
                $validated['physical_condition'],
                'notes' => $validated['notes'] ?? null,
                'photo_path' => $photoPath,
                'checked_at' => now(),
            ]);

            $incident = null;

            if ($outsideRange) {
                $incident = ThermalIncident::create([
                    'shipment_id' => $shipment->id,
                    'thermal_check_id' => $check->id,
                    'temperature_celsius' => $temperature,
                    'allowed_min_temperature' => $min,
                    'allowed_max_temperature' => $max,
                    'exposure_duration_seconds' => null,
                    'severity' => 'pending_assessment',
                    'incident_status' => 'open',
                    'description' => sprintf(
                        'Suhu pemeriksaan %.2f°C berada di luar batas produk %.2f°C sampai %.2f°C.',
                        $temperature,
                        $min,
                        $max
                    ),
                    'detected_at' => now(),
                ]);
            }

            return [
                'check' => $check,
                'incident' => $incident,
                'outside_range' => $outsideRange,
            ];
        });

        AnalyzeThermalRecord::dispatch(
            'thermal_check',
            $result['check']->id
        )->afterCommit();
        $result['check']->refresh();

        return response()->json([
            'success' => true,
            'message' => $result['outside_range']
                ? 'Pemeriksaan tersimpan. Insiden suhu dibuat dan memerlukan penilaian.'
                : 'Pemeriksaan tersimpan. Suhu berada dalam rentang yang ditetapkan.',
            'data' => [
                'thermal_check' => $result['check'],
                'ai_assessment_status' => $result['check']->ai_assessment_status,
                'temperature_status' => $result['outside_range']
                    ? 'outside_range'
                    : 'within_range',
                'incident' => $result['incident'],
            ],
        ], 201);
    }
}