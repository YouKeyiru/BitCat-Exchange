<?php

namespace App\Http\Requests\Api;

class ContractCreateOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'buy_price' => ['required', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //买入价格  正数（包括小数）
            'buy_num'   => ['required', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //买入数量  正数(包括小数)
            'zy'        => ['required', 'sometimes', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //正数（包括小数）
            'zs'        => ['required', 'sometimes', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //正数（包括小数）
            'type'      => ['required', 'in:1,2'], // 1市价 2 限价
            'otype'     => ['required', 'in:1,2'], // 1涨 2跌
            'code'      => 'required', // 产品名称
            'leverage'  => 'required', // 产品杠杆

            // 'trans_type' => ['required', 'in:1,2'] //1 自由交易  2双仓交易
        ];
    }

    public function messages()
    {
        return [
            'buy_price.required' => trans('contract.input_buy_price'),
            'buy_price.regex'    => trans('contract.buy_price_format_error'),
            'buy_num.required'   => trans('contract.input_buy_num'),
            'buy_num.regex'      => trans('contract.buy_num_format_error'),
            'zy.regex'           => trans('contract.zy_format_error'),
            'zs.regex'           => trans('contract.zs_format_error'),
            'zy.required'        => trans('contract.input_zy_price'),
            'zs.required'        => trans('contract.input_zs_price'),
            'type.required'      => trans('contract.input_buy_type'),
            'otype.required'     => trans('contract.input_buy_otype'),
            'code.required'      => trans('contract.input_buy_code'),
            'leverage.required'  => trans('contract.input_buy_leverage'),
        ];
    }
}
