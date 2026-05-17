<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'ean',
        'description',

        'category_id',
        'series_id',

        'pack_qty',
        'stock_qty',

        'vat_rate',
        'net_unit',
        'net_pack',
        'gross_unit',
        'gross_pack',

        'height',
        'diameter',
        'width',
        'length',
        'color',
        'weight',
        'is_active',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'series_id' => 'integer',
        'pack_qty' => 'integer',
        'stock_qty' => 'integer',
        'vat_rate' => 'decimal:2',
        'net_unit' => 'decimal:2',
        'net_pack' => 'decimal:2',
        'gross_unit' => 'decimal:2',
        'gross_pack' => 'decimal:2',
        'height' => 'decimal:2',
        'diameter' => 'decimal:2',
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
