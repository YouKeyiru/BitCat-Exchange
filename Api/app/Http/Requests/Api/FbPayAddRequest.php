<?php

namespace App\Http\Requests\Api;


class FbPayAddRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_type' => ['required', 'in:1,2,3'], // 1银行卡 2支付宝 3微信
            'auth_name'    => 'required_if:payment_type,1',
            'bank'         => 'required_if:payment_type,1',
            'branch'       => 'required_if:payment_type,1',
            'card_num'     => 'required|min:3', // 账号 银行卡 ，支付宝 ，微信 账号
            'act'          => ['required', 'in:add,edit'],
            'qrcode'       => 'required_if:payment_type,2',
            'payment_password'       => 'required',
        ];
    }

    public function messages()
    {
        return [
            'payment_type.required' => trans('fb.input_payment_type'),
            'auth_name.required_if' => trans('fb.input_auth_name'),
            'bank.required_if'      => trans('fb.input_bank'),
            'branch.required_if'    => trans('fb.input_branch'),
            'card_num.required'     => trans('fb.input_card_num'),
            'act.required'          => trans('fb.input_act'),
            'qrcode.required_if'    => trans('fb.input_qrcode'),
            'payment_password.required'       => trans('fb.input_pay_pwd'),
        ];
    }
}
