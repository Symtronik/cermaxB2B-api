<?php

namespace App\Http\Requests\Admin\Series;

use Illuminate\Foundation\Http\FormRequest;

class SeriesStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // jeśli masz polityki → zmień
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            'name' => ['required', 'string', 'max:255'],

            // slug opcjonalny – wygenerujesz go w controllerze
            'slug' => ['nullable', 'string', 'max:255'],

            // jedno zdjęcie serii
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:180'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategoria jest wymagana.',
            'category_id.exists' => 'Wybrana kategoria nie istnieje.',

            'name.required' => 'Nazwa serii jest wymagana.',

            'image.image' => 'Plik musi być obrazem.',
            'image.mimes' => 'Dozwolone formaty: JPG, JPEG, PNG, WEBP.',
            'image.max' => 'Maksymalny rozmiar obrazu to 4MB.',

            'seo_description.max' => 'Opis SEO nie może przekraczać 180 znaków.',
        ];
    }
}
