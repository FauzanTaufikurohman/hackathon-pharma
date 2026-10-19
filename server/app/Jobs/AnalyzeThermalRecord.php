<?php

namespace App\Jobs;

use App\Models\ThermalCheck;
use App\Models\ThermalIncident;
use App\Services\ThermalAssessmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AnalyzeThermalRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 75;

    public function __construct(
        public string $recordType,
        public int $recordId,
    ) {
        if (! in_array($recordType, ['thermal_check', 'thermal_incident'], true)) {
            throw new RuntimeException('Unsupported thermal record type.');
        }

        $this->onQueue('ai');
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(ThermalAssessmentService $assessmentService): void
    {
        $record = $this->record();
        $record->forceFill([
            'ai_assessment_status' => 'processing',
            'ai_debug' => null,
        ])->save();

        try {
            $assessment = $this->recordType === 'thermal_check'
                ? $assessmentService->assessCheck($record)
                : $assessmentService->assessIncident($record);
        } catch (Throwable $exception) {
            $record->forceFill([
                'ai_debug' => $assessmentService->debugDetails(),
            ])->save();

            throw $exception;
        }

        $record->forceFill([
            'ai_assessment' => $assessment,
            'ai_assessment_status' => 'completed',
            'ai_model' => config('services.openai.model', 'gpt-4.1'),
            'ai_assessed_at' => now(),
            'ai_debug' => $assessmentService->debugDetails(),
        ])->save();
    }

    public function failed(?Throwable $exception): void
    {
        $record = $this->recordType === 'thermal_check'
            ? ThermalCheck::find($this->recordId)
            : ThermalIncident::find($this->recordId);

        $record?->forceFill(['ai_assessment_status' => 'failed'])->save();

        Log::error('Thermal AI assessment job failed.', [
            'record_type' => $this->recordType,
            'record_id' => $this->recordId,
            'error' => $exception?->getMessage(),
        ]);
    }

    private function record(): ThermalCheck|ThermalIncident
    {
        return $this->recordType === 'thermal_check'
            ? ThermalCheck::findOrFail($this->recordId)
            : ThermalIncident::findOrFail($this->recordId);
    }
}
