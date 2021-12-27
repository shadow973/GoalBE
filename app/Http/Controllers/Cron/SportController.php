<?php

namespace App\Http\Controllers\Cron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SportController extends ParserController
{
    public function parse()
    {

        $url = 'https://sport.ua/news';
//        $html = $this->getDataFromUrl($url);
        $j = 1;
        for ($i = 1; $i <= 10; $i++) {
            if ($i > 1) {
                $url_p = $url . '?page=' . $i;
            } else {
                $url_p = $url;
            }

            $html = new \Htmldom($url_p);

            $posts = $html->find('#archive-news-line .hentry');

            foreach ($posts as $post) {
                echo $j . PHP_EOL;
                $j++;

                $a = $post->find('.hentry__pic a')[0];
                echo $a->href . PHP_EOL;
                if (\App\Models\ParsedArticles::where('source_url', $a->href)->count() > 0) {
                    echo "break ------" . PHP_EOL;
                    break 2;
                }
                $this->parsePost($a->href);
            }

        }

    }

    public function parsePost($url)
    {
        $post = new \Htmldom($url);

        $data = [
            'title' => '',
            'content' => '',
            'photo' => '',
            'url' => $url
        ];

//        print_r($post->find('.content figure img'));

        $data['title'] = $post->find('.news-title h1', 0)->plaintext . PHP_EOL; //title
        $data['content'] = $post->find('#news_text',0)->innertext . PHP_EOL; //intro
        $data['photo'] = $post->find('.content figure img', 0)->src . PHP_EOL; //intro
        $data['site'] = 'sport.ua';

        $this->saveArticle($data);
        echo "--------------------" . PHP_EOL;
    }

}
