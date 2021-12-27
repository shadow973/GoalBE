<?php

namespace App\Http\Controllers\Cron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Football24Controller extends ParserController
{
    public function parse(){

        $url = 'https://football24.ua/ru/novosti_futbola_tag2/';
//        /
//        $html = $this->getDataFromUrl($url);
        $j = 1;
        for($i = 0; $i <= 10; $i++) {
            if($i > 0){
                $url_p = $url.'startrow'.$i.'0/';
            }else{
                $url_p = $url;
            }

            $html = new \Htmldom($url_p);

            $posts = $html->find('.news-list li');

            foreach ($posts as $post) {
//                if($post->class == 'authors'){
//                    continue;
//                }

                echo $j.PHP_EOL;
                $j++;

                $a = $post->find('a.news-image',0);
                if(empty($a)){
                    echo 'Continue ---'.PHP_EOL;
                    continue;
                }
                echo $a->href . PHP_EOL;
                if(\App\Models\ParsedArticles::where('source_url', $a->href)->count() > 0){
                    echo "break ------".PHP_EOL;
                    break 2;
                }
                $this->parsePost($a->href);
            }

        }

    }

    public function parsePost($url){
        $post = new \Htmldom('https://football24.ua/'.$url);

        $data = [
            'title' => '',
            'content' => '',
            'photo' => '',
            'url' => 'https://football24.ua/'.$url
        ];

        $data['title'] = $post->find('h1.news-title')[0]->plaintext.PHP_EOL; //title

        $contentPs = $post->find('.news-text p');
        $data['content'] = '';
        foreach ($contentPs as $p){
            if($post->class == 'by-the-way cke-markup'){
                continue;
            }
            $data['content'] .= '<p>' . $p->innertext . '</p>' .PHP_EOL;

        }
        $data['photo'] =  $post->find('.photo img',0)->src.PHP_EOL; //intro


        $data['site'] = 'football24.ua';

        $this->saveArticle($data);
        echo "--------------------".PHP_EOL;
    }
}
