<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder whereOrderNo($order_no)
 * Class FbTrans
 * @package App\Models
 */
class FbTrans extends Model
{
    protected $title = '法币交易订单';

    // 1待付款 2已付款 3已确认完成 4 申述中 5取消 6冻结
    const ORDER_PENDING = 1;
    const ORDER_PAID = 2;
    const ORDER_OVER = 3;
    const ORDER_APPEAL = 4;
    const ORDER_CANCEL = 5;

    const TYPE_ORDER = [
        self::ORDER_PENDING => '待付款',
        self::ORDER_PAID    => '已付款',
        self::ORDER_OVER    => '已确认完成',
        self::ORDER_APPEAL  => '申述中',
        self::ORDER_CANCEL  => '取消',
    ];


    protected $table = 'fb_trans';
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
        $prefix = 'FBTRANS' . date('ymdhis');
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
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function chu()
    {
        return $this->belongsTo(User::class, 'chu_uid', 'id');
    }

    public function gou()
    {
        return $this->belongsTo(User::class, 'gou_uid', 'id');
    }

    public function cancel()
    {
        return $this->belongsTo(User::class, 'cancel_uid', 'id');
    }
}
