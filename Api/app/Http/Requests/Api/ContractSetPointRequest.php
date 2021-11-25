<?php

namespace App\Http\Requests\Api;

class ContractSetPointRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_no' => 'required',
            'zy'       => ['sometimes', 'required', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //正数（包括小数）
            'zs'       => ['sometimes', 'required', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //正数（包括小数）
        ];
    }

    public function messages()
    {
        return [
            'order_no.required' => trans('contract.input_order_no'),
            'zy.required'       => trans('contract.input_zy_price'),
            'zs.required'       => trans('contract.input_zs_price'),
        ];
    }
}
