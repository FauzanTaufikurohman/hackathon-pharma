<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryProof;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ThermalIncident;
use App\Models\TrackingLocation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\ShipmentWhatsAppNotifier;

class CourierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shipments = Shipment::with([
            'customer',
            'originCustomerUser.customer',
            'destinationCustomerUser.customer',
            'product',
            'trackingLocations' => fn ($query) => $query->orderByDesc('recorded_at'),
        ])
            ->where('courier_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($shipments);
    }

    public function assignCourier(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'courier_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $result = DB::transaction(function () use ($validated, $id, $request) {
            $shipment = Shipment::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($shipment->status, ['checker_assigned'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Shipment harus sudah ditugaskan kepada checker.'],
                ]);
            }

            if (! $shipment->checker_id) {
                throw ValidationException::withMessages([
                    'checker_id' => ['Checker belum ditugaskan.'],
                ]);
            }

            if (! $shipment->thermalChecks()->exists()) {
                throw ValidationException::withMessages([
                    'thermal_check' => ['Pemeriksaan suhu wajib dilakukan sebelum penugasan kurir.'],
                ]);
            }

            if (ThermalIncident::where('shipment_id', $shipment->id)
                ->where('incident_status', 'open')
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'thermal_incident' => [
                        'Masih ada insiden suhu terbuka. Selesaikan penilaiannya sebelum pengiriman dilanjutkan.',
                    ],
                ]);
            }

            $courier = User::whereKey($validated['courier_id'])
                ->where('role', 'courier')
                ->where('is_active', true)
                ->first();

            if (! $courier) {
                throw ValidationException::withMessages([
                    'courier_id' => ['User harus aktif dan memiliki role courier.'],
                ]);
            }

            $shipment->courier_id = $courier->id;
            $shipment->save();

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'user_id' => $request->user()->id,
                'event_type' => 'courier_assigned',
                'from_status' => $shipment->status,
                'to_status' => $shipment->status,
                'description' => 'Kurir ditugaskan ke shipment.',
                'metadata' => ['courier_id' => $courier->id],
                'occurred_at' => now(),
            ]);

            return $shipment->fresh(['customer', 'product', 'courier', 'checker']);
        });

        return response()->json([
            'message' => 'Kurir berhasil ditugaskan.',
            'data' => $result,
        ]);
    }

    public function start(Request $request, int $id): JsonResponse
    {
        $shipment = DB::transaction(function () use ($request, $id) {
            $shipment = Shipment::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $shipment->courier_id !== (int) $request->user()->id) {
                abort(403, 'Shipment ini tidak ditugaskan kepada Anda.');
            }

            if ($shipment->status !== 'checker_assigned') {
                throw ValidationException::withMessages([
                    'status' => ['Shipment belum siap dimulai atau sudah diproses.'],
                ]);
            }

            if (! $shipment->thermalChecks()->exists()) {
                throw ValidationException::withMessages([
                    'thermal_check' => ['Pemeriksaan suhu belum dilakukan.'],
                ]);
            }

            if (ThermalIncident::where('shipment_id', $shipment->id)
                ->where('incident_status', 'open')
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'thermal_incident' => [
                        'Pengiriman ditahan karena masih ada insiden suhu terbuka.',
                    ],
                ]);
            }

            $previousStatus = $shipment->status;

            $shipment->status = 'in_transit';
            $shipment->started_at = now();
            $shipment->save();

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'user_id' => $request->user()->id,
                'event_type' => 'shipment_started',
                'from_status' => $previousStatus,
                'to_status' => $shipment->status,
                'description' => 'Kurir memulai pengiriman.',
                'metadata' => null,
                'occurred_at' => now(),
            ]);

            return $shipment->fresh(['customer', 'product', 'courier']);
        });
        app(ShipmentWhatsAppNotifier::class)->notifyCustomer(
            $shipment,
            "Anteraja Pharma: pengiriman {$shipment->shipment_number} telah dimulai. Status: IN TRANSIT."
        );
        return response()->json([
            'message' => 'Pengiriman dimulai.',
            'data' => $shipment,
        ]);
    }

    public function updateLocation(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $shipment = Shipment::whereKey($id)
            ->where('courier_id', $request->user()->id)
            ->firstOrFail();

        if ($shipment->status !== 'in_transit') {
            throw ValidationException::withMessages([
                'status' => ['Lokasi hanya dapat dicatat saat shipment sedang dikirim.'],
            ]);
        }

        $location = TrackingLocation::create([
            'shipment_id' => $shipment->id,
            'courier_id' => $request->user()->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'is_simulated' => true,
            'recorded_at' => $validated['recorded_at'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Lokasi simulasi berhasil dicatat.',
            'data' => $location,
        ], 201);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $shipmentData = Shipment::with([
            'destinationCustomerUser.customer',
            'customer',
        ])->findOrFail($id);
        $recipientName = $shipmentData->destinationCustomerUser?->customer?->contact_name
            ?? $shipmentData->destinationCustomerUser?->name
            ?? $shipmentData->destinationCustomerUser?->customer?->company_name
            ?? $shipmentData->customer?->contact_name
            ?? $shipmentData->customer?->company_name;
        $photoPath = $request->file('photo')->store('delivery-proofs', 'public');

        try {
            $shipment = DB::transaction(function () use (
                $request,
                $id,
                $photoPath,
                $recipientName
            ) {
                $shipment = Shipment::whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $shipment->courier_id !== (int) $request->user()->id) {
                    abort(403, 'Shipment ini tidak ditugaskan kepada Anda.');
                }

                if ($shipment->status !== 'in_transit') {
                    throw ValidationException::withMessages([
                        'status' => ['Hanya shipment in_transit yang dapat diselesaikan.'],
                    ]);
                }

                $previousStatus = $shipment->status;
                $deliveredAt = now();

                DeliveryProof::create([
                    'shipment_id' => $shipment->id,
                    'courier_id' => $request->user()->id,
                    'photo_path' => $photoPath,
                    'recipient_name' => $recipientName,
                    'notes' => null,
                    'delivered_at' => $deliveredAt,
                ]);

                $shipment->status = 'delivered';
                $shipment->delivered_at = $deliveredAt;
                $shipment->save();

                ShipmentEvent::create([
                    'shipment_id' => $shipment->id,
                    'user_id' => $request->user()->id,
                    'event_type' => 'shipment_delivered',
                    'from_status' => $previousStatus,
                    'to_status' => $shipment->status,
                    'description' => 'Bukti serah terima telah disimpan.',
                    'metadata' => [
                        'recipient_name' => $recipientName,
                        'photo_path' => $photoPath,
                    ],
                    'occurred_at' => $deliveredAt,
                ]);

                return $shipment->fresh(['customer', 'product', 'courier']);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($photoPath);
            throw $exception;
        }
        $reportQueued = app(ShipmentWhatsAppNotifier::class)
            ->notifyShipmentReport($shipment);
        return response()->json([
            'message' => 'Pengiriman selesai dan bukti serah terima tersimpan.',
            'data' => $shipment,
            'whatsapp_report_queued' => $reportQueued,
        ]);
    }
}