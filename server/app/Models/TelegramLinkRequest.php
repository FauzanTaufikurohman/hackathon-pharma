<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramLinkRequest extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['user_id', 'token_hash', 'expires_at', 'consumed_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}
