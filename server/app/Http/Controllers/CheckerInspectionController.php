<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Shipment;
use App\Models\User;
use App\OperationsWorkflow;
use App\VendorShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CheckerInspectionController extends Controller
{
    public function show(Request $request, string $awb, OperationsWorkflow $workflow, VendorShipmentService $serializer): JsonResponse
    {
        $s = $workflow->visible($request->user(), 'checker')->where('tracking_number', $awb)->with('latestApproval')->firstOrFail();

        return response()->json(['data' => $serializer->serialize($s) + ['approval' => $s->latestApproval,
            'can_submit' => in_array($s->status, ['created', 'inspection'], true),
            'ocr' => ['available' => false, 'status' => 'not_requested']]])->header('Cache-Control', 'private, no-store');
    }

    public function submit(Request $request, string $awb, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'checker');
        $data = $request->validate(['version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid'],
            'packaging' => ['required', 'boolean'], 'seal' => ['required', 'boolean'], 'label' => ['required', 'boolean'],
            'temperature' => ['required', 'numeric', 'between:-100,100'], 'note' => ['required', 'string', 'min:5', 'max:4000'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'passed' => ['prohibited'],
            'sensor_device' => ['sometimes', 'nullable', 'string', 'max:255'], 'hub_name' => ['sometimes', 'nullable', 'string', 'max:255']]);
        $paths = [];
        try {
            $result = $workflow->perform($request->user(), 'checker', $awb, 'inspect', $data, function (Shipment $s, User $actor) use ($data, $workflow, &$paths) {
                abort_unless(in_array($s->status, ['created', 'inspection'], true), 409, 'Pemeriksaan ini sudah dikirim. Admin harus meminta pemeriksaan ulang.');
                $size = getimagesize($data['photo']->path());
                abort_unless($size && $size[0] * $size[1] <= 16000000, 422, 'Foto maksimal 16 megapiksel.');
                $path = $data['photo']->store('inspections', 'local');
                abort_unless($path, 503, 'Foto belum tersimpan. Coba lagi.');
                $paths[] = $path;
                $passed = (bool) $data['packaging'] && (bool) $data['seal'] && (bool) $data['label']
                    && $s->temperature_min !== null && $s->temperature_max !== null
                    && $data['temperature'] >= $s->temperature_min && $data['temperature'] <= $s->temperature_max;
                $inspection = Inspection::create([
                    'shipment_id' => $s->id, 'user_id' => $actor->id, 'packaging' => (bool) $data['packaging'],
                    'seal' => (bool) $data['seal'], 'label' => (bool) $data['label'], 'temperature' => $data['temperature'],
                    'passed' => $passed, 'note' => $data['note'], 'sensor_device' => $data['sensor_device'] ?? null,
                    'hub_name' => $data['hub_name'] ?? null, 'photo_path' => $path, 'inspected_at' => now(),
                ]);
                $from = $s->status;
                $s->status = 'pending_approval';

                return $workflow->result($s, $actor, $from, 'inspect', 'Pemeriksaan dikirim untuk keputusan Admin', $data['note'],
                    ['inspection_id' => $inspection->id, 'passed' => $passed]) + ['inspection_id' => $inspection->id, 'passed' => $passed];
            });
            foreach ($paths as $path) {
                if (! Inspection::where('photo_path', $path)->exists()) {
                    Storage::disk('local')->delete($path);
                }
            }

            return response()->json($result);
        } catch (Throwable $exception) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function photo(Request $request, int $inspection, OperationsWorkflow $workflow): StreamedResponse
    {
        $workflow->authorize($request->user(), 'checker');
        $record = Inspection::where('user_id', $request->user()->id)->findOrFail($inspection);
        $path = $record->photo_path;
        abort_unless($path && str_starts_with($path, 'inspections/') && ! str_contains($path, '..') && ! str_contains($path, '\\')
            && Storage::disk('local')->exists($path), 404);
        abort_unless(in_array(Storage::disk('local')->mimeType($path), ['image/png', 'image/jpeg', 'image/webp'], true), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
