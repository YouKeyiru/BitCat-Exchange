<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentAssets extends Model
{
    protected $title = '代理商资产';
	
    protected $table = 'agent_assets';
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(AgentUser::class, 'uid','id');
    }
}
