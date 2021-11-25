<?php

namespace App\Http\Controllers\Api;

use App\Models\SystemSlides;
use Illuminate\Http\Request;

class SystemSlidesController extends BaseController
{
    //
    protected $title = '轮播图';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取轮播图
     * @param Request $request
     * @return array
     */
    public function slides(Request $request)
    {
        $type = $request->input('type', 0);
        $result = SystemSlides::select('image', 'href', 'position')
            ->where('type', $type)
            ->where('lang', \App::getLocale())
            ->get();

        return $this->success($result);
    }
}
