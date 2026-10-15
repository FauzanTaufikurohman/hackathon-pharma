<?php

namespace App;

use App\Models\Message;
use App\Models\ShipmentEvent;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class VendorReadinessService
{
    public function update(User $user, string $awb, array $data, VendorShipmentService $service): array
    {
        try {
            return DB::transaction(function () use ($user, $awb, $data, $service) {
                $shipment = $service->owned($user)->where('tracking_number', $awb)->lockForUpdate()->firstOrFail();
                $previous = ShipmentEvent::where('request_id', $data['request_id'])->first();
                if ($previous) {
                    abort_unless($previous->user_id === $user->id && $previous->shipment_id === $shipment->id
                        && $previous->type === 'recipient_readiness'
                        && (bool) $previous->metadata['recipient_ready'] === (bool) $data['recipient_ready'], 409, 'Request ID sudah digunakan untuk tindakan lain.');

                    return ['data' => $previous->metadata['result'], 'replayed' => true];
                }
                abort_if(in_array($shipment->status, ['completed', 'delivered', 'returned', 'cancelled'], true), 409, 'Kiriman sudah selesai; kesiapan tidak dapat diubah.');
                abort_unless($shipment->version === (int) $data['version'], 409, 'Data kiriman berubah. Muat ulang sebelum menyimpan.');
                $shipment->recipient_ready = (bool) $data['recipient_ready'];
                $shipment->version++;
                $shipment->save();
                $result = $shipment->only(['tracking_number', 'recipient_ready', 'version', 'status']);
                $event = ShipmentEvent::create([
                    'shipment_id' => $shipment->id, 'user_id' => $user->id,
                    'type' => 'recipient_readiness', 'title' => $shipment->recipient_ready ? 'Penerima siap menerima' : 'Penerima belum siap',
                    'from_status' => $shipment->status, 'to_status' => $shipment->status,
                    'request_id' => $data['request_id'],
                    'metadata' => ['recipient_ready' => $shipment->recipient_ready, 'result' => $result],
                    'created_at' => now(),
                ]);
                $recipients = User::where('role', 'admin')
                    ->when($shipment->courier_id, fn ($query) => $query->orWhere('id', $shipment->courier_id))->pluck('id');
                foreach ($recipients as $recipientId) {
                    Message::create([
                        'shipment_id' => $shipment->id, 'user_id' => $recipientId,
                        'subject' => $event->title,
                        'body' => $shipment->tracking_number.': '.$event->title,
                        'status' => 'pending', 'dedupe_key' => $event->id.':'.$recipientId,
                    ]);
                }

                return ['data' => $result, 'replayed' => false, 'notification_status' => 'queued'];
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            abort(409, 'Request ID sudah digunakan. Muat ulang data.');
        }
    }
}
