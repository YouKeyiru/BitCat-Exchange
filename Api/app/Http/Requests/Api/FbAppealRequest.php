<?php

namespace App\Http\Requests\Api;

class FbAppealRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'refer'    => 'required',
            'order_no' => 'required|min:0',
            'reason'   => 'required|min:0',
        ];
    }

    public function messages()
    {
        return [
            'refer.required'    => '付款参考号不能为空',
            'order_no.required' => '交易编号不能为空',
            'reason.required'   => '申诉理由不能为空',
        ];
    }
}
