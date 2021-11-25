<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder whereOrderNo($order_no)
 * Class ExchangeOrder
 * @package App\Models
 */
class ExchangeOrder extends Model
{
    //

    protected $title = '会员交易单';

    protected $table = 'exchange_orders';
    protected $guarded = ['id'];

    // 0待交易 1交易中 2交易完成(数据分离到记录表) 3撤单
    const WAIT_TRANS = 0;
    const ING_TRANS = 1;
    const OVER_TRANS = 2;
    const REVOKE_TRANS = 3;

    const TYPE_STATUS = [
        self::WAIT_TRANS => '待交易',
        self::ING_TRANS => '交易中',
        self::OVER_TRANS => '交易完成',
        self::REVOKE_TRANS => '撤单',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

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
        $prefix = 'EXCHANGE' . date('ymdhis');
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
}
