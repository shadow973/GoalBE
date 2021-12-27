<?php

namespace App\Http\Controllers;

use App\Models\NewVideos;
use App\Models\Preroll;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function videoPlayer(Request $request, NewVideos $video = null) {
        if((!$request->url || empty($request->url)) && $video === null) return '';

        if(strpos($video->video_img,'http://') !== false || strpos($video->video_img,'https://') !== false)
            $image = $video->video_img;
        else $image = env('STORAGE_URL').'/'.$video->video_img;

        if($video !== null) {
            if(strpos($video->video_url,'http://') !== false || strpos($video->video_url,'https://') !== false)
            $url = $video->video_url;
            else $url = env('STORAGE_URL').$video->video_url;
        } else {
            $url = htmlentities(htmlspecialchars($request->url));
        }

        $preroll = Preroll::getPreroll();
        $preroll->view_count = $preroll->view_count + 1;
        $preroll->save();

        return view('video-player', compact('url', 'image', 'preroll'));
    }

    public function generateXml(Request $request) {
        $preroll = Preroll::findOrFail($request->preroll_id);

        $xmlContent = file_get_contents(env('APP_URL').'/preroll-template.xml');

        $xmlContent = str_replace('%name%', $preroll->name, $xmlContent);
        $xmlContent = str_replace('%impression_tracking%', $preroll->impression_tracking_url, $xmlContent);
        $xmlContent = str_replace('%click_tracking%', $preroll->click_tracking_url, $xmlContent);
        $xmlContent = str_replace('%url%', $preroll->video_url, $xmlContent);

        return response($xmlContent, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getSubmenuItems() {
        return [
          [
              'title' => "ქართული",
              'id' => 319,
              'type' => 'league',
          ],
          [
              'title' => "ინგლისი",
              'id' => 8,
              'type' => 'league',
          ],
          [
              'title' => "ესპანეთი",
              'id' => 564,
              'type' => 'league',
          ],
          [
              'title' => "იტალია",
              'id' => 384,
              'type' => 'league',
          ],
          [
              'title' => "გერმანია",
              'id' => 82,
              'type' => 'league',
          ],
          [
              'title' => "ჩემპიონთა ლიგა",
              'id' => 2,
              'type' => 'league',
          ]
        ];
    }

    public function signInWithApple() {
        return view('sign-in-apple');
    }
}
