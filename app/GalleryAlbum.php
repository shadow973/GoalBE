<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GalleryAlbum extends Model
{
    protected $fillable = [
        'title',
    ];

    public function galleryItems(){
        return $this->hasMany(GalleryItem::class, 'album_id');
    }

    public static function generateShortcode($data)
    {
    	$album = \App\GalleryAlbum::find($data['id']);

    	if(empty($album)){
    		return 'Album Not Found !';
        }
        
        $count = 0;
        $html = "";
        $html .= '<div class="imageGallery">';
           foreach($album->galleryItems as $album_items) {
                $count++;
                $html .='<a class="GalleryImageItem" data-options="{"thumbs: { autoStart: false"}" data-fancybox="image" href="//storage.goal.ge/'.$album_items->filename_preview.'" >
                            <img src="//storage.goal.ge/'.$album_items->filename_preview.'" alt=""/>
                        </a>'; 
           }         
        $html .= '</div>';

        // $html .= '<script>jQuery(".GalleryImageItem").fancybox();</script>';

    	return $html;
    }
}


