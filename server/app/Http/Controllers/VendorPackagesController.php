<?php

namespace App\Http\Controllers;

use App\VendorReportService;
use App\VendorShipmentService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class VendorPackagesController extends Controller
{
    public function export(Request $request, VendorShipmentService $service, VendorReportService $reports): Response
    {
        $filters = $request->validate([
            'category' => ['sometimes', Rule::in(['all', 'transit', 'qc', 'delivered'])],
            'status' => ['sometimes', Rule::in(array_keys(VendorShipmentService::LABELS))],
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $query = $service->filtered($request->user(), $filters);
        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', CarbonImmutable::parse($filters['from'], 'Asia/Jakarta')->utc());
        }
        if (! empty($filters['to'])) {
            $query->where('created_at', '<', CarbonImmutable::parse($filters['to'], 'Asia/Jakarta')->addDay()->utc());
        }
        abort_if((clone $query)->count() > 500, 422, 'Rekap dibatasi 500 paket. Persempit tanggal atau filter.');
        $rows = $query->orderBy('id')->get(['tracking_number', 'item_summary', 'status', 'is_demo']);
        $content = '<p>Dicetak '.e(now()->timezone('Asia/Jakarta')->format('d M Y H:i')).' WIB. Hanya paket akun ini.</p><table><tr><th>Resi</th><th>Muatan</th><th>Status</th></tr>';
        foreach ($rows as $row) {
            $content .= '<tr><td>'.e($row->tracking_number).($row->is_demo ? ' (demo)' : '').'</td><td>'.e($row->item_summary).'</td><td>'.e(VendorShipmentService::LABELS[$row->status] ?? $row->status).'</td></tr>';
        }
        $content .= '</table>';

        return $reports->pdf('Rekap paket farmasi', $content, 'rekap-paket.pdf');
    }

    public function index(Request $request, VendorShipmentService $service): JsonResponse
    {
        $filters = $request->validate([
            'category' => ['sometimes', Rule::in(['all', 'transit', 'qc', 'delivered'])],
            'status' => ['sometimes', Rule::in(array_keys(VendorShipmentService::LABELS))],
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'sort' => ['sometimes', Rule::in(['newest', 'oldest', 'eta'])],
        ]);
        $query = $service->filtered($request->user(), $filters)->with(['latestTelemetry', 'latestInspection.checker']);
        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('id'),
            'eta' => $query->orderByRaw('CASE WHEN scheduled_at IS NULL THEN 1 ELSE 0 END')->orderBy('scheduled_at')->orderBy('id'),
            default => $query->orderByDesc('id'),
        };
        $page = $query->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($s) => $service->serialize($s)),
            'counts' => $service->counts($request->user()),
            'meta' => [
                'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(), 'total' => $page->total(),
            ],
        ])->header('Cache-Control', 'private, no-store');
    }
}
