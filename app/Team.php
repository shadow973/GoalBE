<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'league_id',
        'tag_id',
        'games',
        'points',
    ];

    public function tag(){
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}
