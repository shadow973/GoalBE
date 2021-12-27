<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiTeamLeagues extends Model
{
    protected $fillable = ['league_id', 'team_id', 'season_id', 'stats'];
    public $timestamps = false;

    public function leagues() {
        return $this->belongsTo(ApiLeagues::class, 'league_id', 'league_id');
    }
}
