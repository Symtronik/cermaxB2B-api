<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image_path',
        'seo_title',
        'seo_description',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }
}
