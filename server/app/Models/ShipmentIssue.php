<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShipmentIssue extends Model
{
    protected $table = 'issues';

    protected $guarded = ['id'];

    protected $hidden = ['photo_path'];

    public function latestDecision(): HasOne
    {
        return $this->hasOne(Decision::class, 'issue_id')->latestOfMany();
    }
}
