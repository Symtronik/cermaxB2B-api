<?php

namespace App\Http\Requests\Customer\Users;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyUserRequest extends FormRequest
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
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','email:rfc','max:255','unique:users,email'],
            'password'              => ['required','string','min:8','confirmed'],
            'password_confirmation' => ['required','string','min:8'],
            'roles'                 => ['sometimes','array'],
            'roles.*'               => ['string'],
        ];
    }
}
