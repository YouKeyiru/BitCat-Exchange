<?php

namespace App\Http\Controllers\Api;

use App\Services\CaptchaService;
use Dingo\Api\Http\Request;

class CaptchaController extends BaseController
{
    /**
     * 生成图片验证码
     * @param Request $request
     * @return mixed
     */
    public function show(Request $request)
    {
        return $this->success(CaptchaService::store($request->input('key_str')));
    }
}
