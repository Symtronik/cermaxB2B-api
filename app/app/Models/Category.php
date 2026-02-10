<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
    'name', 'slug', 'seo_title', 'seo_description',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];


    public function series()
    {
        return $this->hasMany(Series::class);
    }
}
