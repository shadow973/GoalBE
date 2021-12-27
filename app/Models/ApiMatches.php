<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApiMatches extends Model
{
    protected $table = 'api_matches';
    protected $connection =  LIVESCORE_CONNECTION;

    public function localteam(){
        return $this->hasOne('App\Models\ApiTeams', 'team_id', 'localteam_id');
    }

    public function article(){
        return $this->hasMany('App\Article', 'match_id', 'match_id');
    }


    public function visitorteam(){
        return $this->hasOne('App\Models\ApiTeams', 'team_id', 'visitorteam_id');
    }

    public function localteam_mini(){
        return $this->hasOne('App\Models\ApiTeams', 'team_id', 'localteam_id')
        ->addSelect(['team_id','name', 'short_code', 'logo_path', 'country_id']);
    }

    public function visitorteam_mini(){
        return $this->hasOne('App\Models\ApiTeams', 'team_id', 'visitorteam_id')
        ->addSelect(['team_id','name', 'short_code', 'logo_path', 'country_id']);
    }

    public function getRates(){
        $rate = \App\Models\MatchRate::select(DB::raw('answer, count(answer) as cnt'))->where('match_id', $this->match_id)->groupBy('answer')->get();

        return $rate;
    }
}
