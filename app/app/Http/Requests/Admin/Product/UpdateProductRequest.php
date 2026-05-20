<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],

            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($productId),
            ],

            'ean' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'ean')->ignore($productId),
            ],

            'description' => ['nullable', 'string'],

            'category_id' => ['required', 'exists:categories,id'],
            'series_id' => ['required', 'exists:series,id'],

            'pack_qty' => ['nullable', 'integer', 'min:1'],
            'stock_qty' => ['nullable', 'integer', 'min:0'],

            'vat_rate' => ['nullable', 'numeric', 'min:0'],
            'net_unit' => ['nullable', 'numeric', 'min:0'],
            'net_pack' => ['nullable', 'numeric', 'min:0'],
            'gross_unit' => ['nullable', 'numeric', 'min:0'],
            'gross_pack' => ['nullable', 'numeric', 'min:0'],

            'height' => ['nullable', 'numeric', 'min:0'],
            'diameter' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],

            'color_id' => ['nullable', 'exists:colors,id'],
            'weight' => ['nullable', 'numeric', 'min:0'],

            'is_active' => ['nullable', 'boolean'],

            'attributes' => ['nullable', 'array'],

            'attributes.*.attribute_id' => [
                'required',
                'exists:attributes,id',
            ],

            'attributes.*.value' => ['nullable'],

            'images' => ['nullable', 'array'],

            'images.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'existing_images' => ['nullable', 'array'],

            'existing_images.*.id' => [
                'required',
                'exists:product_images,id',
            ],

            'existing_images.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'existing_images.*.is_main' => [
                'nullable',
                'boolean',
            ],

            'main_image_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
