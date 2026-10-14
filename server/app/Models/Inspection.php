<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['photo_path'];

    protected function casts(): array
    {
        return ['packaging' => 'boolean', 'seal' => 'boolean', 'label' => 'boolean',
            'passed' => 'boolean', 'temperature' => 'float', 'inspected_at' => 'immutable_datetime'];
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
