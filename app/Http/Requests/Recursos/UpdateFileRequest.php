<?php

namespace App\Http\Requests\Recursos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFileRequest extends FormRequest
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
            "name" => ["nullable", "string", "max:255"],
            "bpm" => ["nullable", "integer", "max:255"],
            "image" => ['nullable', 'image', 'max:100000'],
            "slider_new" => ["nullable", "boolean", "max:255"],
            "artists_id"=> ["nullable", "integer", "max:255"],
            "genres_id" => ["nullable", "integer", "max:255"],
        ];
    }
}
