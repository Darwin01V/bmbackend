<?php

namespace App\Http\Requests\Ventas;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlanRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'time' => ['required', 'integer'],
            'type' => ['required', 'string'],
            'price' => ['required', 'decimal:0,10'],
            'discount_percentage' => ['required', 'integer'],
            'unlimited' => ['required', 'boolean'],
            'n_audios' => ['required', 'integer'],
            'n_videos' => ['required', 'integer'] ,
            'active' => ['required', 'boolean'],
            'enlace' => ['required', 'string'],
        ];
    }
}
