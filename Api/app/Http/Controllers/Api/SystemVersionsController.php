<?php


namespace App\Http\Controllers\Api;

use App\Models\SystemVersion;
use Dingo\Api\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SystemVersionsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 下载链接
     */
    public function downloadLink()
    {
        $link = config('site.software_link');

        $qrcode = QrCode::encoding('UTF-8')->format('png')->size(368)->margin(0)
            ->generate($link);
        $data['qrcode'] = 'data:image/png;base64,' . base64_encode($qrcode);
        $data['link'] = $link;

        return $this->success($data);
    }

    /**
     * 版本更新
     * @param Request $request
     * @return array
     */
    public function softwareUpdate(Request $request)
    {
        $clientVersion = $request->version;
        $type = $request->type;
        if (!in_array($type, array(1, 2))) {
            return $this->failed('更新范围错误');
        }
        $version = SystemVersion::query()->select('title', 'content', 'address', 'uptype', 'vercode')->where('type', $type)->orderBy('id', 'desc')->first();
        if (is_null($version)) {
            return $this->success([], '没有更新内容');
        }
        $v1 = str_replace('.','',$version->vercode);
        $v2 = str_replace('.','',$clientVersion);

        if ($v1 > $v2) {
            return $this->success($version, '有新版本');
        } else {
            return $this->success([], '当前版本为最新');
        }
    }

}
