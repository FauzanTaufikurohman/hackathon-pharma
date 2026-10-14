<?php

namespace App;

use App\Models\CourierActionReceipt;
use App\Models\Message;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\User;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CourierWorkflow
{
    public function perform(User $user, string $awb, string $action, array $data, Closure $operation): array
    {
        $hash = hash('sha256', json_encode($this->canonical(['awb' => $awb, 'action' => $action, 'data' => $data]), JSON_THROW_ON_ERROR));
        try {
            return DB::transaction(function () use ($user, $awb, $action, $data, $operation, $hash) {
                $freshUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                abort_unless($freshUser->is_active && $freshUser->role === 'courier' && $user->tokenCan('courier'), 403, 'Akses ini khusus kurir aktif.');
                $receipt = CourierActionReceipt::where('request_id', $data['request_id'])->first();
                if ($receipt) {
                    abort_unless($receipt->user_id === $user->id && $receipt->action === $action && hash_equals($receipt->payload_hash, $hash), 409, 'Request ID sudah digunakan untuk tindakan lain.');

                    return ['data' => $receipt->result, 'replayed' => true];
                }
                $shipment = app(CourierShipmentService::class)->owned($user)->where('tracking_number', $awb)->lockForUpdate()->firstOrFail();
                if (isset($data['version'])) {
                    abort_unless($shipment->version === (int) $data['version'], 409, 'Data tugas berubah. Muat ulang terlebih dahulu.');
                }
                abort_if(in_array($shipment->status, ['completed', 'delivered', 'returned'], true), 409, 'Kiriman sudah selesai dan dikunci.');
                $result = $operation($shipment, $freshUser);
                CourierActionReceipt::create([
                    'request_id' => $data['request_id'], 'shipment_id' => $shipment->id, 'user_id' => $user->id,
                    'action' => $action, 'payload_hash' => $hash, 'result' => $result,
                ]);

                return ['data' => $result, 'replayed' => false];
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            abort(409, 'Request ID sudah digunakan. Muat ulang data.');
        }
    }

    private function canonical(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return ['sha256' => hash_file('sha256', $value->path()), 'mime' => $value->getMimeType()];
        }
        if (! is_array($value)) {
            return $value;
        }
        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(fn ($item) => $this->canonical($item), $value);
    }

    public function approved(Shipment $shipment): void
    {
        $shipment->loadMissing(['latestApproval', 'latestInspection']);
        $approval = $shipment->latestApproval;
        $inspection = $shipment->latestInspection;
        abort_unless($approval && $approval->decision === 'approved' && $inspection && $inspection->passed
            && $approval->inspection_id === $inspection->id
            && User::whereKey($approval->user_id)->where('role', 'admin')->exists(), 409, 'Pemeriksaan belum mendapat persetujuan Admin yang valid.');
        abort_unless($shipment->is_demo || $inspection->photo_path, 409, 'Foto pemeriksaan wajib tersedia sebelum berangkat.');
    }

    public function record(Shipment $shipment, ?User $actor, string $type, string $title, string $from, array $metadata = [], ?string $reason = null): ShipmentEvent
    {
        return ShipmentEvent::create([
            'shipment_id' => $shipment->id, 'user_id' => $actor?->id, 'type' => $type,
            'title' => $title, 'reason' => $reason, 'from_status' => $from, 'to_status' => $shipment->status,
            'metadata' => $metadata + ['actor_name' => $actor?->name ?? 'Sistem', 'actor_role' => $actor?->role ?? 'system'],
            'created_at' => now(),
        ]);
    }

    public function notify(Shipment $shipment, ShipmentEvent $event, ?int $courierId = null): void
    {
        $ids = User::where('role', 'admin')->pluck('id')->push($shipment->buyer_id)->push($courierId ?? $shipment->courier_id)->filter()->unique();
        foreach ($ids as $id) {
            Message::firstOrCreate(['dedupe_key' => 'courier:'.$event->id.':'.$id], [
                'shipment_id' => $shipment->id, 'user_id' => $id, 'subject' => $event->title,
                'body' => $shipment->tracking_number.': '.$event->title, 'status' => 'pending',
            ]);
        }
    }
}
