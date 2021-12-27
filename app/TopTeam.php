<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopTeam extends Model
{
    protected $fillable = [
        'tag_id',
        'order',
    ];

    public function tag(){
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}
