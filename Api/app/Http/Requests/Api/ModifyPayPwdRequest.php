<?php

namespace App\Http\Requests\Api;


class ModifyPayPwdRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_pay_pwd'              => ['required', 'string', function ($attribute, $value, $fail) {
                $user = \Auth::user();
                if (!\Hash::check($value, $user->payment_password)) {
                    $fail(trans('user.old_pay_pwd_error'));
                    return;
                }
            }],
            'new_pay_pwd'              => 'required|confirmed|string|min:6',
            'new_pay_pwd_confirmation' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'old_pay_pwd.required'  => trans('user.input_origin_pay_pwd'),
            'new_pay_pwd.required'  => trans('user.input_new_pay_pwd'),
            'new_pay_pwd.min'       => trans('user.input_new_pay_pwd_bit'),
            'new_pay_pwd.confirmed' => trans('user.two_pwd_different'),
        ];
    }
}
