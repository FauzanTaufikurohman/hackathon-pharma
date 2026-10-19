<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerShipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customerId = $request->user()->customer_id;

        $shipments = Shipment::with([
            'customer:id,company_name',
            'originCustomerUser.customer',
            'destinationCustomerUser.customer',
            'product:id,sku,name,min_temperature,max_temperature',
            'thermalChecks' => fn ($query) => $query->orderByDesc('checked_at'),
            'thermalIncidents' => fn ($query) => $query->orderByDesc('detected_at'),
            'trackingLocations' => fn ($query) => $query->orderByDesc('recorded_at'),
            'events' => fn ($query) => $query->orderBy('occurred_at'),
            'deliveryProofs',
        ])
            ->where(function ($query) use ($customerId, $request) {
                if ($customerId !== null) {
                    $query->where('customer_id', $customerId)
                        ->orWhere('origin_customer_user_id', $request->user()->id)
                        ->orWhere('destination_customer_user_id', $request->user()->id);
                } else {
                    $query->where('origin_customer_user_id', $request->user()->id)
                        ->orWhere('destination_customer_user_id', $request->user()->id);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($shipments);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::with([
            'customer:id,company_name',
            'originCustomerUser.customer',
            'destinationCustomerUser.customer',
            'product:id,sku,name,min_temperature,max_temperature',
            'thermalChecks' => fn ($query) => $query->orderByDesc('checked_at'),
            'thermalIncidents' => fn ($query) => $query->orderByDesc('detected_at'),
            'trackingLocations' => fn ($query) => $query->orderByDesc('recorded_at'),
            'events' => fn ($query) => $query->orderBy('occurred_at'),
            'deliveryProofs',
        ])
            ->where(function ($query) use ($request) {
                $query->where('customer_id', $request->user()->customer_id)
                    ->orWhere('origin_customer_user_id', $request->user()->id)
                    ->orWhere('destination_customer_user_id', $request->user()->id);
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ]);
    }
}
