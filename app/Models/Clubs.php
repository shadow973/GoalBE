<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clubs extends Model
{
    protected $table = 'clubs';

    public function tag()
    {
        return $this->hasOne('App\Tag', 'id', 'tag_id');
    }

    public function team()
    {
        return $this->hasOne('App\Models\ApiTeams', 'team_id', 'team_id');
    }
}
