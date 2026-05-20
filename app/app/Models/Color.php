<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Color extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'hex',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($color) {
            if (empty($color->slug)) {
                $color->slug = Str::slug($color->name);
            }
        });

        static::updating(function ($color) {
            if (empty($color->slug)) {
                $color->slug = Str::slug($color->name);
            }
        });
    }
}
