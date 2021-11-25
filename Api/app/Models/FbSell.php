<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder whereOrderNo($order_no)
 * Class FbSell
 * @package App\Models
 */
class FbSell extends Model
{
    protected $title = '法币交易出售';

    protected $table = 'fb_sell';
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            if (!$model->order_no) {
                // 调用 findAvailableNo 生成订单流水号
                $model->order_no = static::findAvailableNo();
                // 如果生成失败，则终止创建订单
                if (!$model->order_no) {
                    return false;
                }
            }
        });
    }

    public static function findAvailableNo()
    {
        // 订单流水号前缀
        $prefix = 'FBSELL' . date('ymdhis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('order_no', $no)->exists()) {
                return $no;
            }
            usleep(100);
        }
        return false;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id')->select(['id', 'account', 'avatar', 'phone', 'email', 'nickname']);
    }
}
