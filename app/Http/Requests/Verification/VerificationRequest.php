<?php

namespace App\Http\Requests\Verification;

use App\Enums\GenderEnum;
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
            "verify_name"=>"required|string|max:255",
            "verify_surname"=>"required|string|max:255",
            "verify_phone_number"=>"required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10",
            "verify_gender"=>["required",Rule::in([GenderEnum::FEMALE,GenderEnum::MALE])],
            "verify_date_of_birth"=>"required|date"
        ];
    }
}
