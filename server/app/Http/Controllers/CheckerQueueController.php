<?php

namespace App\Http\Controllers;

use App\OperationsWorkflow;
use App\VendorShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckerQueueController extends Controller
{
    public function queue(Request $request, OperationsWorkflow $workflow, VendorShipmentService $serializer): JsonResponse
    {
        $base = $workflow->visible($request->user(), 'checker');
        $data = $request->validate(['search' => ['sometimes', 'string', 'max:120'], 'page' => ['sometimes', 'integer', 'between:1,100000'],
            'per_page' => ['sometimes', 'integer', 'between:1,100']]);
        $counts = (clone $base)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $query = $base->whereIn('status', ['created', 'inspection']);
        if (trim($data['search'] ?? '') !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower(trim($data['search']))).'%';
            $query->where(fn ($q) => $q->whereRaw("LOWER(tracking_number) LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("LOWER(item_summary) LIKE ? ESCAPE '!'", [$pattern]));
        }
        $page = $query->with(['latestTelemetry', 'latestInspection.checker'])->orderBy('scheduled_at')->orderBy('id')->paginate($data['per_page'] ?? 20);

        return response()->json(['data' => $page->getCollection()->map(fn ($s) => $serializer->serialize($s)), 'counts' => $counts,
            'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()]])->header('Cache-Control', 'private, no-store');
    }
}
