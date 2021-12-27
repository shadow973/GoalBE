<?php

namespace App\FACup;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'fa_cup_players';

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    public function homeMatches(){
        return $this->hasMany(Match::class, 'player_1_id');
    }

    public function awayMatches(){
        return $this->hasMany(Match::class, 'player_2_id');
    }

    public function groupStageGroup(){
        return $this->belongsToMany(Group::class, 'fa_cup_group_player', 'player_id', 'group_id')
            ->where('stage', 'group');
    }
}
