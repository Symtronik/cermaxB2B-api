<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'sku',
        'ean',
        'quantity',
        'pack_qty',
        'pieces_total',
        'net_pack',
        'gross_pack',
        'net_total',
        'gross_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'pack_qty' => 'integer',
        'pieces_total' => 'integer',
        'net_pack' => 'decimal:2',
        'gross_pack' => 'decimal:2',
        'net_total' => 'decimal:2',
        'gross_total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
