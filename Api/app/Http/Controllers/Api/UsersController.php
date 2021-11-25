<?php

namespace App\Http\Controllers\Api;

use App\Entities\Notification\Email\VerifyMailHandel;
use App\Entities\Notification\SmsHandel;
use App\Events\Registered;
use App\Http\Requests\Api\UserRequest;
use App\Models\User;
use App\Services\GoogleAuthenticatorService;
use App\Services\UserService;
use Dingo\Api\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @Resource("Users")
 * Class UsersController
 * @package App\Http\Controllers\Api
 */
class UsersController extends BaseController
{
    /**
     * UsersController constructor.
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 用户详情
     * @Get("/user/info")
     * @return mixed
     */
    public function show()
    {
        $user = \Auth::user();

        $result = [
            'user'   => [
                'account'        => $user->account,
                'phone'          => $user->phone,
                'email'          => $user->email,
                'nickname'       => $user->nickname,
                'avatar'         => $user->avatar,
                'authentication' => $user->authentication,
            ],
            'config' => [
                'google_verify'        => $user->config->google_verify,
                'google_bind'          => $user->config->google_bind,
                'payment_password_set' => $user->config->payment_password_set,
                'phone_bind'           => $user->config->phone_bind,
                'email_bind'           => $user->config->email_bind,
                'security_level'       => $user->config->security_level,
                'fbshop'               => $user->config->fbshop,
            ],
        ];

        if ($result['user']['authentication']) {
            $result['auth'] = [
                'name'          => $user->name,
                'status'        => $user->auth->status,
                'card_id'       => $user->auth->card_id,
                'refuse_reason' => $user->auth->refuse_reason,
            ];
        }
        return $this->success($result);
    }

