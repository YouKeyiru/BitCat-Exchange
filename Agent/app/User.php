<?php

namespace App;

use App\Models\AgentUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $guarded = ['id'];

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
