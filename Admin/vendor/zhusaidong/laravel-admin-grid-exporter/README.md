# laravel-admin extension

## grid导出

所列即所导。

列表显示什么，导出就是什么，妈妈再也不用担心我设置各种字段了。

**该插件基于`Laravel-Excel 3.*`开发。所以在使用前请将laravel-admin升级到v1.6.12版本之后。**

### 安装使用

> composer require zhusaidong/laravel-admin-grid-exporter

### 配置

打开`config/admin.php`

```php
'extensions' => [
    'gridexporter' => [
        // Set this to false if you want to disable this extension
        'enable' => true,
    ]
]
```

### 设置

> 由于官方`Grid`的限制，不支持从外部读取`$exporter`属性，所以写了个静态方法`Exporter::get($grid)`来获取`$exporter`属性。

```php
use Zhusaidong\GridExporter\Exporter;

$exporter = Exporter::get($grid);
```

#### 设置导出文件名

```php
$exporter->setFileName('导出文件名.xlsx')；
```
#### 设置排除列

```php
$exporter->setExclusions(['排除列1','排除列2']);
$exporter->setExclusion('排除列3');
```
