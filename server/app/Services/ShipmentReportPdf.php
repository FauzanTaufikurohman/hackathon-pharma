<?php

namespace App\Services;

use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;

class ShipmentReportPdf
{
    public function render(Shipment $shipment): string
    {
        $shipment = Shipment::with([
            'customer',
            'originCustomerUser.customer',
            'destinationCustomerUser.customer',
            'product',
            'checker',
            'courier',
            'events' => fn ($query) => $query->orderBy('occurred_at'),
            'thermalChecks' => fn ($query) => $query->orderBy('checked_at'),
            'thermalIncidents' => fn ($query) => $query->orderBy('detected_at'),
            'deliveryProofs',
            'trackingLocations' => fn ($query) => $query->orderBy('recorded_at'),
        ])->findOrFail($shipment->getKey());

        return Pdf::loadView('reports.shipment', [
            'shipment' => $shipment,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait')->output();
    }

    public function fileName(Shipment $shipment): string
    {
        return 'shipment-'.preg_replace(
            '/[^A-Za-z0-9._-]/',
            '-',
            $shipment->shipment_number
        ).'.pdf';
    }
}