    /**
     * 注册
     * @Post("/register")
     * @Request({"username": "foo", "password": "bar","v_code":"123456","invite_code":"666888","password_confirmation":"bar"})
     * @param UserRequest $request
     * @param UserService $userService
     * @return mixed
     * @throws \Exception
     */
    public function store(UserRequest $request, UserService $userService)
    {
        $input = $request->input();
        $username = filter_var($input['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        if ($userService->isRegister($username, $input['username'])) {
            return $this->failed(trans('user.username_registered'));
        }

        $verify_result = $userService->checkStore($input['username'],$request->v_code);
        if (!$verify_result){
            return $this->failed(trans('common.verification_code_error'));
        }

        try {
            \DB::beginTransaction();

            $userData = [
                'account'   => UserService::createUID(),
                'password'  => $input['password'],
                $username   => $input['username'],
            ];

            if ($username == 'phone'){
                $userData['area_code'] = $input['area_code'];
            }

            $user = User::create($userData);

            $userService->afterRegister($user);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->failed(trans('user.register_failed') . ':' . $e->getMessage());
        }

        event(new Registered($user));

        return $this->success([
            'access_token' => \Auth::guard('api')->fromUser($user),
            'token_type'   => 'Bearer',
            'expires_in'   => \Auth::guard('api')->factory()->getTTL() * 60
        ], trans('user.register_success'));
    }

    /**
     * 登录
     * @Post("/login")
     * @Request({"username": "foo", "password": "bar"})
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $input = $request->input();

        if (isset($input['google_code'])) {
            return $this->google_login($input);
        }

        $username = filter_var($input['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (!\Auth::attempt([$username => $request->input('username'), 'password' => $request->password])) {
            return $this->failed(trans('user.login_pwd_error'));
        }

        $user = \Auth::user();
        if ($user->stoped) {
            return $this->failed('账号已冻结',401);
        }
        if ($user->config->google_verify) {
            return $this->success([
                'access_token' => 0,
                'secret'       => encrypt($user->id . '.' . $user->account)
            ], trans('common.input_google_verification'));
        }

        $meta = [
            'access_token' => \Auth::guard('api')->fromUser($user),
            'token_type'   => 'Bearer',
            'expires_in'   => \Auth::guard('api')->factory()->getTTL() * 60
        ];

        self::storeToken($user);

        $user->last_login_time = Carbon::now();
        $user->current_token = $meta['access_token'];
        $user->save();

        return $this->success($meta, trans('user.login_success'));
    }

    /**
     * 保存令牌
     * @param $user
     * @param $token
     */
    private static function storeToken(User $user){
        if($user->current_token) {
            $apiGuard = \Auth::guard('api');
            $apiGuard->setToken($user->current_token);
            // 检查旧 Token 是否有效
            if ($apiGuard->check()) {
                // 加入黑名单
                $apiGuard->invalidate();
            }
        }
    }

    /**
     * 谷歌验证登录
     * @param $input
     * @return mixed
     */
    public function google_login($input)
    {
        $secret = decrypt($input['secret']);
        $de_secret = explode('.', $secret);
        $user = User::find($de_secret[0]);
        $config = $user->config;
        if (empty($config)) {
            return $this->failed('解析Secret失败');
        }
        // 验证验证码和密钥是否相同
        if (!GoogleAuthenticatorService::CheckCode($config->google_secret, $input['google_code'])) {
            return $this->failed(trans('common.google_verification_code_error'));
        }

        $meta = [
            'access_token' => \Auth::guard('api')->fromUser($user),
            'token_type'   => 'Bearer',
            'expires_in'   => \Auth::guard('api')->factory()->getTTL() * 60
        ];

        self::storeToken($user);
        $user->last_login_time = Carbon::now();
        $user->current_token = $meta['access_token'];
        $user->save();

        return $this->success($meta, trans('user.login_success'));
    }

    /**
     * 修改用户名称
     * @Post("/user/set_name")
     * @Request({"name": "foo"})
     * @param Request $request
     * @return mixed
     */
    public function updateName(Request $request)
    {
        $name = $request->input('name', '');
        if (!$name) {
            return $this->failed(trans('user.input_username'));
        }
        if (strlen($name) > 100) {
            return $this->failed(trans('user.username_rule'));
        }
        $user = \Auth::user();
        $user->nickname = $name;
        $user->save();
        return $this->success();
    }

    /**
     * 更新头像
     * @Post("/user/set_avatar")
     * @Request({"avatar": "foo"})
     * @param Request $request
     * @return mixed
     */
    public function updateAvatar(Request $request)
    {
        $avatar = $request->input('avatar', '');
        if (!$avatar) {
            return $this->failed(trans('user.input_avatar'));
        }
        UserService::setAvatar(\Auth::user(), $avatar);
        return $this->success();
    }

    /**
     * 绑定手机号
     * @Post("/user/set_phone")
     * @Request({"phone": "foo"})
     * @param Request $request
     * @return mixed
     */
    public function updatePhone(Request $request,UserService $userService)
    {
        $phone = $request->input('phone', '');
        if (!$phone) {
            return $this->failed(trans('user.input_phone'));
        }
        if (User::wherePhone($phone)->first()) {
            return $this->failed(trans('user.username_registered'));
        }

        $verify_result = $userService->checkStore($phone,$request->v_code);
        if (!$verify_result){
            return $this->failed(trans('common.verification_code_error'));
        }

        $user = \Auth::user();
        UserService::setPhone($user, $phone);
        return $this->success();
    }

    /**
     * 绑定邮箱号
     * @Post("/user/set_email")
     * @Request({"email": "foo"})
     * @param Request $request
     * @return mixed
     */
    public function updateEmail(Request $request,UserService $userService)
    {
        $email = $request->input('email', '');
        if (!$email) {
            return $this->failed(trans('user.input_email'));
        }
        $type = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        if($type!='email'){
            return $this->failed(trans('user.email_rule'));
        }


        if (User::whereEmail($email)->first()) {
            return $this->failed(trans('user.username_registered'));
        }

        $verify_result = $userService->checkStore($email,$request->v_code);
        if (!$verify_result){
            return $this->failed(trans('common.verification_code_error'));
        }

        $user = \Auth::user();
        UserService::setEmail($user, $email);
        return $this->success();
    }


}
