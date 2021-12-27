<?php

namespace App\FACup;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    protected $table = 'fa_cup_matches';

    protected $fillable = [
        'player_1_id',
        'player_2_id',
        'player_1_score',
        'player_2_score',
        'stage',
        'datetime',
        'status',
        'room',
    ];

    public $timestamps = false;

    public function playerOne(){
        return $this->hasOne(Player::class, 'id', 'player_1_id');
    }

    public function playerTwo(){
        return $this->hasOne(Player::class, 'id', 'player_2_id');
    }
}
