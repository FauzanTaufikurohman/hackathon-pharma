<?php

namespace App;

use App\Models\Message;
use App\Models\Shipment;
use App\Models\ShipmentReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportDistribution
{
    public function queue(ShipmentReport $report): void
    {
        abort_unless(hash_equals($report->sha256, $report->snapshotHash()), 409, 'Integritas laporan tidak cocok.');
        DB::transaction(function () use ($report): void {
            $shipment = Shipment::findOrFail($report->shipment_id);
            $ids = User::where('role', 'admin')->where('is_active', true)->pluck('id')
                ->push($shipment->buyer_id)->push($shipment->courier_id)->filter()->unique();
            foreach ($ids as $id) {
                Message::firstOrCreate(['dedupe_key' => 'report:'.$report->id.':'.$id], [
                    'user_id' => $id, 'shipment_id' => $shipment->id, 'shipment_report_id' => $report->id,
                    'subject' => 'Laporan pengiriman tersedia',
                    'body' => $shipment->tracking_number.' | PDF laporan R'.$report->revision.' terlampir. | SHA256 snapshot '.$report->sha256,
                    'status' => 'pending',
                ]);
            }
        });
    }
}
