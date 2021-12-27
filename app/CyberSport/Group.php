<?php

namespace App\CyberSport;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'cs_groups';

    protected $fillable = [
        'name',
        'stage',
        'date',
        'room',
    ];

    public $timestamps = false;

    public function players(){
        return $this->belongsToMany(Player::class, 'cs_group_player', 'group_id', 'player_id')
//            ->orderBy('cs_group_player.order')
            ->withPivot('advances_to_next_stage', 'order');
    }
}
