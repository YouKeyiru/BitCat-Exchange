<?php

namespace App\Http\Requests\Api;

class AddWithdrawAddress extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address' => ['required', 'max:100'],
            'notes'   => ['required', 'max:255'],
            // 'type'    => ['required', 'in:1,2'],
            'wid'    => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'address.required' => '请输入地址',
            'notes.required'   => '请输入备注',
            // 'type.required'    => '请选择类型', // 1,2
            'wid.required'    => '请选择币种', // 1,2
        ];
    }
}
