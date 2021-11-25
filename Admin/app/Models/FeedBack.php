<?php

namespace App\Models;


class FeedBack extends Model
{
    protected $title = '反馈列表';

    protected $table = 'feedbacks';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }
}
