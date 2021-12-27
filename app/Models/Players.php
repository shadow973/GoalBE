<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Players extends Model
{
    protected $table = 'players';

    public function player()
    {
        return $this->hasOne('App\Models\ApiTeamsPlayers', 'player_id', 'player_id');
    }
}
