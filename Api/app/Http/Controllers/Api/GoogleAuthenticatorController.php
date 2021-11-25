<?php


namespace App\Http\Controllers\Api;


use App\Entities\Notification\SmsHandel;
use App\Http\Requests\Api\UpdateGoogleRequest;
use App\Services\GoogleAuthenticatorService;
use App\Services\UserService;
use Dingo\Api\Http\Request;

class GoogleAuthenticatorController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建谷歌验证码
     * @return mixed
     */
    public function createGoogleSecret()
    {
        $user = \Auth::user();
        if ($user->config->google_bind) {
            return $this->failed(trans('common.yes_bind'));
        }

        $createSecret = GoogleAuthenticatorService::CreateSecret();
        // 自定义参数，随表单返回
        $parameter = [];
        return $this->success(['createSecret' => $createSecret, "parameter" => $parameter]);
    }

    /**
     * 绑定谷歌验证码
     * @param UpdateGoogleRequest $request
     * @return mixed
     */
    public function updateGoogle(UpdateGoogleRequest $request)
    {
        $user = \Auth::user();
        $input = $request->input();
        if ($user->config->google_bind) {
            return $this->failed(trans('common.yes_bind'));
        }
        if (!GoogleAuthenticatorService::CheckCode($input['google_secret'], $input['google_code'])) {
            return $this->failed(trans('common.google_verification_code_error'));
        }
        UserService::setGoogle($user, $input['google_secret']);
        return $this->success();
    }

    /**
     * 开启或关闭谷歌验证
     * @param Request $request
     * @return mixed
     */
    public function googleVerifyStart(Request $request)
    {
        $input = $request->input();
        $user = \Auth::user();
        if ($user->config->google_bind == 0) {
            return $this->failed(trans('common.no_bind'));
        }

        if (!GoogleAuthenticatorService::CheckCode($user->config->google_secret, $input['google_code'])) {
            return $this->failed(trans('common.google_verification_code_error'));
        }

        switch ($input['act']) {
            case 'start':
                $user->config->google_verify = 1;
                $user->config->security_level += 1;
                break;
            case 'stop':

                $checkSms = SmsHandel::check($user->phone, $request->v_code);
                if (!$checkSms) {
                    return $this->failed(trans('common.verification_code_error'));
                }

                $user->config->google_verify = 0;
                $user->config->security_level -= 1;
                break;
            default:
                return $this->failed(trans('common.params_error'));
        }


        $user->config->save();
        return $this->success();
    }

}
