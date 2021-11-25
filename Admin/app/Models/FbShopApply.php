<?php

namespace App\Models;

class FbShopApply extends Model
{
    protected $title = '商家管理';

    const SHOP_APPLY_NO = 0;//未申请
    const SHOP_APPLY_CHECK = 1;//申请商家待审核
    const SHOP_APPLY_AGREE = 2;//申请商家同意
    const SHOP_APPLY_REFUSE = 3;//申请商家拒绝
    const SHOP_CANCEL_CHECK = 4;//取消商家待审核
    const SHOP_CANCEL_AGREE = 5;//取消商家同意
    const SHOP_CANCEL_REFUSE = 6;//取消商家拒绝

    const SHOP_ACTION = 1;//申请商家
    const SHOP_ACTION_CANCEL = 2;//取消商家

    const SHOP_APPLY_STATUS = [
        self::SHOP_APPLY_NO      => '未申请',
        self::SHOP_APPLY_CHECK   => '待审核',
        self::SHOP_APPLY_AGREE   => '申请商家同意',
        self::SHOP_APPLY_REFUSE  => '申请商家拒绝',
        self::SHOP_CANCEL_CHECK  => '取消商家待审核',
        self::SHOP_CANCEL_AGREE  => '取消商家同意',
        self::SHOP_CANCEL_REFUSE => '取消商家拒绝',
    ];

    const SHOP_ACTION_TYPE = [
        self::SHOP_ACTION        => '申请商家',
        self::SHOP_ACTION_CANCEL => '取消商家',
    ];

    protected $table = 'fb_shop_apply';
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class, 'uid','id');
    }

    //商户申请状态 和user表保持一致
    public function setStatusAttribute($value)
    {
        $user = User::find($this->uid);
        if($user) {
            $user->config->fbshop = $value;
            $user->config->save();
        }
        $this->attributes['status'] = $value;

    }
}
