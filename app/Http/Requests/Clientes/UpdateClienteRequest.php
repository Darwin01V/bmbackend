<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateClienteRequest extends FormRequest
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
            'email' => [
                'nullable',
                'email:filter',
                Rule::unique('users', 'email')->ignore($this->route('id')) // Ignora el usuario actual
            ],
            'password' => ['nullable', 'min:6','string'],
            'name' => ['nullable', 'string'],
            'lastname' => ['nullable', 'string'],
        ];
    }
}
