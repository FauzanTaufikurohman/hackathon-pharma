<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentReport extends Model
{
    protected $guarded = ['id'];

    public static function hashSnapshot(array $snapshot): string
    {
        return hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function snapshotHash(): string
    {
        return self::hashSnapshot($this->snapshot);
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Laporan terbit tidak boleh diubah; buat revisi baru.'));
        static::deleting(fn () => throw new \LogicException('Laporan terbit tidak boleh dihapus.'));
    }

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'is_demo' => 'boolean', 'published_at' => 'immutable_datetime'];
    }
}
