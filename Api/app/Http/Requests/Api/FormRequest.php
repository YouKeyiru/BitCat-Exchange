<?php

namespace App\Http\Requests\Api;

use Dingo\Api\Http\FormRequest as BaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

//    public function failedValidation(Validator $validator)
//    {
//        throw (new HttpResponseException(response()->json([
//            'status_code' => 500,
//            'message'     => '参数错误:' . $validator->errors()->first(),
//            'data'        => [],
//        ], 200)));
//    }

}
