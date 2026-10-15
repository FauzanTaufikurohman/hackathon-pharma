<?php

namespace App\Http\Controllers;

use App\CourierWorkflow;
use App\Models\Approval;
use App\Models\Assignment;
use App\Models\Shipment;
use App\Models\User;
use App\OperationsWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminApprovalsController extends Controller
{
    public function approve(Request $request, string $awb, OperationsWorkflow $workflow): JsonResponse
    {
        $data = $workflow->validateAction($request, ['decision' => ['required', Rule::in(['approved', 'held'])], 'reason' => ['required', 'string', 'min:5', 'max:4000']]);

        return response()->json($workflow->perform($request->user(), 'admin', $awb, 'approve', $data, function (Shipment $s, User $actor) use ($data, $workflow) {
            abort_unless($s->status === 'pending_approval' && $s->latestInspection, 409, 'Pemeriksaan belum menunggu persetujuan.');
            $inspection = $s->latestInspection;
            if ($data['decision'] === 'approved') {
                abort_unless($inspection->passed && $inspection->packaging && $inspection->seal && $inspection->label
                    && $inspection->temperature !== null && $s->temperature_min !== null && $s->temperature_max !== null
                    && $inspection->temperature >= $s->temperature_min && $inspection->temperature <= $s->temperature_max, 409, 'Pemeriksaan tidak aman; gunakan keputusan tahan.');
                abort_unless($inspection->photo_path && Storage::disk('local')->exists($inspection->photo_path), 409, 'Foto pemeriksaan belum tersedia.');
            }
            Approval::create(['shipment_id' => $s->id, 'inspection_id' => $inspection->id, 'user_id' => $actor->id,
                'decision' => $data['decision'], 'reason' => $data['reason']]);
            $s->status = $data['decision'] === 'approved' ? 'approved' : 'held';

            return $workflow->result($s, $actor, 'pending_approval', 'approve', 'Keputusan pemeriksaan dicatat', $data['reason']);
        }));
    }

    public function assign(Request $request, string $awb, OperationsWorkflow $workflow): JsonResponse
    {
        $data = $workflow->validateAction($request, ['courier_id' => ['required', 'integer'], 'reason' => ['required', 'string', 'min:5', 'max:4000']]);

        return response()->json($workflow->perform($request->user(), 'admin', $awb, 'assign', $data, function (Shipment $s, User $actor) use ($data, $workflow) {
            abort_unless($s->status === 'approved' && ! $s->issues()->where('status', 'open')->exists(), 409, 'Kiriman belum siap ditugaskan.');
            app(CourierWorkflow::class)->approved($s);
            $courier = User::whereKey($data['courier_id'])->where('role', 'courier')->where('is_active', true)->lockForUpdate()->firstOrFail();
            $capacity = (float) ($courier->courier_profile['capacity_kg'] ?? 0);
            $load = Shipment::where('courier_id', $courier->id)->whereNotIn('status', ['completed', 'delivered', 'returned', 'cancelled'])->sum('weight_kg');
            abort_unless($capacity > 0 && $load + $s->weight_kg <= $capacity, 409, 'Kapasitas kurir tidak cukup atau belum dikonfigurasi.');
            Assignment::create(['shipment_id' => $s->id, 'courier_id' => $courier->id, 'assigned_by' => $actor->id, 'status' => 'pending']);
            $s->courier_id = $courier->id;
            $s->status = 'assigned';

            return $workflow->result($s, $actor, 'approved', 'assign', 'Penugasan menunggu jawaban kurir', $data['reason']);
        }));
    }

    public function recheck(Request $request, string $awb, OperationsWorkflow $workflow): JsonResponse
    {
        $data = $workflow->validateAction($request, ['reason' => ['required', 'string', 'min:5', 'max:4000']]);

        return response()->json($workflow->perform($request->user(), 'admin', $awb, 'recheck', $data, function (Shipment $s, User $actor) use ($data, $workflow) {
            abort_unless($s->status === 'held' && ! $s->issues()->where('status', 'open')->exists(), 409, 'Pemeriksaan ulang hanya untuk hold pemeriksaan awal.');
            $s->status = 'inspection';

            return $workflow->result($s, $actor, 'held', 'recheck', 'Checker diminta memeriksa ulang', $data['reason']);
        }));
    }
}
