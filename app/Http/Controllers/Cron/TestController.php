<?php

namespace App\Http\Controllers\Cron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function convertSlug(){
        $res = \App\Article::where('id','>', 124683)->get();

        foreach ($res as $v){
            $v->slug = url_slug($v->title,['transliterate' => true]) ;
            $v->save();
            echo $v->id.' - '.$v->slug.PHP_EOL;
            // break;
        }

    }


    public function convertSlugCategory(){
        $res = \App\Category::where('id','>', 0)->get();

        foreach ($res as $v){
            $v->slug = url_slug($v->title,['transliterate' => true]) ;
            $v->save();
            echo $v->id.' - '.$v->slug.PHP_EOL;
            // break;
        }

    }

    public function convertSlugTeams(){
        $res = \App\Models\ApiTeams::where('id','>', 0)->get();

        foreach ($res as $v){
            $v->slug = url_slug($v->name,['transliterate' => true]) ;
            $v->save();
            echo $v->team_id.' '.$v->name.' - '.$v->slug.PHP_EOL;
            // break;
        }

    }

    public function convertSlugLeagues(){
        $res = \App\Models\ApiLeagues::where('id','>', 0)->get();

        foreach ($res as $v){
            $v->slug = url_slug($v->name,['transliterate' => true]) ;
            $v->save();
            echo $v->id.' '.$v->name.' - '.$v->slug.PHP_EOL;
            // break;
        }

    }

    public function convertSlugPlayers(){
        $res = \App\Models\ApiTeamsPlayers::where('id','>', 0)->get();

        foreach ($res as $v){
            $v->slug = url_slug($v->fullname,['transliterate' => true]) ;
            $v->save();
            echo $v->id.' '.$v->fullname.' - '.$v->slug.PHP_EOL;
            // break;
        }

    }
}
