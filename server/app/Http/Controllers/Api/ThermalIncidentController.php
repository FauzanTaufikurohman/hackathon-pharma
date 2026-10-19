<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ThermalIncident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\ShipmentWhatsAppNotifier;
use App\Jobs\AnalyzeThermalRecord;

class ThermalIncidentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ThermalIncident::with([
            'shipment.customer',
            'shipment.product',
            'thermalCheck',
        ])->whereHas('shipment', fn ($shipment) => $shipment->where('status', 'in_transit'))
            ->orderByDesc('detected_at');

        if ($request->filled('incident_status')) {
            $query->where(
                'incident_status',
                $request->string('incident_status')->toString()
            );
        }

        if ($request->filled('shipment_id')) {
            $query->where('shipment_id', $request->integer('shipment_id'));
        }

        return response()->json($query->paginate(15));
    }

    public function debug(int $id): JsonResponse
    {
        $incident = ThermalIncident::findOrFail($id);
        $debugEnabled = (bool) config('services.openai.thermal_debug', false);

        return response()->json([
            'data' => [
                'debug_enabled' => $debugEnabled,
                'ai_assessment_status' => $incident->ai_assessment_status,
                'ai_model' => $incident->ai_model,
                'ai_assessed_at' => $incident->ai_assessed_at,
                'diagnostics' => $debugEnabled ? $incident->ai_debug : null,
            ],
        ]);
    }

    public function simulate(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'temperature_celsius' => [
                'required',
                'numeric',
                'between:-100,100',
            ],
            'exposure_duration_seconds' => [
                'required',
                'integer',
                'min:1',
                'max:2592000',
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $incident = DB::transaction(function () use ($validated, $request, $id) {
            $shipment = Shipment::with('product')
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($shipment->status !== 'in_transit') {
                throw ValidationException::withMessages([
                    'status' => ['Simulasi suhu hanya dapat dilakukan pada shipment in_transit.'],
                ]);
            }

            $product = $shipment->product;

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'product' => ['Produk tidak ditemukan atau tidak aktif.'],
                ]);
            }

            $temperature = (float) $validated['temperature_celsius'];
            $min = (float) $product->min_temperature;
            $max = (float) $product->max_temperature;
            $duration = (int) $validated['exposure_duration_seconds'];

            if ($temperature >= $min && $temperature <= $max) {
                throw ValidationException::withMessages([
                    'temperature_celsius' => [
                        'Suhu simulasi harus berada di luar rentang produk.',
                    ],
                ]);
            }

            $maxExcursion = $product->max_excursion_seconds;

            // Demo classification only. It is not a validated
            // pharmaceutical stability or release decision.
            $severity = $maxExcursion !== null && $duration > $maxExcursion
                ? 'critical'
                : 'warning';

            $incident = ThermalIncident::create([
                'shipment_id' => $shipment->id,
                'thermal_check_id' => null,
                'temperature_celsius' => $temperature,
                'allowed_min_temperature' => $min,
                'allowed_max_temperature' => $max,
                'exposure_duration_seconds' => $duration,
                'severity' => $severity,
                'incident_status' => 'open',
                'description' => $validated['description']
                    ?? sprintf(
                        'SIMULASI: suhu %.2f°C berada di luar rentang %.2f–%.2f°C selama %d detik.',
                        $temperature,
                        $min,
                        $max,
                        $duration
                    ),
                'corrective_action' => null,
                'detected_at' => now(),
                'recovered_at' => null,
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'user_id' => $request->user()->id,
                'event_type' => 'thermal_excursion_simulated',
                'from_status' => $shipment->status,
                'to_status' => $shipment->status,
                'description' => 'Admin membuat simulasi excursion suhu.',
                'metadata' => [
                    'incident_id' => $incident->id,
                    'temperature_celsius' => $temperature,
                    'exposure_duration_seconds' => $duration,
                    'severity' => $severity,
                    'is_simulated' => true,
                ],
                'occurred_at' => now(),
            ]);

            return $incident->load(['shipment.product', 'shipment.customer']);
        });
        AnalyzeThermalRecord::dispatch(
            'thermal_incident',
            $incident->id
        )->afterCommit();

        app(ShipmentWhatsAppNotifier::class)->notifyCustomer(
            $incident->shipment,
            sprintf(
                'PERINGATAN SUHU ANTERAJA PHARMA: Shipment %s mengalami excursion suhu SIMULASI. Suhu: %.2f°C; rentang produk: %.2f–%.2f°C; durasi paparan: %d detik; tingkat: %s. Insiden menunggu penilaian. Ini bukan keputusan bahwa produk aman atau tidak aman.',
                $incident->shipment->shipment_number,
                (float) $incident->temperature_celsius,
                (float) $incident->allowed_min_temperature,
                (float) $incident->allowed_max_temperature,
                (int) $incident->exposure_duration_seconds,
                strtoupper($incident->severity),
            )
        );
        return response()->json([
            'message' => 'Simulasi excursion suhu berhasil dicatat. Insiden memerlukan penilaian.',
            'data' => $incident,
        ], 201);
    }

    public function resolve(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'corrective_action' => ['required', 'string', 'min:5', 'max:2000'],
            'assessment_note' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $incident = DB::transaction(function () use ($validated, $request, $id) {
            $incident = ThermalIncident::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $shipment = Shipment::whereKey($incident->shipment_id)
                ->lockForUpdate()
                ->firstOrFail();
            if ($shipment->status !== 'in_transit') {
                throw ValidationException::withMessages([
                    'status' => ['Insiden hanya dapat ditangani saat shipment berstatus in_transit.'],
                ]);
            }

            if ($incident->incident_status !== 'open') {
                throw ValidationException::withMessages([
                    'incident_status' => ['Insiden ini tidak lagi berstatus open.'],
                ]);
            }

            $previousStatus = $incident->incident_status;

            $incident->corrective_action = $validated['corrective_action'];
            $incident->incident_status = 'resolved';
            $incident->recovered_at = now();
            $incident->save();

            ShipmentEvent::create([
                'shipment_id' => $incident->shipment_id,
                'user_id' => $request->user()->id,
                'event_type' => 'thermal_incident_resolved',
                'from_status' => $previousStatus,
                'to_status' => $incident->incident_status,
                'description' => 'Insiden suhu ditandai resolved oleh Admin setelah penilaian.',
                'metadata' => [
                    'incident_id' => $incident->id,
                    'assessment_note' => $validated['assessment_note'],
                    'corrective_action' => $validated['corrective_action'],
                ],
                'occurred_at' => now(),
            ]);

            return $incident->fresh(['shipment.product', 'thermalCheck']);
        });
        app(ShipmentWhatsAppNotifier::class)->notifyIncidentResolved(
            $incident->shipment,
            $validated['assessment_note']
        );

        return response()->json([
            'message' => 'Penilaian dan tindakan korektif berhasil dicatat.',
            'data' => $incident,
        ]);
    }
}