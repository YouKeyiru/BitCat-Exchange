<?php

namespace App\Http\Requests\Api;

class FbCreateOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'order_type'  => ['required', function ($attribute, $value, $fail) {
                if (!in_array($value, [1, 2])) {
                    $fail(trans('fb.input_post_type_error'));
                }
            }],
            'total_num'   => 'required|numeric|gt:0|max:10000000',
            'total_price' => 'required|numeric|gt:0|max:10000000',
            'order_no'    => 'required|string',
            'pay_method'  => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'pay_method.required'  => trans('fb.input_pay_method'),
            'order_no.required'    => trans('fb.input_order_no'),
            'order_type.required'  => trans('fb.input_post_type'),
            'total_num.required'   => trans('fb.input_total_num'),
            'total_num.gt'         => trans('fb.total_num_gt_0'),
            'total_price.required' => trans('fb.input_total_price'),
            'total_price.gt'       => trans('fb.total_price_gt_0 '),
        ];
    }
}
