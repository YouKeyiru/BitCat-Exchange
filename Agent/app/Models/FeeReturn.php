<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder whereOrderNo($order_no)
 * Class FbSell
 * @package App\Models
 */
class FeeReturn extends Model
{
    protected $title = '手续费返佣';

    protected $table = 'fee_return';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'to_uid', 'id');
    }

    public function from()
    {
        return $this->belongsTo(User::class, 'from_uid', 'id');
    }

    public function to()
    {
        return $this->belongsTo(User::class, 'to_uid', 'id');
    }
}
