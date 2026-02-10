<?php

namespace App\Http\Requests\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category');
        return [
              'name' => ['sometimes', 'required', 'string', 'max:255'],
      'slug' => [
        'sometimes',
        'nullable',
        'string',
        'max:255',
        Rule::unique('categories', 'slug')->ignore($categoryId),
      ],
      'image' => ['sometimes', 'nullable', 'file', 'image', 'max:4096'],
      'seo_title' => ['sometimes', 'nullable', 'string', 'max:70'],
      'seo_description' => ['sometimes', 'nullable', 'string', 'max:180'],
        ];
    }
}
