<?php

namespace App\Http\Requests\Api;

class ApplyWithdraw extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'wid'              => 'required',
            'amount'           => 'required|gt:0',
            'address'          => 'required',
            'payment_password' => 'required',
            'v_code'           => 'required',
            // 'google_code'      => ['sometimes', 'required'],
        ];
    }


    public function messages()
    {
        return [
            'wid.required'              => '请选择提币币种',
            'address.required'          => '请输入提币地址',
            'amount.required'           => '请输入提币数量',
            'payment_password.required' => '请输入资金密码',
            'v_code.required'           => '请输入短信验证码',
            // 'google_code.required'      => '请输入谷歌验证码',
        ];
    }
}
