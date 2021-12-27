<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    protected $table = 'league_l';

    public function league()
    {
        return $this->hasOne('App\Models\ApiLeagues', 'league_id', 'league_id');
    }
}
