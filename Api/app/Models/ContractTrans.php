<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder whereOrderNo($order_no)
 * Class ContractTrans
 * @package App\Models
 */
class ContractTrans extends Model
{
    //
    protected $title = '会员交易单';

    protected $table = 'contract_trans';
    protected $guarded = ['id'];

    //平仓类型 1手动平仓 2止盈平仓 3止损平仓 4爆仓

    const CLOSE_MANUAL = 1;
    const CLOSE_SURPLUS = 2;
    const CLOSE_LOSS = 3;
    const CLOSE_BURST = 4;
    const CLOSE_FORCE = 5;

    const TYPE_CLOSE = [
        self::CLOSE_MANUAL  => '手动平仓',
        self::CLOSE_SURPLUS => '止盈平仓',
        self::CLOSE_LOSS    => '止损平仓',
        self::CLOSE_BURST   => '爆仓',
        self::CLOSE_FORCE   => '系统强平',
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
        $prefix = 'TRANS' . date('ymdhis');
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
