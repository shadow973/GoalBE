<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    protected $table = 'team_subscriptions';

    public function team()
    {
        return $this->hasOne('App\Models\ApiTeams', 'team_id', 'team_id');
    }

    public function user(){
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
