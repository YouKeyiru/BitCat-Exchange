<?php

namespace App\Http\Requests\Api;


class ResetLoginPwdRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username'                   => 'required|string',
            'v_code'                     => 'required|string|min:6',
            'new_login_pwd'              => 'required|confirmed|string|min:6',
            'new_login_pwd_confirmation' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'username.required'       => trans('user.input_username'),
            'v_code.required'         => trans('common.code_error'),
            'new_login_pwd.required'  => trans('user.input_new_login_pwd'),
            'new_login_pwd.min'       => trans('user.input_new_login_pwd_bit'),
            'new_login_pwd.confirmed' => trans('user.two_pwd_different'),
        ];
    }
}
