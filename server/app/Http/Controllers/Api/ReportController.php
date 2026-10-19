<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ThermalIncident;
use App\Services\ShipmentReportPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => [
                'nullable',
                'string',
                'in:pending,approved,rejected,checker_assigned,in_transit,delivered,cancelled',
            ],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);
    }

    private function shipmentsQuery(array $filters)
    {
        return Shipment::query()
            ->with([
                'customer',
                'product',
                'checker',
                'courier',
            ])
            ->when(
                $filters['from'] ?? null,
                fn($query, $from) => $query->whereDate('created_at', '>=', $from)
            )
            ->when(
                $filters['to'] ?? null,
                fn($query, $to) => $query->whereDate('created_at', '<=', $to)
            )
            ->when(
                $filters['status'] ?? null,
                fn($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['product_id'] ?? null,
                fn($query, $productId) => $query->where('product_id', $productId)
            )
            ->when(
                $filters['customer_id'] ?? null,
                fn($query, $customerId) => $query->where('customer_id', $customerId)
            );
    }

    public function summary(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);
        $query = $this->shipmentsQuery($filters);

        $shipments = (clone $query)->get(['id', 'status']);

        $incidentQuery = ThermalIncident::query()
            ->whereIn('shipment_id', $shipments->pluck('id'));

        $incidents = (clone $incidentQuery)->get([
            'id',
            'shipment_id',
            'severity',
            'incident_status',
        ]);

        return response()->json([
            'filters' => $filters,
            'data' => [
                'total_shipments' => $shipments->count(),
                'shipments_by_status' => $shipments
                    ->countBy('status'),
                'total_thermal_incidents' => $incidents->count(),
                'open_thermal_incidents' => $incidents
                    ->where('incident_status', 'open')
                    ->count(),
                'incidents_by_severity' => $incidents
                    ->countBy('severity'),
            ],
        ]);
    }

    public function shipments(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);

        $shipments = $this->shipmentsQuery($filters)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return response()->json($shipments);
    }

    public function shipmentPdf(
        Request $request,
        int $id,
        ShipmentReportPdf $reportPdf,
    ): Response
    {
        $shipment = Shipment::findOrFail($id);

        return response($reportPdf->render($shipment), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' .
                $reportPdf->fileName($shipment) . '"',
        ]);
    }

    public function incidentPdf(Request $request, int $id): Response
    {
        $incident = ThermalIncident::with([
            'shipment.customer',
            'shipment.product',
            'thermalCheck',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('reports.thermal-incident', [
            'incident' => $incident,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            'thermal-incident-' . $incident->id . '.pdf'
        );
    }
}