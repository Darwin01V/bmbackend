<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'user' => ['required', 'array'],
            'user.email' => ['required','email:filter', 'unique:users,email'],
            'user.password' => ['required','min:8', 'string'],
            'user.root' => ['sometimes', 'boolean'],
            'user.active' => ['sometimes', 'boolean'],

            'perfil' => ['required', 'array'],
            'perfil.path_profile' => ['nullable', 'string'],
            'perfil.first_name' => ['required', 'string'],
            'perfil.last_name' => ['required', 'string'],

            'roles.id' => ['required', 'integer']
        ];
    }
}
