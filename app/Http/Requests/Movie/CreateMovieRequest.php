<?php

namespace App\Http\Requests\Movie;

use Illuminate\Foundation\Http\FormRequest;

class CreateMovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'genres_ids' => ['required', 'array'],
            'name_en' => ['required', 'string'],
            'name_ka' => ['required', 'string'],
            'director_en' => ['required', 'string'],
            'director_ka' => ['required', 'string'],
            'description_en' => ['required', 'string'],
            'description_ka' => ['required', 'string'],
            'image' => ['required', 'string']
        ];
    }
}
