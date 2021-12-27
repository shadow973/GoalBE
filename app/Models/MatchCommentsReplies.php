<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchCommentsReplies extends Model
{
    protected $table = 'match_comment_replies';

    public function author(){
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    protected $appends = ['liked_by'];

    public function getLikedByAttribute() {
        return $this->rates()->where('type', 'like')->distinct()->pluck('user_id')->toArray();
    }

    public function rates() {
        return $this->hasMany(MatchCommentsReplyRate::class, 'comment_id');
    }
}
