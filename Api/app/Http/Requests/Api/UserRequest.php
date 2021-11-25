<?php

namespace App\Http\Requests\Api;


class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'area_code'             => 'required',
            'v_code'                => 'required|string|min:6',
            'username'              => 'required|string',
//            'password'              => 'required|confirmed|min:8|max:20',
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required',
            // 'invite_code'           => 'required',
        ];
    }

    public function messages()
    {
        return [
            'v_code.required'      => trans('common.code_error'),
            'username.required'    => trans('user.input_username'),
            'password.required'    => trans('user.input_login_pwd'),
            'password.min'         => trans('user.input_pwd_bit'),
            'password.confirmed'   => trans('user.two_pwd_different'),
            // 'invite_code.required' => trans('user.input_invite_code'),
        ];
    }
}
