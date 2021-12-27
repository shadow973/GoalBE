<?php

namespace App;

use App\Models\CommentsReplyRate;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $fillable = [
        'content',
    ];

    protected $appends = ['liked_by'];

    public function rates() {
        return $this->hasMany(CommentsReplyRate::class, 'comment_id');
    }

    public function getLikedByAttribute() {
        return $this->rates()->where('type', 'like')->distinct()->pluck('user_id')->toArray();
    }

    public function author(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
