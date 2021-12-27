<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use File;

class Tag extends Model
{
    protected $fillable = [
        'title',
        'image',
        'background_image',
    ];

    public function delete()
    {
        $this->deleteImage();
        $this->deleteBackgroundImage();
        parent::delete();
    }

    public function deleteImage()
    {
        File::delete(public_path() . '/images/tags/' . $this->image);
    }

    public function deleteBackgroundImage()
    {
        File::delete(public_path() . '/images/tag_backgrounds/' . $this->background_image);
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tag', 'tag_id', 'article_id')
            ->where('is_draft', false)
            ->orderBy('created_at', 'desc');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tag_subscriptions', 'tag_id', 'user_id');
    }

    public function lastThreeArticles()
    {
        return $this->belongsToMany(Article::class, 'article_tag', 'tag_id', 'article_id')
            ->where('is_draft', false)
            ->orderBy('publish_date', 'desc')
            ->take(4);
    }
}
