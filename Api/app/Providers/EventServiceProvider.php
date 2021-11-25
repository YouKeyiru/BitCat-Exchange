<?php

namespace App\Providers;

use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
//        Registered::class => [
//            SendEmailVerificationNotification::class,
//        ],

        //注册后
        'App\Events\Registered'               => [
            'App\Listeners\RegisteredNotification',
        ],

        //法币下单后
        'App\Events\AfterCreateTransOrder'    => [
            'App\Listeners\CreateTransOrderNotification',
        ],

        //法币订单确认后
        'App\Events\AfterConfirmOrder'        => [
            'App\Listeners\ConfirmOrderNotification',
        ],

        //币币下单后
        'App\Events\AfterCreateExchangeOrder' => [
            'App\Listeners\AfterCreateExchangeOrderNotification',
        ],

        //合约下单后
        'App\Events\AfterCreateContractOrder' => [
            'App\Listeners\AfterCreateContractOrderNotification',
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
