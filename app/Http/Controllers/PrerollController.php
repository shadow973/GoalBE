<?php

namespace App\Http\Controllers;

use App\ArticleView;
use App\Http\Requests\AnchorRequest;
use App\Http\Requests\ArticleAddRequest;
use App\Http\Requests\ArticleUpdateRequest;
use App\Models\Preroll;
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

class PrerollController extends Controller
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
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }
        return Preroll::all();
    }

    /**
     * @return mixed
     */
    public function show($prerollId)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }
        return Preroll::findOrFail($prerollId);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->toArray();
        $data['percentage'] = (int)$data['percentage'];

        if(isset($data['is_active']))
            Preroll::where('is_active')->update(['view_count' => 0]);

        return Preroll::create($data);
    }

    /**
     * @param $prerollId
     * @param AnchorRequest $request
     * @return mixed
     */
    public function update($prerollId, Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->toArray();
        if(!isset($request->is_active)) $data['is_active'] = false;
        $data['percentage'] = (int)$data['percentage'];

        if(isset($data['is_active']) && $data['is_active'])
            DB::table('prerolls')->where('is_active', 1)->update(['view_count' => 0]);

        $preroll = Preroll::findOrFail($prerollId);
        $preroll->update($data);

        return $preroll;
    }

    /**
     * @param $prerollId
     * @return bool|null
     * @throws \Exception
     */
    public function destroy($prerollId)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $preroll = Preroll::findOrFail($prerollId);

        return ['message' => $preroll->delete()];
    }

}
