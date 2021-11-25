<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Dingo\Api\Routing\Helpers;

class BaseController extends Controller
{
    use Helpers, ApiResponse;

    protected $lang = 'zh-CN';

    public function __construct()
    {
        $this->changeLand();
    }

    /**
     * 切换语言包
     */
    public function changeLand()
    {
        $land = \request()->header('lang','zh-CN');
        if ($land == 'en') {
            $this->lang = 'en';
            \App::setLocale('en');
            return;
        }
        \App::setLocale($land);
//        \App::setLocale('zh-CN');
    }

}
