<?php

namespace App\Http\Requests\Customer\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpsertCompanyRequest extends FormRequest
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
        return [
            'company_name'  => ['required','string','max:255'],
            'vat_id'        => ['required','string','max:50', 'unique:company_profiles,vat_id'],
            'regon'         => ['nullable','string','max:50'],
            'address_line1' => ['required','string','max:255'],
            'address_line2' => ['nullable','string','max:255'],
            'postal_code'   => ['required','string','max:20'],
            'city'          => ['required','string','max:120'],
            'country'       => ['required','string','size:2'],
            'phone'         => ['required','string','max:50'],
            'description'   => ['required','string','max:255'],
        ];
    }
}
