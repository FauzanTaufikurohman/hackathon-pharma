<?php

namespace App\Http\Controllers;

use App\CourierShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierDashboardController extends Controller
{
    public function show(Request $request, CourierShipmentService $service): JsonResponse
    {
        $owned = $service->owned($request->user());
        $today = now()->timezone('Asia/Jakarta')->startOfDay()->utc();
        $priority = (clone $owned)->whereNotIn('status', ['completed', 'delivered', 'returned'])
            ->orderByRaw("CASE WHEN status IN ('held', 'deviation_hold') THEN 0 WHEN status = 'assigned' THEN 1 ELSE 2 END")
            ->orderByRaw('CASE WHEN scheduled_at IS NULL THEN 1 ELSE 0 END')->orderBy('scheduled_at')->orderBy('id')->first();

        return response()->json(['data' => [
            'profile' => $service->profile($request->user()),
            'metrics' => [
                'new_tasks_count' => (clone $owned)->where('status', 'assigned')->count(),
                'on_route_count' => (clone $owned)->whereIn('status', ['in_transit', 'arrived', 'returning'])->count(),
                'issues_hold_count' => (clone $owned)->whereIn('status', ['held', 'deviation_hold'])->count(),
                'completed_today_count' => (clone $owned)->whereIn('status', ['completed', 'delivered', 'returned'])
                    ->where('completed_at', '>=', $today)->where('completed_at', '<', $today->copy()->addDay())->count(),
                'ready_to_depart_count' => (clone $owned)->where('status', 'accepted')->count(),
            ],
            'counts' => $service->counts($request->user()),
            'priority_task' => $priority ? $service->task($priority) : null, 'refreshed_at' => now()->toISOString(),
        ]])->header('Cache-Control', 'private, no-store');
    }
}
