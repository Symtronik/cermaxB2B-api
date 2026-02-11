<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class vat extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'code',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];
}
