<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentReport extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'is_demo' => 'boolean', 'published_at' => 'immutable_datetime'];
    }
}
