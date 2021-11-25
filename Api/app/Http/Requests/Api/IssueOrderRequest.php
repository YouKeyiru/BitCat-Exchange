<?php

namespace App\Http\Requests\Api;

class IssueOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'order_type'   => ['required', function ($attribute, $value, $fail) {
                if (in_array($value, [1, 2])) {
                    if ($value == 1) {
                        $user = \Auth::user();
//                        $check = ContractPosition::where('uid', $user->id)->first();
//                        if ($check) {
//                            //存在未完成的交易订单
//                            $fail(trans('fb.input_post_type_error'));
//                        }
                    }
                } else {
                    $fail(trans('fb.input_post_type_error'));
//                    $fail(trans('user.verify_failed'));
                }
            }], //1.sell  2 buying
            'wid'          => ['required', function ($attribute, $value, $fail) {
                if ($value != 1) {
                    $fail(trans('user.input_wid_not_allow'));
                }
//                if (!WalletCode::find($value)) {
//                    $fail(trans('asset.code_no_existent'));
//                }
            }],
            'min_price'    => 'required|numeric|gt:0|max:10000000',
            'max_price'    => 'required|numeric|gt:min_price|max:10000000',
            'trans_num'    => 'required|numeric|gt:0|max:10000000',
            'price'        => 'required|numeric|min:0|max:10000000',
            'pay_method'   => 'required|string',
            'notes'        => 'sometimes|max:200',
//            'payment_password' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'order_type.required' => trans('fb.input_post_type'),
            'wid.required'        => trans('asset.code_no_existent'),
            'min_price.required'  => trans('fb.input_min_price'),
            'min_price.gt'        => trans('fb.min_price_gt_0'),
            'max_price.required'  => trans('fb.input_max_price'),
            'max_price.gt'        => trans('fb.max_gt_min '),
            'trans_num.gt'        => trans('fb.trans_num_gt_0'),
            'trans_num.required'  => trans('fb.input_trans_num'),
            'price.required'      => trans('fb.input_price'),
//            'payment_password.required' => trans('user.input_pay_pwd'),
        ];
    }
}
