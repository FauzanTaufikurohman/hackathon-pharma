<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetrySample extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['temperature' => 'float', 'latitude' => 'float', 'longitude' => 'float',
            'speed_kmh' => 'float', 'is_simulated' => 'boolean', 'recorded_at' => 'immutable_datetime'];
    }
}
