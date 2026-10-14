<?php

namespace App\Http\Controllers;

use App\CourierShipmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourierTasksController extends Controller
{
    public function index(Request $request, CourierShipmentService $service): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['sometimes', Rule::in(['all', ...array_keys(CourierShipmentService::BUCKETS)])],
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $query = $service->owned($request->user())->with(['latestAssignment', 'latestTelemetry', 'latestInspection.checker', 'deliveryProof']);
        if (($filters['status'] ?? 'all') !== 'all') {
            $query->whereIn('status', CourierShipmentService::BUCKETS[$filters['status']]);
        }
        if (! empty($filters['search'])) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower(trim($filters['search']))).'%';
            $query->where(function (Builder $query) use ($pattern) {
                foreach (['tracking_number', 'item_summary', 'recipient_name', 'address', 'sender_name'] as $column) {
                    $query->orWhereRaw("LOWER($column) LIKE ? ESCAPE '!'", [$pattern]);
                }
            });
        }
        $page = $query->orderByDesc('id')->paginate($filters['per_page'] ?? 20);

        return response()->json(['data' => $page->getCollection()->map(fn ($shipment) => $service->task($shipment)),
            'counts' => $service->counts($request->user()),
            'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(), 'per_page' => $page->perPage()],
        ])->header('Cache-Control', 'private, no-store');
    }
}
