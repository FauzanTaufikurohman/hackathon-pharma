<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['attempts' => 'integer', 'claimed_at' => 'immutable_datetime',
            'next_attempt_at' => 'immutable_datetime', 'sent_at' => 'immutable_datetime'];
    }
}
