<?php

namespace App\Http\Requests\Verification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\VerifyStatus;
use App\Enums\VerifyFileType;
class VerificationRequest extends FormRequest
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
           
           
            "verify_type"=>[
                "required",
                Rule::in([VerifyFileType::IDENTITY_CARD,VerifyFileType::CENCUS,VerifyFileType::PASSPORT])
            ],
            'images' => 'required|array|max:2',
            'images.*' => 'image|mimes:jpeg,png,jpg,PNG|max:30720',
        ];
    }
}
