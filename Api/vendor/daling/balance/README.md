# CheckBalance for Laravel

#### 发布配置文件

``` php
php artisan vendor:publish --provider="Daling\Balance\ServiceProvider"
```
#### 生成迁移
``` php
php artisan migrate
```

#### Use the Traits

``` php
use AddressRecharge;
return $this->test();
```


#### 触发以下事件(可选)

- AddressRecharge
