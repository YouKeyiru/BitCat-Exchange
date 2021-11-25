<?php

namespace App\Http\Requests\Api;

class ExchangeOrderListRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_type' => ['required', 'in:1,2'], // 1当前委托 2历史成交
            'code'       => ['sometimes', 'required', 'string'] // 产品名称（必填）
        ];
    }

    public function messages()
    {
        return [
            'order_type.required' => trans('exchange.input_order_type'),
            'code.required'       => trans('exchange.input_buy_code'),
        ];
    }
}
