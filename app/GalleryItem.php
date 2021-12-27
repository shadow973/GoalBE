<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $fillable = [
        'filename',
        'filename_webp',
        'image_x',
        'image_y',
        'filename_preview',
        'type',
        'title',
        'show_in_video_gallery',
    ];

    public function videoGalleryCategories(){
        return $this->belongsToMany(VideoGalleryCategory::class, 'video_gallery_category__gallery_item', 'gallery_item_id', 'vgc_id');
    }
}
