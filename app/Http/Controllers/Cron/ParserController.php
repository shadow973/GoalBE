<?php

namespace App\Http\Controllers\Cron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ParserController extends Controller
{

    /*
    football.ua +
    Football24.ua
    sports.ru
    sport.ua +
     */

    public function getDataFromUrl($url, $metod="get", $data = []){
        $ch = curl_init();


        if($metod == 'post'){
            curl_setopt( $ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt( $ch, CURLOPT_URL, $url.http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        curl_close ($ch);

        return $server_output;
    }

    public function saveArticle($data){
        $article = new \App\Models\ParsedArticles();
        $article->title = $data['title'];
        $article->content = $data['content'];
        $article->photo = $data['photo'];
        $article->source_url = $data['url'];
        $article->site = $data['site'];
        $article->save();
    }

}
