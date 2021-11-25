<?php

namespace App\Http\Controllers\Api;

use App\Models\AgentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function agent(Request $request)
    {
        $pid = $request->get('q');
        return AgentUser::where('recommend_id', $pid)->get(['id', DB::raw('username as text')]);
    }

    public function upload(Request $request)
	{
		$urls = [];
		foreach ($request->file() as $file) {
			// 1.是否上传成功
	        if (! $file->isValid()) {
	            return ['code' => 500,'data' => []];
	        }


	        // 2.是否符合文件类型 getClientOriginalExtension 获得文件后缀名
	        $fileExtension = $file->getClientOriginalExtension();
	        if(! in_array($fileExtension, ['png','PNG', 'jpg','JPG', 'gif','GIF','JPEG','jpeg'])) {
	            return ['code' => 500,'data' => []];
	        }

	        // 3.判断大小是否符合 2M
	        $tmpFile = $file->getRealPath();
	        if (filesize($tmpFile) >= 2048000) {
	            return ['code' => 500,'data' => []];
	        }

	        // 4.是否是通过http请求表单提交的文件
	        if (! is_uploaded_file($tmpFile)) {
	            return ['code' => 500,'data' => []];
	        }
	        
	        // 5.每天一个文件夹,分开存储, 生成一个随机文件名
	        $fileName = '/images/'.date('Y_m_d').'/'.md5(time()) .mt_rand(0,9999).'.'. $fileExtension;
	        if (!Storage::disk('oss')->put($fileName, file_get_contents($tmpFile))){
	            return ['code' => 500,'data' => []];
	        }

	        $urls[] = env('IMG_URL').$fileName;

	    }

	    return [
	        "errno" => 0,
	        "data"  => $urls,
	    ];
	}
}
