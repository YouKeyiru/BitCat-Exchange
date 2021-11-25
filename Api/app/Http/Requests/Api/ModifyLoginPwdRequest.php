<?php

namespace App\Http\Requests\Api;


class ModifyLoginPwdRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_login_pwd'              => ['required', 'string', function ($attribute, $value, $fail) {
                $user = \Auth::user();
                if (!\Hash::check($value, $user->password)) {
                    $fail(trans('user.old_login_pwd_error'));
                    return;
                }
            }],
            'new_login_pwd'              => 'required|confirmed|string|min:6',
            'new_login_pwd_confirmation' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'old_login_pwd.required'  => trans('user.input_origin_login_pwd'),
            'new_login_pwd.required'  => trans('user.input_new_login_pwd'),
            'new_login_pwd.min'       => trans('user.input_new_login_pwd_bit'),
            'new_login_pwd.confirmed' => trans('user.two_pwd_different'),
        ];
    }
}
