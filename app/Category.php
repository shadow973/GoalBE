<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use File;

class Category extends Model
{
    use NodeTrait;
    protected $hidden = ['pivot'];

    protected $fillable = [
        'title',
        'image',
        'background_image',
        'parent_id',
        'color',
        'is_visible',
    ];

    public function delete(){
        $this->deleteImage();
        $this->deleteBackgroundImage();
        parent::delete();
    }

    public function deleteImage(){
        File::delete(public_path() . '/images/categories/' . $this->image);
    }

    public function deleteBackgroundImage(){
        File::delete(public_path() . '/images/category_backgrounds/' . $this->background_image);
    }

    public function articles(){
        return $this->belongsToMany(Article::class, 'article_category', 'category_id', 'article_id')
            ->where('is_draft', false)
            ->orderBy('publish_date', 'desc');
    }

    public function users(){
        return $this->belongsToMany(User::class, 'category_subscriptions', 'category_id', 'user_id');
    }

    public function lastThreeArticles(){
        return $this->belongsToMany(Article::class, 'article_category', 'category_id', 'article_id')
            ->where('is_draft', false)
            ->orderBy('publish_date', 'desc')
            ->take(4);
    }

    public function userSubscriptions(){
        return $this->belongsToMany(User::class, 'category_subscriptions', 'category_id', 'user_id');
    }

    public function articlesLimited(){
        return $this->belongsToMany(Article::class, 'article_category', 'category_id', 'article_id')
            ->where('is_draft', false)
            ->orderBy('publish_date', 'desc')
            ->select([
                'id',
                'title',
                'views',
                'is_blog_post',
                'is_draft',
                'main_gallery_item_id',
                'user_id',
                'publish_date',
                'updated_at',
            ]);
    }

    public function getShareLink() {
        return 'https://goal.ge/category/'.$this->id.'/'.$this->slug.'/1';
    }
}
