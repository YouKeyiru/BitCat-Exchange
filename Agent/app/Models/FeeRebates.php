<?php

namespace App\Models;

use App\User;
use App\Models\AgentUser;
use Illuminate\Database\Eloquent\Model;

class FeeRebates extends Model
{
    protected $title = '手续费返佣';
	
    protected $table = 'fee_rebates';
    protected $guarded = ['id'];

    public function from() {
        return $this->belongsTo(User::class, 'from_uid','id');
    }

    public function recommend() {
        return $this->belongsTo(User::class, 'recommend_id','id');
    }

    public function staff() {
        return $this->belongsTo(AgentUser::class, 'staff_id','id');
    }

    public function agent() {
        return $this->belongsTo(AgentUser::class, 'agent_id','id');
    }

    public function unit() {
        return $this->belongsTo(AgentUser::class, 'unit_id','id');
    }
    
    public function center() {
        return $this->belongsTo(AgentUser::class, 'center_id','id');
    }
}
