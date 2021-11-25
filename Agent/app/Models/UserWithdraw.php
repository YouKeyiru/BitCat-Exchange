<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserWithdraw extends Model
{
    protected $title = '用户提现';

    const WAIT_CHECK = 1;//未到账
    const ARRIVING = 2;//到帐中
    const CHECK_REFUSE = 3;//拒绝
    const CHECK_AGREE = 4;//同意
    const CHECK_ERROR = 5;//失败

    protected $guarded = ['id'];
    protected $table = 'user_withdraw';

    public function user() {
        return $this->belongsTo(User::class, 'uid', 'id');
    }
}
