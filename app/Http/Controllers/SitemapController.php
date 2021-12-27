<?php

namespace App\Http\Controllers;

use App\Article;
use App\Category;
use App\Models\ApiLeagues;
use App\Models\ApiMatches;
use App\Models\ApiTeams;
use App\Models\ApiTeamsPlayers;
use DOMAttr;
use DOMDocument;
use Illuminate\Http\Request;
use App\Models\Players;
use JWTAuth;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    private $path;
    
    const STATIC_URLS = [
        'https://goal.ge/',
        'https://goal.ge/livescore',
        'https://goal.ge/my-teams',
        'https://goal.ge/cxrilebi',
        'https://goal.ge/contact',
        'https://goal.ge/ads',
        'https://goal.ge/day-news/1',
    ];

    public function __construct() {
        $this->path = '/home/goal/old1';
    }

    public function generateAll() {
        $this->generateLeaguesSitemap();
        dump('Leagues sitemap generated'.PHP_EOL);
        $this->generateTeamsSitemap();
        dump('Teams sitemap generated'.PHP_EOL);
        $this->generatePlayerSitemap();
        dump('Players sitemap generated'.PHP_EOL);
        $this->generateCategoriesSitemap();
        dump('Categories sitemap generated'.PHP_EOL);
        $this->generateNewsSitemap();
        dump('News sitemap generated'.PHP_EOL);
        $this->generateStaticUrlSitemap();
        dump('Static url sitemap generated'.PHP_EOL);

        $this->generateMainSitemap();
        dump('Main sitemap generated'.PHP_EOL);
    }

    private function getSitemapUrl($sitemapPath) {
        $split = explode('/', $sitemapPath);
        $sitemapName = end($split);

        return 'https://goal.ge/'.$sitemapName;
    }

    public function generateMainSitemap() {
        $sitemaps = glob($this->path.'/*.xml');

        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xmlFileName = $this->path.'/sitemap.xml';
        $root = $dom->createElement('sitemapindex');
        $rootAttr = new DOMAttr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $root->setAttributeNode($rootAttr);

        foreach ($sitemaps as $sitemap) {
            if(strpos($sitemap, 'sitemap.xml') === false) {
                $url = $this->getSitemapUrl($sitemap);
                $urlNode = $dom->createElement('sitemap');
                $urlNodeLoc = $dom->createElement('loc', $url);
                $urlNode->appendChild($urlNodeLoc);
                $urlNodeLastMod = $dom->createElement('lastmod', (new \DateTime())->format('Y-m-d'));
                $urlNode->appendChild($urlNodeLastMod);
                $root->appendChild($urlNode);
            }
        }

        $dom->appendChild($root);
        $dom->save($xmlFileName);
    }
    
    public function generateStaticUrlSitemap() {
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xmlFileName = $this->path.'/sitemap_general.xml';
        $root = $dom->createElement('urlset');
        $rootAttr = new DOMAttr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $root->setAttributeNode($rootAttr);

        foreach (self::STATIC_URLS as $url) {
            $urlNode = $dom->createElement('url');
            $urlNodeLoc = $dom->createElement('loc', $url);
            $urlNode->appendChild($urlNodeLoc);

            $urlMobile = $dom->createElement('xhtml:link');
            $relAttr = new DOMAttr('rel', 'alternate');
            $mediaAttr = new DOMAttr('media', 'only screen and (max-width: 992px)');
            $urlMobile->setAttributeNode($relAttr);
            $urlMobile->setAttributeNode($mediaAttr);
            $urlAttr = new DOMAttr('href', str_replace('goal.ge', 'm.goal.ge', $url));
            $urlMobile->setAttributeNode($urlAttr);
            $urlNode->appendChild($urlMobile);

            $urlNodeLastMod = $dom->createElement('lastmod', (new \DateTime())->format('Y-m-d'));
            $urlNode->appendChild($urlNodeLastMod);
            $urlNodePriority = $dom->createElement('priority', 1);
            $urlNode->appendChild($urlNodePriority);
            $root->appendChild($urlNode);
        }

        $dom->appendChild($root);
        $dom->save($xmlFileName);
    }

    public function generateLeaguesSitemap() {
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xmlFileName = $this->path.'/sitemap_leagues.xml';
        $root = $dom->createElement('urlset');
        $rootAttr = new DOMAttr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $root->setAttributeNode($rootAttr);

        $items = ApiLeagues::select(['league_id','slug'])->get();
        foreach ($items as $item) {
            $urlNode = $dom->createElement('url');
            $url = $item->getShareLink();
            $urlNodeLoc = $dom->createElement('loc', $url);
            $urlNode->appendChild($urlNodeLoc);


            $urlMobile = $dom->createElement('xhtml:link');
            $relAttr = new DOMAttr('rel', 'alternate');
            $mediaAttr = new DOMAttr('media', 'only screen and (max-width: 992px)');
            $urlMobile->setAttributeNode($relAttr);
            $urlMobile->setAttributeNode($mediaAttr);
            $urlAttr = new DOMAttr('href', str_replace('goal.ge', 'm.goal.ge', $url));
            $urlMobile->setAttributeNode($urlAttr);
            $urlNode->appendChild($urlMobile);


            $urlNodeLastMod = $dom->createElement('lastmod', (new \DateTime())->format('Y-m-d'));
            $urlNode->appendChild($urlNodeLastMod);
            $urlNodePriority = $dom->createElement('priority', 1);
            $urlNode->appendChild($urlNodePriority);
            $root->appendChild($urlNode);
        }

        $dom->appendChild($root);
        $dom->save($xmlFileName);
    }

    public function generateTeamsSitemap() {
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xmlFileName = $this->path.'/sitemap_teams.xml';
        $root = $dom->createElement('urlset');
        $rootAttr = new DOMAttr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $root->setAttributeNode($rootAttr);

        $items = ApiTeams::select(['team_id','slug'])->get();
        foreach ($items as $item) {
            $urlNode = $dom->createElement('url');
            $url = $item->getShareLink();
            $urlNodeLoc = $dom->createElement('loc', $url);
            $urlNode->appendChild($urlNodeLoc);

            $urlMobile = $dom->createElement('xhtml:link');
            $relAttr = new DOMAttr('rel', 'alternate');
            $mediaAttr = new DOMAttr('media', 'only screen and (max-width: 992px)');
            $urlMobile->setAttributeNode($relAttr);
            $urlMobile->setAttributeNode($mediaAttr);
            $urlAttr = new DOMAttr('href', str_replace('goal.ge', 'm.goal.ge', $url));
            $urlMobile->setAttributeNode($urlAttr);
            $urlNode->appendChild($urlMobile);


            $urlNodeLastMod = $dom->createElement('lastmod', (new \DateTime())->format('Y-m-d'));
            $urlNode->appendChild($urlNodeLastMod);
            $urlNodePriority = $dom->createElement('priority', 1);
            $urlNode->appendChild($urlNodePriority);
            $root->appendChild($urlNode);
        }

        $dom->appendChild($root);
        $dom->save($xmlFileName);
    }

    public function generatePlayerSitemap() {
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xmlFileName = $this->path.'/sitemap_players.xml';
        $root = $dom->createElement('urlset');
        $rootAttr = new DOMAttr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $root->setAttributeNode($rootAttr);

        $items = ApiTeamsPlayers::where('status_id', '>', 0)->select(['player_id','slug'])->get();
        foreach ($items as $item) {
            $urlNode = $dom->createElement('url');
            $url = $item->getShareLink();
            $urlNodeLoc = $dom->createElement('loc', $url);
            $urlNode->appendChild($urlNodeLoc);

            $urlMobile = $dom->createElement('xhtml:link');
            $relAttr = new DOMAttr('rel', 'alternate');
            $mediaAttr = new DOMAttr('media', 'only screen and (max-width: 992px)');
            $urlMobile->setAttributeNode($relAttr);
            $urlMobile->setAttributeNode($mediaAttr);
            $urlAttr = new DOMAttr('href', str_replace('goal.ge', 'm.goal.ge', $url));
            $urlMobile->setAttributeNode($urlAttr);
            $urlNode->appendChild($urlMobile);


            $urlNodeLastMod = $dom->createElement('lastmod', (new \DateTime())->format('Y-m-d'));
            $urlNode->appendChild($urlNodeLastMod);
            $urlNodePriority = $dom->createElement('priority', 1);
            $urlNode->appendChild($urlNodePriority);
            $root->appendChild($urlNode);
        }

        $dom->appendChild($root);
        $dom->save($xmlFileName);
    }

    public function generateCategoriesSitemap() {
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xmlFileName = $this->path.'/sitemap_news_categories.xml';
        $root = $dom->createElement('urlset');
        $rootAttr = new DOMAttr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $root->setAttributeNode($rootAttr);

        $items = Category::where('is_visible', true)->select(['id','slug'])->get();
        foreach ($items as $item) {
            $urlNode = $dom->createElement('url');
            $url = $item->getShareLink();
            $urlNodeLoc = $dom->createElement('loc', $url);
            $urlNode->appendChild($urlNodeLoc);

            $urlMobile = $dom->createElement('xhtml:link');
            $relAttr = new DOMAttr('rel', 'alternate');
            $mediaAttr = new DOMAttr('media', 'only screen and (max-width: 992px)');
            $urlMobile->setAttributeNode($relAttr);
            $urlMobile->setAttributeNode($mediaAttr);
            $urlAttr = new DOMAttr('href', str_replace('goal.ge', 'm.goal.ge', $url));
            $urlMobile->setAttributeNode($urlAttr);
            $urlNode->appendChild($urlMobile);


            $urlNodeLastMod = $dom->createElement('lastmod', (new \DateTime())->format('Y-m-d'));
            $urlNode->appendChild($urlNodeLastMod);
            $urlNodePriority = $dom->createElement('priority', 1);
            $urlNode->appendChild($urlNodePriority);
            $root->appendChild($urlNode);
        }

        $dom->appendChild($root);
        $dom->save($xmlFileName);
    }

    public function generateNewsSitemap() {
        $articleQuery = Article::where('is_draft', false)->select(['id','slug']);
        $splitCount = 50000;
        $sitemapCount = (int)($articleQuery->count() / $splitCount) + 1;

        for ($i = 0; $i < $sitemapCount; $i++) {
            $dom = new DOMDocument();
            $dom->encoding = 'utf-8';
            $dom->xmlVersion = '1.0';
            $dom->formatOutput = true;
            $xmlFileName = $this->path.'/sitemap_news_'.($i+1).'.xml';
            $root = $dom->createElement('urlset');
            $rootAttr = new DOMAttr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $root->setAttributeNode($rootAttr);

            $items = Article::where('is_draft', false)
                ->offset($i * $splitCount)
                ->limit($splitCount)
                ->select(['id','slug']);

            foreach ($items->get() as $item) {
                $urlNode = $dom->createElement('url');
                $url = $item->getShareLink();
                $urlNodeLoc = $dom->createElement('loc', $url);
                $urlNode->appendChild($urlNodeLoc);

                $urlMobile = $dom->createElement('xhtml:link');
                $relAttr = new DOMAttr('rel', 'alternate');
                $mediaAttr = new DOMAttr('media', 'only screen and (max-width: 992px)');
                $urlMobile->setAttributeNode($relAttr);
                $urlMobile->setAttributeNode($mediaAttr);
                $urlAttr = new DOMAttr('href', str_replace('goal.ge', 'm.goal.ge', $url));
                $urlMobile->setAttributeNode($urlAttr);
                $urlNode->appendChild($urlMobile);


                $urlNodeLastMod = $dom->createElement('lastmod', (new \DateTime())->format('Y-m-d'));
                $urlNode->appendChild($urlNodeLastMod);
                $urlNodePriority = $dom->createElement('priority', 1);
                $urlNode->appendChild($urlNodePriority);
                $root->appendChild($urlNode);
            }

            $dom->appendChild($root);
            $dom->save($xmlFileName);
        }
    }
}
