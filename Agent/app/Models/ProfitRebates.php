<?php

namespace App\Models;

use App\User;
use App\Models\AgentUser;
use Illuminate\Database\Eloquent\Model;

class ProfitRebates extends Model
{
    protected $title = '盈亏返佣';
	
    protected $table = 'profit_rebates';
    protected $guarded = ['id'];

    public function from() {
        return $this->belongsTo(User::class, 'from_uid','id');
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
