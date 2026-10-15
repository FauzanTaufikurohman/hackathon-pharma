<?php

namespace App;

use App\Models\CourierActionReceipt;
use App\Models\Shipment;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class OperationsWorkflow
{
    public function validateAction(Request $request, array $rules = []): array
    {
        return $request->validate($rules + ['version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid']]);
    }

    public function authorize(User $user, string $role): void
    {
        abort_unless($user->is_active && $user->role === $role && $user->tokenCan($role), 403, 'Akun ini tidak memiliki akses '.$role.'.');
    }

    public function visible(User $user, string $role): Builder
    {
        $this->authorize($user, $role);
        $query = Shipment::query();
        if ($role === 'checker') {
            $query->where('inspector_id', $user->id);
        }

        return $query;
    }

    public function canonical(mixed $value): mixed
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

    public function perform(User $actor, string $role, ?string $awb, string $action, array $data, Closure $operation): array
    {
        $this->authorize($actor, $role);
        $action = $role.':'.$action;
        $hash = hash('sha256', json_encode($this->canonical(['awb' => $awb, 'action' => $action, 'data' => $data]), JSON_THROW_ON_ERROR));
        try {
            return DB::transaction(function () use ($actor, $role, $awb, $action, $data, $hash, $operation) {
                $fresh = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
                abort_unless($fresh->role === $role && $fresh->is_active, 403);
                $receipt = CourierActionReceipt::where('request_id', $data['request_id'])->first();
                if ($receipt) {
                    abort_unless($receipt->user_id === $actor->id && $receipt->action === $action && hash_equals($receipt->payload_hash, $hash), 409, 'Request ID telah dipakai dengan data berbeda.');

                    return ['data' => $receipt->result, 'replayed' => true];
                }
                $shipment = $awb === null ? null : $this->visible($actor, $role)->where('tracking_number', $awb)->lockForUpdate()->firstOrFail();
                if ($shipment) {
                    abort_unless($shipment->version === (int) $data['version'], 409, 'Data kiriman berubah. Muat ulang terlebih dahulu.');
                    abort_if(in_array($shipment->status, ['completed', 'delivered', 'returned', 'cancelled'], true), 409, 'Kiriman sudah ditutup dan tidak dapat diubah.');
                }
                $result = $operation($shipment, $fresh);
                CourierActionReceipt::create(['request_id' => $data['request_id'], 'user_id' => $actor->id,
                    'shipment_id' => $result['shipment_id'] ?? $shipment?->id, 'action' => $action,
                    'payload_hash' => $hash, 'result' => $result]);

                return ['data' => $result, 'replayed' => false];
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            abort(409, 'Resi atau request ID telah dipakai. Muat ulang data.');
        }
    }

    public function result(Shipment $shipment, User $actor, string $from, string $type, string $title, string $reason, array $metadata = []): array
    {
        $shipment->version++;
        $shipment->save();
        $workflow = app(CourierWorkflow::class);
        $event = $workflow->record($shipment, $actor, $type, $title, $from, $metadata, $reason);
        $workflow->notify($shipment, $event, $metadata['notify_courier_id'] ?? null);

        return ['shipment_id' => $shipment->id, 'awb' => $shipment->tracking_number,
            'workflow_status' => $shipment->status, 'version' => $shipment->version, 'notification_status' => 'queued'];
    }
}
