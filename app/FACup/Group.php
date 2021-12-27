<?php

namespace App\FACup;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'fa_cup_groups';

    protected $fillable = [
        'name',
        'stage',
        'date',
        'room',
    ];

    public $timestamps = false;

    public function players(){
        return $this->belongsToMany(Player::class, 'fa_cup_group_player', 'group_id', 'player_id')
            ->withPivot('advances_to_next_stage', 'order');
    }
}
