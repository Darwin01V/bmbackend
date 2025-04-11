<?php

namespace App\Http\Requests\Recursos;

use Illuminate\Foundation\Http\FormRequest;

class CreateFileRequest extends FormRequest
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
            // Validación para cada elemento del array
            'files' => ['required','array'],
            'files.*.name' => ['required', 'string'],
            'files.*.bpm' => ['required', 'string'],
            'files.*.file' => ['required', 'file', 'max:100000'],
            'files.*.preview' => ['required', 'file', 'max:100000'],
            'files.*.version' => ['required', 'integer'],
            'files.*.type' => ['required', 'string'],
            'files.*.slider_new' => ['required', 'string'],
            'files.*.artists_id' => ['required', 'integer'],
            'files.*.genres_id' => ['required', 'integer'],
        ];
    }
}
