<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierActionReceipt extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['result' => 'array'];
    }
}
