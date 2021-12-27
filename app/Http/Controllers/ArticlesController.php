<?php

namespace App\Http\Controllers;

use App\ArticleLike;
use App\ArticleView;
use App\Http\Requests\ArticleAddRequest;
use App\Http\Requests\ArticleUpdateRequest;
use App\Slide;
use App\Tag;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Article;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention;
use JWTAuth;
use Illuminate\Support\Facades\Cache;
use App\Models\Redis;
use App\GalleryItem;
use Illuminate\Support\Facades\DB;

class ArticlesController extends Controller
{
    protected $user;
    private $imageLocation = 'uploads/posts/';
    private $videoLocation = 'uploads/posts/';

    protected $request;

    public function __construct(Request $request)
    {
        try {
            $this->user = JWTAuth::parseToken()->toUser();
        } catch (\Exception $e) {

        }

        $this->request = $request;
    }

    public function news($request) {
        if (isset($request->is_admin)) {
            $articles = Article::with('mainGalleryItem', 'author', 'categories', 'matche', 'players', 'leagues')->withCount(['comments']);
        } else {
            $articles = Article::with(['mainGalleryItem',
                'categories' => function ($q) {
                    $q->select(['title', 'image']);
                },
                'teams' => function ($q) {
                    $q->select(['name', 'name_geo', 'logo_path']);
                },
                'players' => function ($q) {
                    $q->select(['article_player.player_id', 'common_name', 'nationality', 'image_path', 'slug']);
                },
                'leagues' => function ($q) {
                    $q->select(['api_leagues.league_id', 'api_leagues.name', 'api_leagues.name_geo']);
                },
                'matche'])->withCount(['comments']);

            if($request->match_news) {
                $articles = $articles->whereHas('matche');
            }
        }

        //$articles->addSelect(DB::raw('if (is_pinned > 0 and publish_date >= now() - INTERVAL 1 DAY, 1, 0) as is_pinned'));

        if (str_contains($request->get('options'), 'today')) {
            $articles->whereRaw('DATE(publish_date) = CURDATE()');
        }

        if (str_contains($request->get('options'), 'last-3-days')) {
            $articles->whereRaw('publish_date >= (CURDATE() - INTERVAL 3 DAY)');
        }

        if (str_contains($request->get('options'), 'news-only')) {
            $articles->whereHas('mainGalleryItem', function ($q) {
                $q->where('type', 'image');
            });
        }

        if (str_contains($request->get('options'), 'video')) {
            $articles->whereHas('mainGalleryItem', function ($q) {
                $q->where('type', 'video');
            });
        }

        if (str_contains($request->get('options'), 'BLOG_POSTS_ONLY')) {
            $articles->where('is_blog_post', true);
        } elseif (str_contains($request->get('options'), 'ARTICLES_ONLY')) {
            $articles->where('is_blog_post', false);
        }

        if ($request->has('q')) {
            $articles->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->get('q')}%")
                    ->orWhere('content', 'LIKE', "%{$request->get('q')}%");
            });
        }

        if ($request->has('date')) {
            $articles->where(function ($q) use ($request) {
                $q->whereDate('publish_date', '=', $request->get('date'));
            });
        }

        if ($request->has('user_id')) {
            $articles->where('user_id', $request->get('user_id'));
        }


        if ($request->has('category_id')) {
            $articles->whereHas('categories', function ($q) use ($request) {
                $q->where('id', $request->get('category_id'));
            });
        }

        if (!$request->has('include_unpublished')) {
            $articles->where('is_draft', false);
        } else {
            if ($this->user && ($this->user->can('articles_crud') || $this->user->hasRole('admin'))) {
                $articles->where(function ($query) {
                    $query->where('is_draft', true)
                        ->orWhere('is_draft', false);
                });
            }
        }


        if ($request->has('minimal')) {
            $articles->select(DB::raw('
                id, title, user_id, main_gallery_item_id, views, publish_date, is_pinned, slug, (CASE
                    WHEN articles.is_pinned = 1 and publish_date >= NOW() - INTERVAL 1 DAY THEN 1
                    ELSE 0
                    END) AS pinned'));
        } else {
            $articles->select(DB::raw('
                articles.id, title, articles.content, articles.user_id, main_gallery_item_id, views, is_blog_post, is_draft, publish_date,
                video_gallery_id, meta, old_id, match_id, main_video, slug, source_url, watermark, is_pinned, transfer_status, (CASE
                    WHEN articles.is_pinned = 1 and publish_date >= NOW() - INTERVAL 1 DAY THEN 1
                    ELSE 0
                    END) AS pinned'));
        }

        if ($request->has('sort')) {
            if ($request->get('sort') == 'views') {
                $articles->orderBy('views', 'desc');
            } else {
                $articles->orderByDesc('pinned')->orderBy('publish_date', 'desc');
            }
        } else {
            $articles->orderByDesc('pinned')->orderBy('publish_date', 'desc');
        }

        if (str_contains($request->get('options'), 'paginate')) {
            $limit = $request->per_page;

            $excepts = $request->excepts && json_decode($request->excepts) ? json_decode($request->excepts) : [];

            if(isset($request->per_page) && intval($request->per_page) <= 30 && intval($request->per_page) > 0){
                $limit = intval($request->per_page);
            }

            if(count($excepts) > 0) $articles->whereNotIn('id', $excepts);

            return $articles->paginate($limit);
        }

        return $articles->get();
    }

    public function getNews($articleId) {
        $article = Article::withCount('comments')->findOrFail($articleId)->load('tags', 'categories', 'mainGalleryItem', 'teams', 'leagues');
        // Article Views

        if(is_int($articleId))
            ArticleView::insert([
                'article_id' => $articleId,
                'datetime' => (new \DateTime()),
            ]);

        // End of article views

        $next = Article::select('title',
            'user_id',
            'created_at',
            'updated_at',
            'main_gallery_item_id',
            'views',
            'is_blog_post',
            'is_draft',
            'is_pinned',
            'transfer_status',
            'publish_date')
            ->where('publish_date', '>', $article->publish_date)
            ->where('is_draft', '=', 1)
            ->first();
        $previous = Article::select('title',
            'user_id',
            'created_at',
            'updated_at',
            'main_gallery_item_id',
            'views',
            'is_blog_post',
            'is_draft',
            'is_pinned',
            'transfer_status',
            'publish_date')
            ->where('publish_date', '<', $article->publish_date)
            ->where('is_draft', '>', 0)
            ->first();
        /*$next = null;
        $previous = null;*/

        $article->meta = empty($article->meta)? json_decode('{}'):json_decode( $article->meta);

        $article->nextArticle = $next;
        $article->previousArticle = $previous;
        $article->players = $article->getPlayers();
        $article->leagues = $article->getLeagues();
        $article->teams = $article->getTeams();
        $article->hashtags = $article->getHashtags();
        $article->shareLink = $article->getShareLink();
        $article->tagged = $article->getTagged();

        if (!isset($this->request->isedit)) {
            $article->content = \App\Models\ShortCode::ContentShortcode($article->content);
            $article->main_video = \App\Models\ShortCode::ContentShortcode($article->main_video);
        }

        return $article;
    }

    /**
     * @return mixed
     */
    public function index(Request $request)
    {
        $request_all = $request->all();

        $cashe_index = implode('__', array_map(
            function ($v, $k) { return sprintf("%s=%s", $k, $v); },
            $request_all,
            array_keys($request_all)
        ));
        $cache_time = 3000;
        $cache_prefix = 'articles_index_';
        $tags = ['articles', 'article-list'];
        if($request->category_id) $tags[] = 'category-'.$request->category_id;

        if (isset($request->is_admin)) {
            return $this->news($request);
        }

        // print_r($cashe_index );
        // die;

        if($this->request->clear_cache == 1) {
            Cache::tags($tags)->flush();
        }

        $articles_res = Cache::tags($tags)->remember($cache_prefix . $cashe_index, $cache_time, function () use ($request) {

            // return ['test1'.time()];

            // return ['test'];

            return $this->news($request);

        });

        return $articles_res;
    }

    public function getStatsByUser()
    {
        $statsByUserData = Article::selectRaw('count(*) as cnt, user_id, sum(views) as user_views')->where('is_draft', 0)->groupBy('user_id')->orderBy('user_views', 'desc')->get();

        $statsByUser = [];
        foreach ($statsByUserData as $v) {
            $statsByUser[$v->user_id] = $v;
        }

        $usersByArticlesData = \App\User::whereIn('id', function($q){
            $q->select('user_id')->from('articles')->groupBy('user_id');
        })->get();

        foreach ($usersByArticlesData as $u) {
            $statsByUser[$u->id]['user'] =$u;
        }


        return $statsByUser;
    }

    public function getStatsByUserByDate(Request $request)
    {
        $from_date = date($request->get('from_date'));
        $to_date = date($request->get('to_date'));
        $statsByUserData = Article::selectRaw('count(*) as cnt, user_id, sum(views) as user_views')->where('is_draft', 0)->whereBetween('publish_date', [$from_date, $to_date])->groupBy('user_id')->orderBy('user_views', 'desc')->get();

        $statsByUser = [];
        foreach ($statsByUserData as $v) {
            $statsByUser[$v->user_id] = $v;
        }

        $usersByArticlesData = \App\User::whereIn('id', function($q){
            $q->select('user_id')->from('articles')->groupBy('user_id');
        })->get();

        foreach ($usersByArticlesData as $u) {
            $statsByUser[$u->id]['user'] =$u;
        }


        return $statsByUser;
    }


    public function getTopNews()
    {
        $news = Article::with('mainGalleryItem', 'author', 'categories', 'matche')
            ->where('created_at', '>=', (new \DateTime())->modify('-24 hours')->format('Y-m-d H:i:s'))
            ->orderBy('views', 'desc')->limit(10)->get();
        return $news;
    }

    public function todayViews(Request $request){
        $request_all = $request->all();

        // print_r($request_all );
        $cashe_index = implode('__', $request_all);
        $cache_time = 0;
        $cache_prefix = 'articles_views_index_';

        $articles_res = Cache::remember($cache_prefix . $cashe_index, $cache_time, function () use ($request) {
            // SELECT article_id, count(article_id) as cnt FROM `article_views` where datetime >= NOW() - INTERVAL 1 DAY GROUP BY article_id ORDER BY count(article_id) Desc
            $views = DB::select('SELECT article_id, count(article_id) as cnt FROM `article_views` where EXISTS (SELECT id FROM articles where id=article_id) and datetime >= NOW() - INTERVAL 1 DAY GROUP BY article_id ORDER BY count(article_id) Desc limit 12');

            $ids = [];
            foreach ($views as $v) {
                $ids[ $v->article_id ] = $v->cnt;
            }
            $articles = Article::whereIn('id', array_keys($ids))->with(['mainGalleryItem',
                'categories' => function ($q) {
                    $q->select(['title', 'image']);
                },
                'teams' => function ($q) {
                    $q->select(['name', 'logo_path']);
                },
                'matche']);


            if ($request->has('minimal')) {
                $articles->select(['id', 'title', 'views', 'main_gallery_item_id', 'slug']);
            }

            $articles = $articles->get();
            foreach ($articles as $v) {
                $viewvs_cnt = $ids[$v->id];
                $ids[$v->id] = $v;
                $ids[$v->id]->viewvs_cnt = $viewvs_cnt;
            }
            return array_values($ids);
        });

        return $articles_res;
    }

    public function search($s = false, Request $request)
    {
        if (!$s) {
            $s = $request->get('q');
        }

        $limit = (isset($request->per_page) && (int)$request->per_page < 30)? (int)$request->per_page : 30;

        $res = Article::with(['categories'])->orderBy('publish_date', 'desc')
            ->select(['id','title','main_gallery_item_id','slug'])
            ->with('mainGalleryItem')
            ->where(function ($q) use ($s) {
                $q->where('title', 'LIKE', "%{$s}%");
                // ->orWhere('content', 'LIKE', "%{$s}%");
            })
            // ->where('is_published', true)
            ->limit($limit)
            // ->toSql();
            ->get();

        return [ 'data' => $res ];
    }

    public function getSubscribed(Request $request)
    {
        if (!$this->user) {
            return abort(403);
        } else {
            $this->user->load('categorySubscriptions.articles.mainGalleryItem');
            $this->user->load('tagSubscriptions.articles.mainGalleryItem');

            $articles = [];
            $uniqueArticles = [];

            foreach ($this->user->categorySubscriptions as $category) {
                foreach ($category->articles as $article) {
                    $articles[] = $article;
                }
            }

            foreach ($this->user->tagSubscriptions as $tag) {
                foreach ($tag->articles as $article) {
                    $articles[] = $article;
                }
            }

            foreach ($articles as $article) {
                if (isset($uniqueArticles[$article->id])) {
                    continue;
                } else {
                    $uniqueArticles[$article->id] = $article;
                }
            }

            if ($request->get('type') == 'news-only') {
                $uniqueArticles = array_filter($uniqueArticles, function ($article) {
                    return $article->mainGalleryItem->type == 'image';
                });
            } elseif ($request->get('type') == 'videos') {
                $uniqueArticles = array_filter($uniqueArticles, function ($article) {
                    return $article->mainGalleryItem->type == 'video';
                });
            }

            $uniqueArticles = array_reverse(array_sort($uniqueArticles, function ($a, $b) {
                return $a['publish_date'];
            }));

            $uniqueArticles = array_values($uniqueArticles);

            $pageStart = $request->get('page', 1);
            $offSet = ($pageStart * 30) - 30;
            $itemsForCurrentPage = array_slice($uniqueArticles, $offSet, 30, true);

            return new LengthAwarePaginator(
                array_values($itemsForCurrentPage),
                count($uniqueArticles),
                30,
                Paginator::resolveCurrentPage(),
                ['path' => Paginator::resolveCurrentPath()]
            );
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(ArticleAddRequest $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $parsed_file = $request->parsed_file;

//        $data = $request->all();

        $data = [];
        $data['title'] = $request->title;
        $data['is_draft'] = isset($request->is_draft) ? $request->is_draft : false;
        $data['is_pinned'] = isset($request->is_pinned) ? $request->is_pinned : false;
        $data['slug'] = url_slug($request->title,['transliterate' => true]);
        $data['content'] = str_replace('&quot;', '"', $request->{'content'});
        $data['video_gallery_id'] = $request->video_gallery_id;
        $data['main_video'] = $request->main_video;
        $data['transfer_status'] = $request->transfer_status;

        if (isset($request->parsed_file)) {

            $image = Intervention::make($request->parsed_file);
            $fileName = str_random(32) . '.jpg';
            $fileNamePreview = str_random(32) . '.jpg';

            $image->encode('jpg', 90)
                ->backup();

            $imagePreview = $image->fit(800, 450, function ($constraint) {
                $constraint->upsize();
            }, 'top');

            Storage::disk(config('app.gallery_disk'))->put($this->imageLocation . $fileNamePreview, (string)$imagePreview->stream());

            $imageOriginal = $image->reset();

            if(isset($request->watermark)) {
                $imageOriginal = Article::addWatermark($imageOriginal, $request->watermark);
            }

            Storage::disk(config('app.gallery_disk'))->put($this->imageLocation . $fileName, (string)$imageOriginal->stream());

            $galleryItem = new GalleryItem([
                'filename' => $fileName,
                'filename_preview' => $fileNamePreview,
                'type' => 'image',
                'title' => $request->get('title', null),
            ]);

            $galleryItem->save();

            $data['main_gallery_item_id'] = $galleryItem->id;
        }else{
            $data['main_gallery_item_id'] = $request->main_gallery_item_id;
        }

        $data['match_id'] = $request->match_id;
        $data['source_url'] = $request->source_url;
        $data['content'] = str_replace('&quot;', '"', $data['content']);


        foreach ($data as $k => $v) {
            if (strpos($k, 'editor-') !== false) {
                unset($data[$k]);
            }
        }

        $article = new Article($data);
        $article->user_id = $this->user->id;
        $article->publish_date = $request->get('publish_date') ?: (new \DateTime);
        // $article->is_published = $request->get('is_published');



        if($request->watermark !== $article->watermark) {
            $article->watermark = in_array($request->watermark, ['hidden', 'top', 'bottom']) ? $request->watermark : 'hidden';
        }

        $article->save();


        $galleryItem = GalleryItem::find($data['main_gallery_item_id']);
        if($galleryItem) {
            $image = Article::addWatermarkToPath($this->imageLocation . $galleryItem->filename, config('app.gallery_disk'), $article->watermark);
            Storage::disk(config('app.gallery_disk'))->put($this->imageLocation . $galleryItem->filename, (string)$image->stream());
        }
//            GalleryController


        $cats = $request->get('categories');
        if (!empty($cats)) {
            $cats = explode(',', $cats);
        } else {
            $cats = [];
        }
        foreach ($cats as $cat) {
            Cache::tags(['category-'.$cat])->flush();
        }

        if(isset($request->leagues)) {
            $leaguesArr = explode(',', $request->leagues);
            foreach ($leaguesArr as $leagueId) {
                Cache::tags(['league-'.$leagueId])->flush();
            }
        }

        if(isset($request->teams)) {
            $teamsArr = explode(',', $request->teams);
            foreach ($teamsArr as $teamId) {
                Cache::tags(['team-'.$teamId])->flush();
            }
        }
//        foreach ($lea)

        $tgs = $request->get('tags', []);

        if (!empty($tgs)) {
            $tgs = explode(',', $tgs);
        } else {
            $tgs = [];
        }

        // print_r($cats);
        // die;

        $article->tags()->sync($tgs);
        $article->categories()->sync($cats);
        $article->players_sync($request->players);
        $article->leagues_sync($request->leagues);
        $article->teams_sync($request->teams);
        $article->saveHashtags($request->hashtags ? json_decode($request->hashtags, true) : null);


        /*
         * If add_to_slides is checked remove the last slide and prepend article as new slide to slide list.
         */
        if ($request->get('add_to_slides')) {
            $slideToDelete = Slide::orderBy('order', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($slideToDelete) {
                $slideToDelete->delete();
            }

            $firstSlide = Slide::orderBy('order')
                ->orderBy('created_at', 'desc')
                ->first();

            $slide = new Slide([
                'article_id' => $article->id,
                'order' => ($firstSlide ? $firstSlide->order - 1 : 0),
            ]);
            $slide->save();
        }

        //new tags
        if ($request->get('newTags')) {
            $newTagIds = [];
            $requestNewTags = $request->get('newTags');
            $currentTags = Tag::whereIn('title', $requestNewTags)->get();
            $requestNewTags = array_diff($requestNewTags, $currentTags->pluck('title')->all());

            foreach ($requestNewTags as $newTag) {
                $tag = new Tag([
                    'title' => $newTag
                ]);
                $tag->save();

                $newTagIds[] = $tag->id;
            }

            $newTagIdsMerge = array_merge($currentTags->pluck('id')->all(), $newTagIds);

            $article->tags()->attach($newTagIdsMerge);
        }
        //end of new tags

        Redis::ClearArticles();

        return $this->show($article->id);
    }

    public function showWithSlug(string $slug) {
        $article = Article::where('slug', $slug)->firstOrFail();

        return $this->show($article->id);
    }

    public function show($articleId)
    {
        $tmpArticle = Article::findOrFail($articleId);
        $tmpArticle->views += 1;
        $tmpArticle->save();

        $cache_time = 3000;
        $cache_prefix = 'articles_show_'.$articleId;
        if($this->user && $this->user->hasRole('admin')) {
            return $this->getNews($articleId);
        }
        $tags = ['articles', 'articles-show'];

        if($this->request->clear_cache) {
            Cache::tags($tags)->forget($cache_prefix);
        }

        $article = Cache::tags($tags)->remember($cache_prefix, $cache_time, function () use ($articleId) {
            $article = $this->getNews($articleId);

            if($article->created_at > (new \DateTime())->modify('-3 days')->format('Y-m-d'))
                return $article;

            return null;
        });

        if(!$article) {
            Cache::tags($tags)->forget($cache_prefix);
            $article = $this->getNews($articleId);
        }

        return $article;
    }

    /**
     * @param $articleId
     * @param Request $request
     * @return mixed
     */
    public function update($articleId, ArticleUpdateRequest $request)
    {
        // print_r($request->all());

        $data = [];
        $data['title'] = $request->title;
        $data['slug'] = url_slug($request->title,['transliterate' => true]);
        $data['content'] = str_replace('&quot;', '"', $request->{'content'});
        $data['video_gallery_id'] = $request->video_gallery_id;
        $data['main_video'] = $request->main_video;
        $data['main_gallery_item_id'] = $request->main_gallery_item_id;
        $data['match_id'] = $request->match_id;
        $data['is_draft'] = isset($request->is_draft) ? $request->is_draft : false;
        $data['is_pinned'] = isset($request->is_pinned) ? $request->is_pinned : false;
        $data['transfer_status'] = $request->transfer_status;

        // die;


        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $article = Article::findOrFail($articleId);

        $article->update($data);

        if(isset($request->watermark) &&
            ($request->watermark !== $article->watermark || $request->main_gallery_item_id !== $article->main_gallery_item_id)
            && $request->watermark !== 'hidden') {
            $galleryItem = GalleryItem::find($article->main_gallery_item_id);

            if($galleryItem) {
                $image = Article::addWatermarkToPath($this->imageLocation . $galleryItem->filename, 'sftp', $request->watermark);
                Storage::disk('sftp')->put($this->imageLocation . $galleryItem->filename, (string)$image->stream());
            }

            $article->watermark = in_array($request->watermark, ['hidden', 'top', 'bottom']) ? $request->watermark : 'hidden';
            $article->save();
        }

        // print_r($data);
        // die;

        // $article->is_published = $request->get('is_draft');
        // $article->save();


        $cats = $request->get('categories');
        if (!empty($cats)) {
            $cats = explode(',', $cats);
        } else {
            $cats = [];
        }

        $article->clearCategoryCache();
        foreach ($cats as $cat) {
            Cache::tags(['category-'.$cat])->flush();
        }

        $article->clearLeagueCache();
        if(isset($request->leagues)) {
            $leaguesArr = explode(',', $request->leagues);
            foreach ($leaguesArr as $leagueId) {
                Cache::tags(['league-'.$leagueId])->flush();
            }
        }

        $article->clearTeamCache();
        if(isset($request->teams)) {
            $teamsArr = explode(',', $request->teams);
            foreach ($teamsArr as $teamId) {
                Cache::tags(['team-'.$teamId])->flush();
            }
        }

        $tgs = $request->get('tags', []);

        if (!empty($tgs)) {
            $tgs = explode(',', $tgs);
        } else {
            $tgs = [];
        }

        // print_r($cats);
        // die;

        $article->tags()->sync($tgs);
        $article->categories()->sync($cats);
        $article->players_sync($request->players);
        $article->leagues_sync($request->leagues);
        $article->teams_sync($request->teams);
        $article->saveHashtags($request->hashtags ? json_decode($request->hashtags, true) : null);

        /*
         * If add_to_slides is checked remove the last slide and prepend article as new slide to slide list.
         */
        if ($request->get('add_to_slides')) {
            $slides = Slide::pluck('article_id')->toArray();

            if (!in_array($articleId, $slides)) {
                $slideToDelete = Slide::orderBy('order', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($slideToDelete) {
                    $slideToDelete->delete();
                }

                $firstSlide = Slide::orderBy('order')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $slide = new Slide([
                    'article_id' => $article->id,
                    'order' => ($firstSlide ? $firstSlide->order - 1 : 0),
                ]);
                $slide->save();
            }
        }

        //new tags
        $newTagIds = [];

        foreach ($request->get('newTags', []) as $newTag) {
            $tag = new Tag([
                'title' => $newTag
            ]);
            $tag->save();
            $newTagIds[] = $tag->id;
        }

        $article->tags()->attach($newTagIds);
        //end of new tags


        $cache_prefix = 'articles_show_'.$articleId;
        Cache::forget($cache_prefix);

        Redis::ClearArticles();

        return $this->show($article->id);
    }

    /**
     * @param $articleId
     */
    public function destroy($articleId)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $article = Article::findOrFail($articleId);
        $article->delete();
        Redis::ClearArticles();
    }

    public function like(Request $request, $articleId) {

        $article = Article::findOrFail($articleId);
	$userId = $request->userId;

        if(in_array($userId, $article->liked_by)) return ['status' => 'failed'];

        DB::beginTransaction();
        try {
            $article->likes++;
            $article->save();

            $like = ArticleLike::create([
                'article_id' => $articleId,
                'user_id' => $userId
            ]);
            DB::commit();
            return ['status' => 'ok'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'failed'];
        }
    }

    public function unlike(Request $request, $articleId) {

        $article = Article::findOrFail($articleId);
	$userId = $request->userId;


        if(!in_array($userId, $article->liked_by)) return ['status' => 'failed'];

        DB::beginTransaction();
        try {
            $article->likes--;
            $article->save();

            $like = ArticleLike::where([
                'article_id' => $articleId,
                'user_id' => $userId
            ]);
            $like->delete();
            DB::commit();
            return ['status' => 'ok'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'failed'];
        }
    }
}
