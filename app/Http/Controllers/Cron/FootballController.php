<?php

namespace App\Http\Controllers\Cron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Cron\ParserController;
use Sunra\PhpSimple\HtmlDomParser;

class FootballController extends ParserController
{
    public function parse(){

        $url = 'https://football.ua/newsarc/';
//        $html = $this->getDataFromUrl($url);
        $j = 1;
        for($i = 1; $i <= 10; $i++) {
            if($i > 1){
                $url_p = $url.'page'.$i.'.html';
            }else{
                $url_p = $url;
            }

            $html = new \Htmldom($url_p);

            $posts = $html->find('.archive-list li');

            foreach ($posts as $post) {
                if($post->class == 'authors'){
                    continue;
                }

                echo $j.PHP_EOL;
                $j++;

                $a = $post->find('a')[1];
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
        $post = new \Htmldom($url);

        $data = [
            'title' => '',
            'content' => '',
            'photo' => '',
            'url' => $url
        ];

        if (stripos($url, "champions.football.ua") !== false) {

            $data['title'] = $post->find('.article h1',0)->plaintext.PHP_EOL; //title
            $data['content'] = $post->find('.article p',2)->plaintext.PHP_EOL; //intro
            $data['content'] .= $post->find('.article .article_text',0)->innertext.PHP_EOL; //intro
            $data['photo'] =  $post->find('.article .article_photo img',0)->src.PHP_EOL; //intro
        }else{
            if(!isset($post->find('.author-article h1',0)->plaintext)){
                return;
            }
            $data['title'] = $post->find('.author-article h1',0)->plaintext.PHP_EOL; //title
            $data['content'] = $post->find('.author-article .intro',0)->plaintext.PHP_EOL; //intro
            $data['content'] .= $post->find('.author-article .article-text',0)->innertext.PHP_EOL; //intro
            $data['photo'] = $post->find('.author-article .article-photo img',0)->src.PHP_EOL; //intro
        }

        $data['site'] = 'football.ua';

        $this->saveArticle($data);
        echo "--------------------".PHP_EOL;
    }
}
