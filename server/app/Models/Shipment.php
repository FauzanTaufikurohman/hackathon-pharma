<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number',
        'customer_id',
        'origin_customer_user_id',
        'destination_customer_user_id',
        'product_id',
        'quantity',
        'origin_address',
        'destination_address',
        'checker_id',
        'courier_id',
        'status',
        'notes',
        'scheduled_at',
        'started_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function originCustomerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'origin_customer_user_id');
    }

    public function destinationCustomerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destination_customer_user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
    public function thermalChecks(): HasMany
    {
        return $this->hasMany(ThermalCheck::class);
    }

    public function thermalIncidents(): HasMany
    {
        return $this->hasMany(ThermalIncident::class);
    }

    public function trackingLocations(): HasMany
    {
        return $this->hasMany(TrackingLocation::class);
    }

    public function deliveryProofs(): HasMany
    {
        return $this->hasMany(DeliveryProof::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class);
    }
}