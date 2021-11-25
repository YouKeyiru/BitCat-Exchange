<?php

namespace App\Http\Controllers\Api;

use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @Resource("Upload")
 * Class UploadController
 * @package App\Http\Controllers\Api
 */
class UploadController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * @Post("/upload")
     * @Request({"file": "/image/aaa/bbb.jpg"})
     * @param Request $request
     * @return mixed
     */
    public function images(Request $request)
    {
        try {
            $file = $request->file('file');

            $result = $this->upload($file);
            if ($result['code'] == 200) {
                $path = $result['data'];
            } else {
                throw new \Exception($result['msg']);
            }

            $data = [
                'absolute_path' => ImageService::setHost() .'storage'. $path,
                'relative_path' => $path,
            ];
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    public function upload($file, $disk = 'public')
    {
        // 1.是否上传成功
        if (!$file->isValid()) {
            return ['code' => 500, 'msg' => '上传失败'];
        }

        // 2.是否符合文件类型 getClientOriginalExtension 获得文件后缀名
        $fileExtension = $file->getClientOriginalExtension();
        if (!in_array($fileExtension, ['png', 'PNG', 'jpg', 'JPG', 'gif', 'GIF', 'JPEG', 'jpeg'])) {
            return ['code' => 500, 'msg' => '格式不正确'];
        }

        // 3.判断大小是否符合 2M
        $tmpFile = $file->getRealPath();
        if (filesize($tmpFile) >= 2048000) {
            return ['code' => 500, 'msg' => '文件大小大于2M'];
        }

        // 4.是否是通过http请求表单提交的文件
        if (!is_uploaded_file($tmpFile)) {
            return ['code' => 500, 'msg' => '请求方式错误'];
        }
        // 5.每天一个文件夹,分开存储, 生成一个随机文件名
        $fileName = '/images/' . date('Y_m_d') . '/' . md5(time()) . mt_rand(0, 9999) . '.' . $fileExtension;
        if (Storage::disk($disk)->put($fileName, file_get_contents($tmpFile))) {
            return ['code' => 200, 'data' => $fileName];
        }
    }
}
