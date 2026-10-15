<?php

namespace App\Http\Controllers;

use App\CourierReportService;
use App\CourierTelemetryService;
use App\Models\Message;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ShipmentIssue;
use App\Models\ShipmentReport;
use App\Models\User;
use App\OperationsWorkflow;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminReportsController extends Controller
{
    public function daily(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $data = $request->validate(['date' => ['sometimes', 'date_format:Y-m-d']]);
        $start = CarbonImmutable::parse(($data['date'] ?? now()->timezone('Asia/Jakarta')->toDateString()).' 00:00:00', 'Asia/Jakarta')->utc();
        $end = $start->addDay();
        $totals = Shipment::where('created_at', '>=', $start)->where('created_at', '<', $end)
            ->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        return response()->json(['data' => ['date' => $start->timezone('Asia/Jakarta')->toDateString(), 'timezone' => 'Asia/Jakarta',
            'created_shipments' => (int) $totals->sum(), 'statuses_of_created_shipments' => $totals,
            'closed_shipments' => Shipment::where('completed_at', '>=', $start)->where('completed_at', '<', $end)->count(),
            'published_reports' => ShipmentReport::where('published_at', '>=', $start)->where('published_at', '<', $end)->count(),
            'new_incidents' => ShipmentIssue::where('created_at', '>=', $start)->where('created_at', '<', $end)->count(),
            'regulatory_certification' => false]])->header('Cache-Control', 'private, no-store');
    }

    public function reportDistribution(Request $request, int $report, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $record = ShipmentReport::findOrFail($report);
        abort_unless(hash_equals($record->sha256, $record->snapshotHash()), 409, 'Integritas laporan tidak cocok.');
        $ids = User::where('role', 'admin')->where('is_active', true)->pluck('id');
        $s = Shipment::findOrFail($record->shipment_id);
        $ids->push($s->buyer_id)->push($s->courier_id);
        foreach ($ids->filter()->unique() as $id) {
            Message::firstOrCreate(['dedupe_key' => 'report:'.$record->id.':'.$id], [
                'user_id' => $id, 'shipment_id' => $s->id, 'subject' => 'Laporan pengiriman tersedia',
                'body' => $s->tracking_number.' | Laporan '.$record->id.' | SHA256 '.$record->sha256, 'status' => 'pending']);
        }

        return response()->json(['data' => ['report_id' => $record->id, 'status' => 'queued', 'channel' => 'telegram']], 202);
    }

    public function retryNotification(Request $request, int $message, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $data = $request->validate(['confirm_possible_duplicate' => ['sometimes', 'boolean']]);
        $result = DB::transaction(function () use ($message, $data) {
            $record = Message::whereKey($message)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($record->status, ['failed', 'unconfigured', 'uncertain'], true), 409, 'Pesan tidak membutuhkan retry.');
            abort_if($record->status === 'uncertain' && ! ($data['confirm_possible_duplicate'] ?? false), 409, 'Pengiriman belum terkonfirmasi; retry dapat membuat pesan ganda.');
            $record->update(['status' => 'pending', 'attempts' => 0, 'claim_token' => null, 'next_attempt_at' => null, 'error_code' => null]);

            return ['id' => $record->id, 'status' => 'pending'];
        });

        return response()->json(['data' => $result], 202);
    }

    public function reports(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $data = $request->validate(['awb' => ['sometimes', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'between:1,100000']]);
        $query = ShipmentReport::query()->select(['id', 'shipment_id', 'title', 'revision', 'sha256', 'is_demo', 'published_at']);
        if (isset($data['awb'])) {
            $query->whereIn('shipment_id', Shipment::where('tracking_number', $data['awb'])->select('id'));
        }

        return response()->json($query->latest('id')->paginate(30))->header('Cache-Control', 'private, no-store');
    }

    public function downloadReport(Request $request, int $report, OperationsWorkflow $workflow, CourierReportService $service): Response
    {
        $workflow->authorize($request->user(), 'admin');

        return $service->download(ShipmentReport::findOrFail($report));
    }

    public function events(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $data = $request->validate(['awb' => ['sometimes', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'between:1,100000']]);
        $query = ShipmentEvent::query();
        if (isset($data['awb'])) {
            $query->whereIn('shipment_id', Shipment::where('tracking_number', $data['awb'])->select('id'));
        }

        return response()->json($query->latest('id')->paginate(50))->header('Cache-Control', 'private, no-store');
    }

    public function notifications(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $request->validate(['page' => ['sometimes', 'integer', 'between:1,100000']]);

        return response()->json(['gateway' => ['provider' => 'telegram', 'configured' => (bool) config('services.telegram.bot_token'),
            'enabled' => (bool) config('services.telegram.enabled')], 'messages' => Message::latest('id')->paginate(30)]);
    }

    public function telegramRecipient(Request $request, int $user, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $data = $request->validate(['chat_id' => ['required', 'string', 'regex:/^-?[0-9]{1,20}$/']]);
        $record = User::findOrFail($user);
        $record->telegram_chat_id = $data['chat_id'];
        $record->save();

        return response()->json(['data' => ['user_id' => $record->id, 'configured' => true]]);
    }

    public function simulate(Request $request, string $awb, OperationsWorkflow $workflow, CourierTelemetryService $engine): JsonResponse
    {
        abort_unless(app()->environment(['local', 'testing']) || config('services.pharma.simulator_enabled'), 404);
        $data = $workflow->validateAction($request, ['type' => ['required', Rule::in(['temperature', 'signal_loss', 'seal'])],
            'temperature' => ['required_if:type,temperature', 'numeric', 'between:-100,100']]);

        return response()->json($workflow->perform($request->user(), 'admin', $awb, 'simulate', $data, function (Shipment $s, User $actor) use ($data, $engine, $workflow) {
            abort_unless($s->is_demo && in_array($s->status, ['in_transit', 'arrived'], true), 409, 'Simulator hanya untuk kiriman demo yang sedang diantar.');
            $from = $s->status;
            if ($data['type'] === 'temperature') {
                foreach ([4, 2, 0] as $minutes) {
                    $engine->append($s, ['temperature' => $data['temperature'], 'latitude' => -6.2, 'longitude' => 106.8,
                        'recorded_at' => now()->subMinutes($minutes)->toISOString()]);
                }
            } else {
                $engine->hold($s, $data['type'], 'Skenario demo: '.$data['type'], $s->latestTelemetry);
            }

            return $workflow->result($s, $actor, $from, 'simulation', 'Skenario demo dijalankan', 'Data simulasi; tidak dikirim ke Telegram.', ['is_simulated' => true]);
        }));
    }
}
