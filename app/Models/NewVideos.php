<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewVideos extends Model
{
    protected $table = 'new_videos';

    public static function generateShortcode($data)
    {
    	$video = \App\Models\NewVideos::find($data['id']);

    	if(empty($video)){
    		return 'Video Not Found !';
    	}

    	if(!empty($video->embed)){
    		return $video->embed;
    	}

    	/*$html = "";
    	$html .='<video controls  poster="'.env('STORAGE_URL').$video->video_img.'">';
		$html .='<source src="'.env('STORAGE_URL').$video->video_url.'" type="video/mp4">';
		$html .='</video>';*/

		$html = "";
    	$html .='<iframe allowfullscreen="allowfullscreen" src="https://v2.api.goal.ge/news-video/'.$video->id.'" width="100%" height="100%"></iframe>';
//    	$html .='<iframe src="'.env('APP_URL').'/news-video/'.$video->id.'" width="100" height="100"></iframe>';

    	return $html;
    }
}
