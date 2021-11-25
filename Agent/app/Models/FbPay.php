<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FbPay extends Model
{
    protected $title = '法币交易支付方式';

    protected $table = 'fb_pay';
    protected $guarded = ['id'];

    const PAYMENT_TYPE = [
        1 => '银行卡',
        2 => '支付宝',
        3 => '微信',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'uid','id');
    }
}
