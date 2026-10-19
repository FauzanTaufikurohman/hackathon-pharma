<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShipmentEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\ShipmentWhatsAppNotifier;

class ShipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            max((int) $request->query('per_page', 10), 1),
            100
        );

        $shipments = Shipment::query()
            ->with([
                'customer:id,company_name',
                'originCustomerUser.customer',
                'destinationCustomerUser.customer',
                'product:id,sku,name,min_temperature,max_temperature',
                'checker:id,name',
                'courier:id,name',
            ])
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->query('search');

                    $query->where(
                        'shipment_number',
                        'ilike',
                        "%{$search}%"
                    );
                }
            )
            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    $query->where(
                        'status',
                        $request->query('status')
                    );
                }
            )
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Shipments retrieved successfully.',
            'data' => $shipments->items(),
            'meta' => [
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
                'per_page' => $shipments->perPage(),
                'total' => $shipments->total(),
            ],
        ]);
    }


    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin_customer_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', 'customer')
                    ->where('is_active', true),
            ],
            'destination_customer_user_id' => [
                'required',
                'integer',
                'different:origin_customer_user_id',
                Rule::exists('users', 'id')
                    ->where('role', 'customer')
                    ->where('is_active', true),
            ],
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'origin_address' => [
                'required',
                'string',
                'max:1000',
            ],
            'destination_address' => [
                'required',
                'string',
                'max:1000',
            ],
            'scheduled_at' => [
                'nullable',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $parties = $this->validateCustomerParties($validated);
        $shipment = Shipment::create([
            ...$validated,
            'customer_id' => $parties['origin']->customer_id,
            'shipment_number' => 'AP-' . now()->format('YmdHis')
                . '-' . strtoupper(\Illuminate\Support\Str::random(5)),
            'status' => 'pending',
        ]);

        $shipment->load([
            'customer:id,company_name',
            'originCustomerUser.customer',
            'destinationCustomerUser.customer',
            'product:id,sku,name,min_temperature,max_temperature',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shipment created successfully.',
            'data' => $shipment,
        ], 201);
    }


    public function show(string $id): JsonResponse
    {
        $shipment = Shipment::query()
            ->with([
                'customer',
                'originCustomerUser.customer',
                'destinationCustomerUser.customer',
                'product',
                'checker:id,name',
                'courier:id,name',
                'thermalChecks' => fn ($query) => $query->orderByDesc('checked_at'),
                'thermalIncidents' => fn ($query) => $query->orderByDesc('detected_at'),
                'events' => fn ($query) => $query->orderBy('occurred_at'),
                'trackingLocations' => fn ($query) => $query->orderByDesc('recorded_at'),
                'deliveryProofs',
            ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Shipment retrieved successfully.',
            'data' => $shipment,
        ]);
    }

    public function checkerIndex(Request $request): JsonResponse
    {
        $shipments = Shipment::with([
            'customer:id,company_name',
            'product:id,sku,name,min_temperature,max_temperature',
            'thermalChecks',
            'thermalIncidents',
        ])
            ->where('checker_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($shipments);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::whereKey($id)->firstOrFail();

        if ($shipment->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Hanya shipment pending yang dapat diedit.'],
            ]);
        }

        $validated = $request->validate([
            'origin_customer_user_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', 'customer')
                    ->where('is_active', true),
            ],
            'destination_customer_user_id' => [
                'sometimes',
                'required',
                'integer',
                'different:origin_customer_user_id',
                Rule::exists('users', 'id')
                    ->where('role', 'customer')
                    ->where('is_active', true),
            ],
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'origin_address' => ['sometimes', 'required', 'string', 'max:1000'],
            'destination_address' => ['sometimes', 'required', 'string', 'max:1000'],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        if (
            array_key_exists('origin_customer_user_id', $validated)
            || array_key_exists('destination_customer_user_id', $validated)
        ) {
            $originId = $validated['origin_customer_user_id']
                ?? $shipment->origin_customer_user_id;
            $destinationId = $validated['destination_customer_user_id']
                ?? $shipment->destination_customer_user_id;

            if ($originId === null || $destinationId === null) {
                throw ValidationException::withMessages([
                    'origin_customer_user_id' => [
                        'Pilih customer produsen dan customer penerima.',
                    ],
                ]);
            }

            $parties = $this->validateCustomerParties([
                'origin_customer_user_id' => $originId,
                'destination_customer_user_id' => $destinationId,
            ]);
            $validated['origin_customer_user_id'] = $originId;
            $validated['destination_customer_user_id'] = $destinationId;
            $validated['customer_id'] = $parties['origin']->customer_id;
        }

        $shipment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Shipment berhasil diperbarui.',
            'data' => $shipment->fresh(['customer', 'product']),
        ]);
    }

    private function validateCustomerParties(array $validated): array
    {
        if (
            (int) $validated['origin_customer_user_id']
            === (int) $validated['destination_customer_user_id']
        ) {
            throw ValidationException::withMessages([
                'destination_customer_user_id' => [
                    'Pengirim dan penerima harus merupakan akun customer yang berbeda.',
                ],
            ]);
        }

        $origin = User::with('customer')
            ->findOrFail($validated['origin_customer_user_id']);
        $destination = User::with('customer')
            ->findOrFail($validated['destination_customer_user_id']);

        if (! $origin->customer || $origin->customer->company_type !== 'manufacturer') {
            throw ValidationException::withMessages([
                'origin_customer_user_id' => [
                    'Customer asal harus merupakan akun customer bertipe produsen.',
                ],
            ]);
        }

        if (
            ! $destination->customer
            || ! in_array($destination->customer->company_type, ['distributor', 'pharmacy'], true)
        ) {
            throw ValidationException::withMessages([
                'destination_customer_user_id' => [
                    'Customer tujuan harus merupakan distributor atau apotek.',
                ],
            ]);
        }

        return compact('origin', 'destination');
    }

    public function approve(int $id): JsonResponse
    {
        $shipment = DB::transaction(function () use ($id) {
            $shipment = Shipment::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($shipment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => [
                        'Hanya pengiriman berstatus pending yang dapat disetujui.',
                    ],
                ]);
            }

            $shipment->status = 'approved';
            $shipment->save();

            return $shipment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengiriman berhasil disetujui.',
            'data' => $shipment,
        ]);
    }
    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $shipment = DB::transaction(function () use ($id, $validated) {
            $shipment = Shipment::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($shipment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => [
                        'Hanya pengiriman berstatus pending yang dapat ditolak.',
                    ],
                ]);
            }

            $shipment->status = 'rejected';

            // Simpan alasan pada notes tanpa menghapus catatan sebelumnya.
            $existingNotes = trim((string) $shipment->notes);
            $rejectionNote = 'Alasan penolakan: ' . $validated['reason'];

            $shipment->notes = $existingNotes !== ''
                ? $existingNotes . "\n" . $rejectionNote
                : $rejectionNote;

            $shipment->save();

            app(\App\Services\ShipmentWhatsAppNotifier::class)
                ->notifyRejection($shipment, $rejectionNote);

            return $shipment;
        });
        return response()->json([
            'success' => true,
            'message' => 'Pengiriman berhasil ditolak.',
            'data' => $shipment,
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $shipment = DB::transaction(function () use ($id, $validated, $request) {
            $shipment = Shipment::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($shipment->status, ['delivered', 'cancelled', 'rejected'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Shipment dengan status akhir tidak dapat dibatalkan.'],
                ]);
            }

            $previousStatus = $shipment->status;
            $shipment->status = 'cancelled';
            $shipment->cancelled_at = now();
            $shipment->notes = trim(
                (string) $shipment->notes . "\nAlasan pembatalan: " . $validated['reason']
            );
            $shipment->save();

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'user_id' => $request->user()->id,
                'event_type' => 'shipment_cancelled',
                'from_status' => $previousStatus,
                'to_status' => $shipment->status,
                'description' => 'Shipment dibatalkan oleh Admin.',
                'metadata' => ['reason' => $validated['reason']],
                'occurred_at' => now(),
            ]);

            return $shipment->fresh(['customer', 'product', 'courier']);
        });

        $reportQueued = app(ShipmentWhatsAppNotifier::class)
            ->notifyShipmentReport($shipment);

        return response()->json([
            'success' => true,
            'message' => 'Shipment dibatalkan.',
            'data' => $shipment,
            'whatsapp_report_queued' => $reportQueued,
        ]);
    }

    public function assignChecker(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'checker_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $shipment = DB::transaction(function () use ($id, $validated) {
            $shipment = Shipment::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($shipment->status, ['approved', 'checker_assigned'], true)) {
                throw ValidationException::withMessages([
                    'status' => [
                        'Checker hanya dapat ditetapkan pada pengiriman yang disetujui.',
                    ],
                ]);
            }

            $checker = User::whereKey($validated['checker_id'])
                ->where('role', 'checker')
                ->where('is_active', true)
                ->first();

            if (!$checker) {
                throw ValidationException::withMessages([
                    'checker_id' => [
                        'Pengguna tidak ditemukan, tidak aktif, atau bukan checker.',
                    ],
                ]);
            }

            $shipment->checker_id = $checker->id;
            $shipment->status = 'checker_assigned';
            $shipment->save();

            return $shipment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Checker berhasil ditetapkan.',
            'data' => $shipment,
        ]);
    }
}