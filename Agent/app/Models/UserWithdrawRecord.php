<?php

namespace App\Models;

class UserWithdrawRecord extends Model
{
    protected $title = '用户提币记录';
    protected $guarded = ['id'];
    protected $table = 'user_withdraw_record';

    const WAIT_CHECK = 1;//未到账
    const ARRIVING = 2;//到帐中
    const CHECK_REFUSE = 3;//拒绝
    const CHECK_AGREE = 4;//同意
    const CHECK_ERROR = 5;//失败
    const REVOKE = 6;//撤回

    const STATUS = [
        self::WAIT_CHECK   => '未到账',
        self::ARRIVING     => '到帐中',
        self::CHECK_REFUSE => '拒绝',
        self::CHECK_AGREE  => '同意',
        self::CHECK_ERROR  => '失败',
        self::REVOKE  => '撤回',
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
        $prefix = 'WITH' . date('ymdhis');
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
