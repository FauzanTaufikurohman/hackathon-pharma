<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['pre_trip' => 'array', 'responded_at' => 'immutable_datetime'];
    }
}
