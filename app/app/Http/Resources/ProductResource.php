<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'sku' => $this->sku,
            'ean' => $this->ean,
            'description' => $this->description,

            'category_id' => $this->category_id,
            'series_id' => $this->series_id,

            'pack_qty' => $this->pack_qty,
            'stock_qty' => $this->stock_qty,

            'vat_rate' => $this->vat_rate,
            'net_unit' => $this->net_unit,
            'net_pack' => $this->net_pack,
            'gross_unit' => $this->gross_unit,
            'gross_pack' => $this->gross_pack,

            'height' => $this->height,
            'diameter' => $this->diameter,
            'width' => $this->width,
            'length' => $this->length,
            'color' => $this->color,
            'weight' => $this->weight,

            'is_active' => (bool) $this->is_active,

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                    'slug' => $this->category?->slug,
                ];
            }),

            'series' => $this->whenLoaded('series', function () {
                return [
                    'id' => $this->series?->id,
                    'name' => $this->series?->name,
                    'slug' => $this->series?->slug,
                ];
            }),

            'attributes' => $this->whenLoaded('attributes', function () {
                return $this->attributes->map(function ($attribute) {
                    return [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                        'slug' => $attribute->slug,
                        'type' => $attribute->type,
                        'value' => $attribute->pivot?->value,
                    ];
                })->values();
            }),

            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->path,
                        'url' => asset('storage/' . ltrim($image->path, '/')),
                        'alt' => $image->alt,
                        'sort_order' => $image->sort_order,
                        'is_main' => (bool) $image->is_main,
                    ];
                })->values();
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
