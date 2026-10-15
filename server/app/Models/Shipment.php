<?php

namespace App\Models;

use Database\Factories\ShipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shipment extends Model
{
    /** @use HasFactory<ShipmentFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime',
            'temperature_min' => 'float', 'temperature_max' => 'float', 'weight_kg' => 'float',
            'recipient_ready' => 'boolean', 'is_demo' => 'boolean', 'version' => 'integer',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(Inspection::class)->latestOfMany();
    }

    public function latestTelemetry(): HasOne
    {
        return $this->hasOne(TelemetrySample::class)->ofMany(['recorded_at' => 'max', 'id' => 'max']);
    }

    public function telemetry(): HasMany
    {
        return $this->hasMany(TelemetrySample::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ShipmentIssue::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ShipmentReport::class);
    }

    public function latestAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)->latestOfMany();
    }

    public function latestApproval(): HasOne
    {
        return $this->hasOne(Approval::class)->latestOfMany();
    }

    public function deliveryProof(): HasOne
    {
        return $this->hasOne(DeliveryProof::class);
    }
}
