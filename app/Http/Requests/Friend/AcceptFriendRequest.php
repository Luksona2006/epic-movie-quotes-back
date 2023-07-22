<?php

namespace App\Http\Requests\Friend;

use Illuminate\Foundation\Http\FormRequest;

class AcceptFriendRequest extends FormRequest
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
            'from_user' => ['exists:users,id']
        ];
    }

    public function messages()
    {
        return [
            'from_user.exists' => __('validation.exists'),
        ];
    }
}
