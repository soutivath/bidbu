<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            "user_id"=>"required|integer|exists:users,id",
            "score"=>"required|integer|min:1|max:5",
            "comment"=>"required|string|max:255",
        ];
    }
}
