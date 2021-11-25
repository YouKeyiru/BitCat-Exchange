<?php


namespace App\Http\Controllers\Api;


use App\Entities\Notification\SmsHandel;
use App\Http\Requests\Api\ModifyPayPwdRequest;
use App\Http\Requests\Api\ResetPayPwdRequest;
use App\Http\Requests\Api\SetPayPwdRequest;

/**
 * @Resource("UsersPayPwd")
 * Class UserPayPwdController
 * @package App\Http\Controllers\Api
 */
class UserPayPwdController extends BaseController
{

    /**
     * UserPayPwdController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 设置支付密码
     * @Post("/user/set_pay_pwd")
     * @Request({"new_pay_pwd": "foo"})
     * @param SetPayPwdRequest $request
     * @return mixed
     */
    public function set_pay_pwd(SetPayPwdRequest $request)
    {
        $user = \Auth::user();
        $user->payment_password = $request->new_pay_pwd;
        $user->config->payment_password_set = 1;
        $user->save();
        $user->config->save();
        return $this->success();
    }

    /**
     * 修改支付密码
     * @Post("/user/modify_pay_pwd")
     * @Request({"new_pay_pwd": "foo"})
     * @param ModifyPayPwdRequest $request
     * @return mixed
     */
    public function modify_pay_pwd(ModifyPayPwdRequest $request)
    {
        $user = \Auth::user();
        $user->payment_password = $request->new_pay_pwd;
        $user->save();
        return $this->success();
    }

    /**
     * 重置支付密码
     * @Post("/user/reset_pay_pwd")
     * @Request({"new_pay_pwd": "foo"})
     * @param ResetPayPwdRequest $resetPayPwdRequest
     * @return mixed
     */
    public function reset_pay_pwd(ResetPayPwdRequest $resetPayPwdRequest)
    {
        $user = \Auth::user();
//        $input = $resetPayPwdRequest->input();
        try {
//            SmsHandel::check($user->phone, $input['v_code']);


            $user->payment_password = $resetPayPwdRequest->new_pay_pwd;
            $user->save();

            $user->config->payment_password_set = 1;
            $user->config->save();
        } catch (\Exception $exception) {
            return $this->failed($exception->getMessage());
        }


        return $this->success();
    }

}
