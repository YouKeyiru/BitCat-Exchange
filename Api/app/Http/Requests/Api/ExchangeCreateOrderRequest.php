<?php

namespace App\Http\Requests\Api;

class ExchangeCreateOrderRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'buy_price'   => ['required', 'sometimes', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //委托价格 （限价必填）
            'buy_num'     => ['required', 'sometimes', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //委托数量 （限价必填）
            'total_price' => ['required', 'sometimes', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //委托总金额 （市价买入必填）
            'total_num'   => ['required', 'sometimes', 'regex:/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/'], //委托总数量 （市价卖出必填）
            'type'        => ['required', 'in:1,2'], // 1买入 2卖出 （必填）
            'otype'       => ['required', 'in:1,2'], // 1限价 2市价 （必填）
            'code'        => ['required'] // 产品名称（必填）
        ];
    }

    public function messages()
    {
        return [
            'buy_price.required'   => trans('exchange.input_buy_price'),
            'buy_num.required'     => trans('exchange.input_buy_num'),
            'total_price.required' => trans('exchange.input_total_price'),
            'total_num.required'   => trans('exchange.input_total_num'),
            'type.required'        => trans('exchange.input_buy_type'),
            'otype.required'       => trans('exchange.input_buy_otype'),
            'code.required'        => trans('exchange.input_buy_code'),
            'buy_price.regex'      => trans('exchange.buy_price_format_error'),
            'buy_num.regex'        => trans('exchange.buy_num_format_error'),
            'total_price.regex'    => trans('exchange.total_price_format_error'),
            'total_num.regex'      => trans('exchange.total_num_format_error'),
        ];
    }
}
