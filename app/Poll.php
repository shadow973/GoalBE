<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = [
        'question',
        'user_id',
    ];

    public function answers(){
        return $this->hasMany(PollAnswer::class, 'poll_id');
    }
}
