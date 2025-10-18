<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAdminRequest extends FormRequest
{
   
    public function authorize(): bool
    {

        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'password_confirmation' => ['required','string','min:8','max:255'],
            'roles'                 => ['sometimes','array'],
            'roles.*'               => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Hasło i potwierdzenie muszą być takie same.',
        ];
    }
}
