<?php

namespace App\Http;

use App\Http\Middleware\ContractTransStatus;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'                => \App\Http\Middleware\Authenticate::class,
        'auth.basic'          => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'            => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers'       => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'                 => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'               => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm'    => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'              => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'            => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'            => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'enableCrossRequests' => \App\Http\Middleware\EnableCrossRequest::class,

        //高级实名验证
        'advanced'            => \App\Http\Middleware\Advanced::class,
        //用户状态
        'auth_user'            => \App\Http\Middleware\AuthUser::class,
        //谷歌验证码验证
        'google_verify'       => \App\Http\Middleware\GoogleVerify::class,
        //资金密码验证
        'pwd_verify'          => \App\Http\Middleware\VerifyPayPwd::class,
        //短信验证
        'sms_verify'          => \App\Http\Middleware\VerifySmsCode::class,
        'mail_verify'          => \App\Http\Middleware\VerifyMailCode::class,

        //图形验证码验证
        'captcha_verify'        => \App\Http\Middleware\CaptchaVerify::class,

//        //短信验证
//        'sms_verify'          => \App\Http\Middleware\SmsVerify::class,
//        //邮箱验证
//        'email_verify'        => \App\Http\Middleware\EmailVerify::class,

        //法币交易发布单验证
        'fb.create_order'        => \App\Http\Middleware\FbCreateOrder::class,

        //接口安全验证
        'api.security'        => \App\Http\Middleware\ApiSecurity::class,

        //合约交易下单限制
        'contract_trans'        => \App\Http\Middleware\ContractTransStatus::class,

    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
