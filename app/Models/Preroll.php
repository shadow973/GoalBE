<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preroll extends Model
{
    protected $guarded = [];

    public function getVideoUrlAttribute() {
        $url = $this->attributes['video_url'];

        if(strpos($url, 'http') === false) {
            $url = 'https:'.env('STORAGE_URL').$this->attributes['video_url'];
        }

        return $url;
    }

    public static function getPreroll() {
        $prerolls = Preroll::where('is_active', true)->get();

        $totalViews = $prerolls->sum('view_count');

        $availablePrerolls = collect();

        if($totalViews > 0) {
            foreach ($prerolls as $preroll) {
                $viewPercentage = $preroll->view_count / $totalViews * 100;
                if($viewPercentage < $preroll->percentage) $availablePrerolls->push($preroll);
            }
        }

        if($availablePrerolls->count() == 0) $availablePrerolls = $prerolls;

        return $availablePrerolls->random();
    }
}
