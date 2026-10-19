<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThermalCheck extends Model
{
    use HasFactory;

    protected $hidden = ['ai_debug'];

    protected $fillable = [
        'shipment_id',
        'checker_id',
        'temperature_celsius',
        'packaging_condition',
        'physical_condition',
        'notes',
        'photo_path',
        'checked_at',
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
            'checked_at' => 'datetime',
            'ai_assessment' => 'array',
            'ai_assessed_at' => 'datetime',
            'ai_debug' => 'array',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}