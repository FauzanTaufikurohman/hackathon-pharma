<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryProof extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['photo_path', 'signature_path'];

    protected function casts(): array
    {
        return ['temperature' => 'float', 'seal_intact' => 'boolean', 'latitude' => 'float',
            'longitude' => 'float', 'handed_over_at' => 'immutable_datetime'];
    }
}
