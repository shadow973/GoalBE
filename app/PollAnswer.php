<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model
{
    protected $fillable = [
        'answer',
        'poll_id',
    ];

    public function answerers(){
        return $this->belongsToMany(User::class, 'poll_answer_user', 'poll_answer_id', 'user_id');
    }
}
