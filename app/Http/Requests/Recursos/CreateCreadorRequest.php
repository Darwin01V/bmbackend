<?php

namespace App\Http\Requests\Recursos;

use Illuminate\Foundation\Http\FormRequest;

class CreateCreadorRequest extends FormRequest
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
            'email' => ['required','email:filter', 'unique:users,email'],
            'password' => ['required','min:8', 'string'],
            'first_name'=> ['required','string'],
            'last_name'=> ['required','string'],
            'country'=> ['required'],
            'experience'=> ['required'],
            'working'=>['required'],
            'details'=>['nullable'],
        ];
    }
}
