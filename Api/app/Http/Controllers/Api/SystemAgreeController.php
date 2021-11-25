<?php

namespace App\Http\Controllers\Api;

use App\Models\SystemAgree;
use Dingo\Api\Exception\ValidationHttpException;
use Illuminate\Http\Request;

class SystemAgreeController extends BaseController
{
    //
    protected $title = '平台协议';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 协议列表
     * @return mixed
     */
    public function agree_list()
    {
        $agrees = SystemAgree::TYPE_AGREE;
        $result = [];
        foreach ($agrees as $k => $item) {
            $result[] = [
                'agree_type' => $k,
                'agree_name' => $item,
            ];
        }
        return $this->success($result);
    }

    /**
     * 平台协议内容
     * @param Request $request
     * @return array
     */
    public function show(Request $request)
    {
        $type = $request->input('type', 0);
        $result = SystemAgree::query()
            ->select('title', 'content', 'created_at')
            ->where([
                'lang' => \App::getLocale(),
                'type' => $type
            ])
            ->first();
        return $this->success($result);
    }
}
