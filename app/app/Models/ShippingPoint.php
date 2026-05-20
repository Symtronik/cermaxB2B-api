<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingPoint extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'contact_name',
        'phone',
        'street',
        'building_number',
        'apartment_number',
        'postal_code',
        'city',
        'country',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }
}
