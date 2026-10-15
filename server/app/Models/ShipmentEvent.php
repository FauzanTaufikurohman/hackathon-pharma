<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class ShipmentEvent extends Model
{
    protected $table = 'events';

    protected $guarded = ['id'];

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit event tidak boleh diubah.'));
        static::deleting(fn () => throw new LogicException('Audit event tidak boleh dihapus.'));
    }
}
