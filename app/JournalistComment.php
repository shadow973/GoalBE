<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JournalistComment extends Model
{
    protected $fillable = [
        'date',
        'comment',
        'journalist_id',
        'content_manager_id'
    ];

    public function contentManager(){
        return $this->belongsTo(User::class, 'content_manager_id', 'id');
    }
}
