<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThermalIncident extends Model
{
    use HasFactory;

    protected $hidden = ['ai_debug'];

    protected $fillable = [
        'shipment_id',
        'thermal_check_id',
        'temperature_celsius',
        'allowed_min_temperature',
        'allowed_max_temperature',
        'exposure_duration_seconds',
        'severity',
        'incident_status',
        'description',
        'corrective_action',
        'detected_at',
        'recovered_at',
        'ai_assessment',
        'ai_assessment_status',
        'ai_model',
        'ai_assessed_at',
        'ai_debug',
    ];

    protected function casts(): array
    {
        return [
            'temperature_celsius' => 'decimal:2',
            'allowed_min_temperature' => 'decimal:2',
            'allowed_max_temperature' => 'decimal:2',
            'exposure_duration_seconds' => 'integer',
            'detected_at' => 'datetime',
            'recovered_at' => 'datetime',
            'ai_assessment' => 'array',
            'ai_assessed_at' => 'datetime',
            'ai_debug' => 'array',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function thermalCheck(): BelongsTo
    {
        return $this->belongsTo(ThermalCheck::class);
    }
}