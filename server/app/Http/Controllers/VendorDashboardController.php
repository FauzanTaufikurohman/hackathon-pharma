<?php

namespace App\Http\Controllers;

use App\VendorDashboardService;
use App\VendorReadinessService;
use App\VendorShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function show(Request $request, VendorDashboardService $dashboard, VendorShipmentService $shipments): JsonResponse
    {
        $data = $request->validate(['awb' => ['sometimes', 'string', 'max:100']]);

        return response()->json(['data' => $dashboard->dashboard($request->user(), $shipments, $data['awb'] ?? null)])
            ->header('Cache-Control', 'private, no-store');
    }

    public function readiness(Request $request, string $awb, VendorReadinessService $readiness, VendorShipmentService $shipments): JsonResponse
    {
        $data = $request->validate([
            'recipient_ready' => ['required', 'boolean'],
            'version' => ['required', 'integer', 'min:1'],
            'request_id' => ['required', 'uuid'],
        ]);

        return response()->json($readiness->update($request->user(), $awb, $data, $shipments))
            ->header('Cache-Control', 'private, no-store');
    }
}
