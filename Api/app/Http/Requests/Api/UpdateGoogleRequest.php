<?php

namespace App\Http\Requests\Api;

class UpdateGoogleRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//                'code'          => 'required',
            'google_code'   => ['required', 'size:6'],
            'google_secret' => 'required',
        ];
    }

    public function messages()
    {
        return [
//                'code.required'          => '短信验证码必须',
            'google_code.required'   => trans('common.input_google_verification'),
            'google_code.size'       => trans('common.google_verification_code_error'),
            'google_secret.required' => trans('common.input_google_secret'),
        ];
    }
}
