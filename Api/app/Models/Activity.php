<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;


/**
 * @method static Builder whereWid($wid)
 * Class Activity
 * @package App\Models
 */
class Activity extends Model
{
    //
    protected $title = '质押活动信息';

    protected $table = 'activity';
    protected $guarded = ['id'];

    public function wallet()
    {
        return $this->belongsTo(WalletCode::class, 'wid', 'id');
    }
}
