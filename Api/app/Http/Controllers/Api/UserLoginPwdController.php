<?php


namespace App\Http\Controllers\Api;


use App\Http\Requests\Api\ModifyLoginPwdRequest;
use App\Http\Requests\Api\ResetLoginPwdRequest;
use App\Models\User;
use App\Services\UserService;

/**
 * @Resource("UsersLoginPwd")
 * Class UserLoginPwdController
 * @package App\Http\Controllers\Api
 */
class UserLoginPwdController extends BaseController
{

    /**
     * UserPayPwdController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 修改登录密码
     * @Post("/user/modify_login_pwd")
     * @Request({"new_login_pwd": "foo"})
     * @param ModifyLoginPwdRequest $request
     * @return mixed
     */
    public function modify_login_pwd(ModifyLoginPwdRequest $request)
    {
        $user = \Auth::user();
        $user->password = $request->new_login_pwd;
        $user->save();
        return $this->success();
    }

    /**
     * 重置登录密码
     * @Post("/user/reset_login_pwd")
     * @Request({"username":"foo","new_login_pwd": "foo"})
     * @param ResetLoginPwdRequest $request
     * @return mixed
     */
    public function reset_login_pwd(ResetLoginPwdRequest $request,UserService $userService)
    {
        $input = $request->input();
        $username = filter_var($input['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::where([$username => $input['username']])->first();
        if (!$user) {
            return $this->failed(trans('user.username_notfound'));
        }

        $verify_result = $userService->checkStore($input['username'],$input['v_code']);
        if (!$verify_result){
            return $this->failed(trans('common.verification_code_error'));
        }

        $user->password = $input['new_login_pwd'];
        $user->save();
        return $this->success();
    }


}
