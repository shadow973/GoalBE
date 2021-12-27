<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use File;

class Slide extends Model
{
    protected $fillable = [
        'article_id',
        'image',
        'link',
        'order',
    ];

    public function article(){
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function deleteImage(){
        if($this->image){
            File::delete(public_path() . '/' . $this->image);
        }
    }

    public function delete(){
        $this->deleteImage();
        parent::delete();
    }
}
