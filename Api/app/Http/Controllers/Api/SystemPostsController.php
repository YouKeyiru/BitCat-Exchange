<?php

namespace App\Http\Controllers\Api;

use App\Models\SystemPosts;
use Illuminate\Http\Request;

class SystemPostsController extends BaseController
{
    //
    protected $title = '平台公告';

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 公告/资讯 详情
     * @param Request $request
     * @return array
     */
    public function posts_info(Request $request)
    {
        $type = $request->input('type', 1);
        $posts_id = $request->input('posts_id', 0);

        if ($type == 2) {
            $result = \DB::table('xy_blocks_msg')
                ->select('bm_title as title', 'pic_addr', 'content', 'issue_time as created_at')
                ->where('id', $posts_id)
                ->first();
        } else {
            $result = SystemPosts::query()->select('title', 'content', 'created_at')->find($posts_id);
        }
        return $this->success($result);
    }

    /**
     * 公告列表
     * @param Request $request
     * @return array
     */
    public function posts_list(Request $request)
    {
        $type = $request->input('type', 1);
        $result = SystemPosts::query()
            ->select('id as posts_id', 'title', 'content', 'created_at')
            ->where([
                'type'    => $type,
                'display' => SystemPosts::DISPLAY_ON,
                'lang'    => \App::getLocale()
            ])
            ->orderBy('id', 'desc')
            ->paginate(10);
        return $this->success($result);
    }

    /**
     * 资讯列表
     * @param Request $request
     * @return array
     */
    public function blocks_msg(Request $request)
    {
        $lang = $request->header('lang','zh-CN');
        $lang = $lang == 'zh-CN' ? 'zh' : 'en';

        $result = \DB::table('xy_blocks_msg')
            ->select('id as posts_id', 'bm_title as title', 'pic_addr', 'content', 'issue_time as created_at')
            ->where('lang', $lang)
            ->orderBy('id', 'desc')
            ->paginate(15);
        return $this->success($result);
    }

}
