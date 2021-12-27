<?php

namespace App\Http\Controllers;

use App\ArticleView;
use App\Hashtag;
use App\Http\Requests\AnchorRequest;
use App\Http\Requests\ArticleAddRequest;
use App\Http\Requests\ArticleUpdateRequest;
use App\Models\ArticleAnchor;
use App\Slide;
use App\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Article;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Intervention;
use JWTAuth;
use Illuminate\Support\Facades\Cache;
use App\Models\Redis;
use App\GalleryItem;
use Illuminate\Support\Facades\DB;

class HashtagsController extends Controller
{
    protected $user;

    public function __construct(Request $request)
    {
        try {
            $this->user = JWTAuth::parseToken()->toUser();
        } catch (\Exception $e) {

        }
    }

    /**
     * @return mixed
     */
    public function index(Request $request)
    {
        if(!$this->user) abort(403);
        if(!$this->user->hasRole('admin')) abort(403);

        return ['data' => Hashtag::all()->pluck('hashtag')->toArray()];
    }

    public function getArticles(Request $request, $hashtag) {
        $perPage = $request->per_page ? $request->per_page : 8;

        return Article::whereHas('hashtags', function($query) use ($hashtag) {
            $query->where('hashtag', $hashtag);
        })->paginate($perPage);
    }

}
