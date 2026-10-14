<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryProof extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['photo_path', 'signature_path'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Bukti serah terima tidak boleh diubah.'));
        static::deleting(fn () => throw new \LogicException('Bukti serah terima tidak boleh dihapus.'));
    }

    protected function casts(): array
    {
        return ['temperature' => 'float', 'seal_intact' => 'boolean', 'latitude' => 'float',
            'longitude' => 'float', 'handed_over_at' => 'immutable_datetime'];
    }
}
