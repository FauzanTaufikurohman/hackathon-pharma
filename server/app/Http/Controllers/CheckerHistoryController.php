<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Shipment;
use App\OperationsWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckerHistoryController extends Controller
{
    public function history(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'checker');
        $data = $request->validate(['awb' => ['sometimes', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'between:1,100000'],
            'per_page' => ['sometimes', 'integer', 'between:1,100']]);
        $query = Inspection::where('user_id', $request->user()->id)->with('shipment.latestApproval');
        if (isset($data['awb'])) {
            $query->whereIn('shipment_id', Shipment::where('tracking_number', $data['awb'])->select('id'));
        }
        $page = $query->latest('id')->paginate($data['per_page'] ?? 20);

        return response()->json(['data' => $page->getCollection()->map(function ($inspection) {
            $s = $inspection->shipment;
            $approval = $s->latestApproval;

            return $inspection->only(['id', 'shipment_id', 'packaging', 'seal', 'label', 'passed', 'temperature', 'note'])
                + ['awb' => $s->tracking_number, 'submitted_at' => $inspection->inspected_at->toISOString(),
                    'workflow_status' => $s->status, 'approval' => $approval && $approval->inspection_id === $inspection->id ? $approval : null,
                    'photo_url' => route('checker.inspection.photo', ['inspection' => $inspection->id])];
        }), 'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()]])->header('Cache-Control', 'private, no-store');
    }
}
