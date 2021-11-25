<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class SoftwareController extends BaseController
{

    /**
     *获取网站名称、客服等信息
     */
    public function content(Request $request)
    {
        $key = $request->key;

        if (!$key) {
            $content = config('site');
        } else {
            $content = config('site.' . $key);
        }

        return $this->success(['content' => $content]);
    }


}
