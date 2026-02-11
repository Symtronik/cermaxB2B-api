<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Route: vats/{vat} -> route('vat') zwraca model Vat lub ID (zależnie od bindowania)
        $vat = $this->route('vat');
        $vatId = is_object($vat) ? $vat->id : (int) $vat;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:190',
                Rule::unique('vats', 'name')->ignore($vatId),
            ],
            'rate' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
                'max:99.99',
            ],
            'code' => [
                'nullable',
                'string',
                'max:190',
                Rule::unique('vats', 'code')->ignore($vatId),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
