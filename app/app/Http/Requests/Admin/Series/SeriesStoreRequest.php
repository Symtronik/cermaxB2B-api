<?php

namespace App\Http\Requests\Admin\Series;

use Illuminate\Foundation\Http\FormRequest;

class SeriesStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ✅ wiele kategorii (obowiązkowe)
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['required', 'integer', 'exists:categories,id'],

            'name' => ['required', 'string', 'max:255'],

            // slug opcjonalny – wygenerujesz go w controllerze
            'slug' => ['nullable', 'string', 'max:255'],

            'is_active' => ['nullable', 'boolean'],

            // jedno zdjęcie serii
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:180'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }

        // ✅ gdy przyjdzie category_ids jako string (np. "1,2,3") albo pojedyncza wartość
        // nie zawsze potrzebne, ale ratuje gdy frontend/klient wyśle inaczej niż FormData category_ids[]
        if ($this->has('category_ids') && !is_array($this->input('category_ids'))) {
            $raw = $this->input('category_ids');

            $this->merge([
                'category_ids' => is_string($raw)
                    ? array_values(array_filter(array_map('trim', explode(',', $raw))))
                    : [$raw],
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'category_ids.required' => 'Musisz wybrać przynajmniej jedną kategorię.',
            'category_ids.array' => 'Kategorie mają nieprawidłowy format.',
            'category_ids.min' => 'Musisz wybrać przynajmniej jedną kategorię.',
            'category_ids.*.required' => 'Kategoria jest wymagana.',
            'category_ids.*.integer' => 'Kategoria ma nieprawidłowy format.',
            'category_ids.*.exists' => 'Wybrana kategoria nie istnieje.',

            'name.required' => 'Nazwa serii jest wymagana.',
            'name.string' => 'Nazwa serii ma nieprawidłowy format.',
            'name.max' => 'Nazwa serii nie może przekraczać 255 znaków.',

            'slug.string' => 'Slug ma nieprawidłowy format.',
            'slug.max' => 'Slug nie może przekraczać 255 znaków.',

            'is_active.boolean' => 'Status aktywności ma nieprawidłową wartość.',

            'image.file' => 'Plik ma nieprawidłowy format.',
            'image.image' => 'Plik musi być obrazem.',
            'image.mimes' => 'Dozwolone formaty: JPG, JPEG, PNG, WEBP.',
            'image.max' => 'Maksymalny rozmiar obrazu to 4MB.',

            'seo_title.max' => 'Tytuł SEO nie może przekraczać 70 znaków.',
            'seo_description.max' => 'Opis SEO nie może przekraczać 180 znaków.',
        ];
    }
}
