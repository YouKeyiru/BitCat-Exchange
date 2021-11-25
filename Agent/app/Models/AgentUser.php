<?php

namespace App\Models;

class AgentUser extends Model
{
    protected $title = '代理';

    protected $table = 'agent_users';
    protected $guarded = ['id'];

    //对应agent_roles
    // 1 管理员 2 运营中心 3 会员单位 4 代理商 5 合伙人
    const ACCOUNT_ADMIN = 1;
    const ACCOUNT_CENTER = 2;
    const ACCOUNT_UNIT = 3;
    const ACCOUNT_AGENT = 4;
    const ACCOUNT_PARTNER = 5;

    const ACCOUNT_TYPE = [
        self::ACCOUNT_ADMIN => '管理员',
        self::ACCOUNT_CENTER => '运营中心',
        self::ACCOUNT_UNIT => '会员单位',
        self::ACCOUNT_AGENT => '代理商',
        self::ACCOUNT_PARTNER => '员工',
    ];

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            if (!$model->invite_code) {
                $model->invite_code = static::findAvailableNo();
                // 如果生成失败，则终止创建
                if (!$model->invite_code) {
                    return false;
                }
            }
        });
    }

    public static function findAvailableNo()
    {
        // 订单流水号前缀
        $prefix = '';
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('invite_code', $no)->exists()) {
                return $no;
            }
            usleep(100);
        }
        return false;
    }


    public function recommend()
    {
        return $this->belongsTo(AgentUser::class, 'recommend_id', 'id');
    }

    public function agent()
    {
        return $this->belongsTo(AgentUser::class, 'agent_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(AgentUser::class, 'unit_id', 'id');
    }

    public function center()
    {
        return $this->belongsTo(AgentUser::class, 'center_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(AgentUser::class, 'staff_id', 'id');
    }

//    public function user() {
//        return $this->belongsTo(User::class, 'agent_id','id');
//    }
}
