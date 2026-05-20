<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'cart_id',
        'shipping_point_id',
        'number',
        'status',
        'net_total',
        'gross_total',
        'customer_note',
    ];

    protected $casts = [
        'net_total' => 'decimal:2',
        'gross_total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function shippingPoint()
    {
        return $this->belongsTo(ShippingPoint::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
