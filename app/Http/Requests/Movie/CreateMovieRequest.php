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
            'name_en' => ['required'],
            'name_ka' => ['required'],
            'year' => ['nullable'],
            'director_en' => ['required'],
            'director_ka' => ['required'],
            'description_en' => ['required'],
            'description_ka' => ['required'],
            'image' => ['required']
        ];
    }

    public function messages()
    {
        return [
            'genres_ids.required' => __('validation.required'),
            'genres_ids.array' => __('validation.array'),
        ];
    }
}
