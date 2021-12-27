<?php

namespace App;

use App\Models\CommentsRate;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'content',
    ];

    protected $appends = ['liked_by'];

    public function getLikedByAttribute() {
        return $this->rates()->where('type', 'like')->distinct()->pluck('user_id')->toArray();
    }

    public function rates() {
        return $this->hasMany(CommentsRate::class, 'comment_id');
    }

    public function replies(){
        return $this->hasMany(Reply::class, 'comment_id');
    }

    public function author(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function article(){
        return $this->belongsTo(Article::class, 'article_id');
    }
}
