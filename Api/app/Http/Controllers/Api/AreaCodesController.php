<?php


namespace App\Http\Controllers\Api;


use App\Models\AreaCodes;

class AreaCodesController extends BaseController
{
    /**
     * 获取城市代码
     * @return mixed
     */
    public function areaCodes(){

        $lists = AreaCodes::select('code', 'country')->get();
        return $this->success($lists);
    }
}
