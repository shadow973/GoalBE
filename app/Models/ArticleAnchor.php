<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleAnchor extends Model
{
    protected $fillable = ['article_id', 'word', 'link', 'max_number', 'open_new_tab'];
}
