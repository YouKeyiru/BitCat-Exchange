<?php

namespace App\Models;

class AgentUser extends Model
{
    protected $title = '商户';

    protected $table = 'agent_users';
    protected $guarded = ['id'];

    //对应 agent_roles
    const ACCOUNT_ADMIN = 1;//管理员

    const ACCOUNT_CENTER = 2; //运营中心
    const ACCOUNT_UNIT = 3;  //会员单位
    const ACCOUNT_AGENT = 4; //代理商
    const ACCOUNT_STAFF = 5; //员工

    const ACCOUNT_TYPE = [
        self::ACCOUNT_CENTER => '运营中心',
        self::ACCOUNT_UNIT   => '会员单位',
        self::ACCOUNT_AGENT  => '代理商',
        self::ACCOUNT_STAFF  => '员工',
    ];


//    public function assets() {
//        return $this->hasOne(AgentAssets::class,'id', 'uid');
//    }

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
}
