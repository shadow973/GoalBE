<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchComments extends Model
{
    protected $table = 'match_comments';

    protected $appends = ['liked_by'];

    public function getLikedByAttribute() {
        return $this->rates()->where('type', 'like')->distinct()->pluck('user_id')->toArray();
    }

    public function rates() {
        return $this->hasMany(MatchCommentsRate::class, 'comment_id');
    }

    public function replies(){
        return $this->hasMany(MatchCommentsReplies::class, 'comment_id');
    }

    public function author(){
        return $this->belongsTo(\App\User::class, 'user_id');
    }

}
