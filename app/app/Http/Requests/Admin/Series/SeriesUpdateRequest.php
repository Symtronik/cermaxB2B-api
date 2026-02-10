<?php

namespace App\Http\Requests\Admin\Series;

use Illuminate\Foundation\Http\FormRequest;

class SeriesUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // jeśli pozwalasz zmieniać kategorię serii
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],

            'name' => ['sometimes', 'string', 'max:255'],

            // slug może być pusty → controller wygeneruje
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],

            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'seo_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_description' => ['sometimes', 'nullable', 'string', 'max:180'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Wybrana kategoria nie istnieje.',

            'image.image' => 'Plik musi być obrazem.',
            'image.mimes' => 'Dozwolone formaty: JPG, JPEG, PNG, WEBP.',
            'image.max' => 'Maksymalny rozmiar obrazu to 4MB.',

            'seo_description.max' => 'Opis SEO nie może przekraczać 180 znaków.',
        ];
    }
}
