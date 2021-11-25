<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWithdraw extends Model
{
    protected $table = 'agent_withdraw';
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(AgentUser::class, 'uid','id');
    }

    public function createSN()
    {
        return 'AGENTWITH'.date('YmdHis') . $this->id . mt_rand(1000, 9999);
    }
}
