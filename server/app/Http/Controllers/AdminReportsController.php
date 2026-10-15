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
use App\ReportDistribution;
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

    public function reportDistribution(Request $request, int $report, OperationsWorkflow $workflow, ReportDistribution $distribution): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $record = ShipmentReport::findOrFail($report);
        $distribution->queue($record);

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
        $data = $request->validate(['page' => ['sometimes', 'integer', 'between:1,100000']]);
        $messages = Message::latest('id')->paginate(30, ['*'], 'page', $data['page'] ?? 1);
        $statuses = Message::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        return response()->json([
            'gateway' => [
                'provider' => 'telegram',
                'configured' => (bool) config('services.telegram.bot_token'),
                'enabled' => (bool) config('services.telegram.enabled'),
                'active_recipients' => User::where('is_active', true)->whereIn('role', ['admin', 'buyer', 'vendor', 'courier'])
                    ->whereNotNull('telegram_chat_id')->count(),
                'pending_messages' => (int) ($statuses['pending'] ?? 0) + (int) ($statuses['retry'] ?? 0),
                'message_statuses' => $statuses,
                'scheduler_command' => 'php artisan schedule:work',
            ],
            'messages' => $messages,
        ])->header('Cache-Control', 'private, no-store');
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
        $data = $workflow->validateAction($request, ['type' => ['required', Rule::in(['temperature', 'signal_loss', 'seal', 'correct_temperature'])],
            'temperature' => ['required_if:type,temperature', 'numeric', 'between:-100,100']]);

        return response()->json($workflow->perform($request->user(), 'admin', $awb, 'simulate', $data, function (Shipment $s, User $actor) use ($data, $engine, $workflow) {
            abort_unless($s->is_demo && in_array($s->status, ['in_transit', 'arrived', 'deviation_hold'], true), 409, 'Simulator hanya untuk kiriman demo yang sedang diantar atau ditahan karena penyimpangan.');
            $from = $s->status;
            if ($data['type'] === 'correct_temperature') {
                abort_unless($s->status === 'deviation_hold'
                    && $s->temperature_min !== null && $s->temperature_max !== null
                    && $s->issues()->where('type', 'temperature')->where('status', 'open')->exists(), 409,
                    'Koreksi termal hanya tersedia untuk penyimpangan suhu demo yang masih terbuka.');
                $current = $s->latestTelemetry?->temperature;
                abort_unless($current !== null, 409, 'Sampel suhu terakhir tidak tersedia untuk memulai koreksi.');
                $target = round(($s->temperature_min + $s->temperature_max) / 2, 1);
                $starting = (float) $current;
                for ($step = 1; $step <= 3; $step++) {
                    $corrected = round($starting + (($target - $starting) * $step / 3), 2);
                    $engine->append($s, ['temperature' => $corrected, 'latitude' => -6.2, 'longitude' => 106.8,
                        'sensor_id' => 'AI-SIMULATOR', 'recorded_at' => now()->addSeconds($step)->toISOString()]);
                }

                return $workflow->result($s, $actor, $from, 'simulation_correction', 'Koreksi termal simulasi tercatat',
                    'Simulasi mencatat tiga sampel stabil pada titik tengah rentang aman. Kiriman tetap ditahan sampai Admin meninjau dan memutuskan.',
                    ['is_simulated' => true, 'ai_correction' => true, 'target_temperature_c' => $target, 'samples_written' => 3]);
            }
            if ($data['type'] === 'temperature') {
                abort_unless(in_array($s->status, ['in_transit', 'arrived'], true), 409, 'Skenario suhu hanya dapat dimulai saat demo dalam perjalanan.');
                foreach ([4, 2, 0] as $minutes) {
                    $engine->append($s, ['temperature' => $data['temperature'], 'latitude' => -6.2, 'longitude' => 106.8,
                        'sensor_id' => 'SIMULATOR',
                        'recorded_at' => now()->subMinutes($minutes)->toISOString()]);
                }
            } else {
                abort_unless(in_array($s->status, ['in_transit', 'arrived'], true), 409, 'Skenario penyimpangan hanya dapat dimulai saat demo dalam perjalanan.');
                $engine->hold($s, $data['type'], 'Skenario demo: '.$data['type'], $s->latestTelemetry);
            }

            return $workflow->result($s, $actor, $from, 'simulation', 'Skenario demo dijalankan', 'Data simulasi untuk pengujian; bukan pembacaan sensor nyata.', ['is_simulated' => true]);
        }));
    }
}
