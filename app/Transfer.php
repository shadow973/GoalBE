<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'date',
        'player_tag_id',
        'from_team_tag_id',
        'to_team_tag_id',
        'amount',
        'type',
    ];

    public function player(){
        return $this->belongsTo(Tag::class, 'player_tag_id');
    }

    public function fromTeam(){
        return $this->belongsTo(Tag::class, 'from_team_tag_id');
    }

    public function toTeam(){
        return $this->belongsTo(Tag::class, 'to_team_tag_id');
    }
}
