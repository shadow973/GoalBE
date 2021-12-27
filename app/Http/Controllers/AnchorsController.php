<?php

namespace App\Http\Controllers;

use App\ArticleView;
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

class AnchorsController extends Controller
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
        return ArticleAnchor::all();
    }

    /**
     * @return mixed
     */
    public function show($anchorId)
    {
        return ArticleAnchor::findOrFail($anchorId);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(AnchorRequest $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        return ArticleAnchor::create($request->toArray());
    }

    /**
     * @param $anchorId
     * @param AnchorRequest $request
     * @return mixed
     */
    public function update($anchorId, AnchorRequest $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->toArray();
        if(!isset($request->open_new_tab)) $data['open_new_tab'] = false;

        $anchor = ArticleAnchor::findOrFail($anchorId);
        $anchor->update($data);

        return $anchor;
    }

    /**
     * @param $anchorId
     * @return bool|null
     * @throws \Exception
     */
    public function destroy($anchorId)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $anchor = ArticleAnchor::findOrFail($anchorId);

        return ['message' => $anchor->delete()];
    }

}
