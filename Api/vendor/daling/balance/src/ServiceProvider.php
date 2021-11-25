<?php

namespace Daling\Balance;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 加载迁移文件
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // 发布配置文件
        $this->publishes([
            __DIR__ . '/../config/recharge.php' => config_path('recharge.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
