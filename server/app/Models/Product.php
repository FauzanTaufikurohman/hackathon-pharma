<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'min_temperature',
        'max_temperature',
        'max_excursion_seconds',
        'temperature_protocol',
        'protocol_reference',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_temperature' => 'decimal:2',
            'max_temperature' => 'decimal:2',
            'max_excursion_seconds' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}