<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'password' => ['required'],
            'password_confirmation' => ['required', 'same:password'],
            'token' => ['required', 'exists:users,password_reset_token']
        ];
    }

    public function messages()
    {
        return [
            'token.exists' => __('validation.exists'),
        ];
    }
}
