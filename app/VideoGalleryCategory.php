<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class VideoGalleryCategory extends Model
{
    use NodeTrait;

    protected $fillable = [
        'title',
        'parent_id',
    ];
}
