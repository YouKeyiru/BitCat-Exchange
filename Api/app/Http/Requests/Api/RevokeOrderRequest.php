<?php

namespace App\Http\Requests\Api;


use Illuminate\Validation\Rule;

class RevokeOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_type' => ['required', Rule::in([1, 2])],
            'order_no'   => 'required|min:1',
        ];
    }

    public function messages()
    {
        return [
            'order_type.required' => '请选择正确的类型',
            'order_no.required'   => '请输入订单号',
        ];
    }
}
