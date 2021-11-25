<?php

namespace App\Http\Controllers\Api;

use App\Models\FeedBack;
use Dingo\Api\Http\Request;

class FeedbackController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        //
        $user = \Auth::user();
        $content = htmlspecialchars($request->post('content'));
        if (strlen($content) >= 255) {
            return $this->failed('内容过长');
        }

        if (strlen($content) <= 10) {
            return $this->failed('内容过短');
        }

        FeedBack::create([
            'uid' => $user->id,
            'content' => $content
        ]);

        //一日限制反馈三次


        return $this->success([], '反馈成功');
    }

    //反馈记录
    public function record()
    {
        $user = \Auth::user();
        $data = $user->feedBack()
            ->select('content','reply','created_at','updated_at')
            ->orderByDesc('updated_at')
            ->paginate(15);
        return $this->success($data);
    }

}
