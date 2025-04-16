<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
                'sometimes',
                'nullable',
                'email:filter',
                Rule::unique('users', 'email')->ignore(Auth::id()) 
            ],
            'password' => ['sometimes','nullable', 'min:6','string'],
            'name' => ['sometimes','nullable', 'string'],
            'lastname' => ['sometimes','nullable', 'string'],
        ];
    }
}
