<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'whatsapp_number',
        'whatsapp_opt_in',
        'company_type',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'whatsapp_opt_in' => 'boolean',
        ];
    }
}