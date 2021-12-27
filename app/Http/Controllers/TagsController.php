<?php

namespace App\Http\Controllers;

use App\Category;
use App\Tag;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Intervention;
use Storage;
use JWTAuth;

class TagsController extends Controller
{
    protected $user;

    public function __construct()
    {
        try {
            $this->user = JWTAuth::parseToken()->toUser();
        } catch (\Exception $e) {

        }
    }

    public function index(Request $request)
    {
        $tags = Tag::orderBy('title')
            ->withCount('articles', 'users');
        if(isset($request->ids)){
            $ids = explode(',', $request->ids);
            $tags = $tags->whereIn('id',$ids);
        }
        return $tags->get();
    }

    public function store(Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('tags_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $this->validate($request, [
            'title' => 'required',
            'image' => '',
        ]);

        $tag = new Tag($request->all());

        if ($request->has('image')) {
            $image = Intervention::make($request->get('image'));
            $imageName = str_random(32);

            $image = $image->fit(40, 40)
                ->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/tags/' . $imageName . '.png',
                (string)$image
            );

            $tag->image = 'images/tags/' . $imageName . '.png';
        }

        if ($request->has('background_image')) {
            $backgroundImage = Intervention::make($request->get('background_image'));
            $backgroundImageName = str_random(32);

            $backgroundImage = $backgroundImage->fit(1920, 200)
                ->encode('jpg', 90)
                ->stream();

            Storage::disk('sftp')->put(
                'images/tag_backgrounds/' . $backgroundImageName . '.jpg',
                (string)$backgroundImage
            );

            $tag->background_image = 'images/tag_backgrounds/' . $backgroundImageName . '.jpg';
        }

        $tag->save();

        return Tag::find($tag->id);
    }

    public function show($tagId, Request $request)
    {
        $tag = Tag::findOrFail($tagId);

        if ($request->get('withArticles')) {
            $tag->articles = $tag->articles();

            if (str_contains($request->get('options'), 'news-only')) {
                $tag->articles->whereHas('mainGalleryItem', function ($q) {
                    $q->where('type', 'image');
                });
            }

            if (str_contains($request->get('options'), 'video')) {
                $tag->articles->whereHas('mainGalleryItem', function ($q) {
                    $q->where('type', 'video');
                });
            }

            if (str_contains($request->get('options'), 'paginate')) {
                $tag->articles = $tag->articles->paginate(15);
                $tag->articles->load('mainGalleryItem');
                return $tag;
            }

            $tag->load('articles.mainGalleryItem');
        }

        return $tag;
    }

    public function update($tagId, Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('tags_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $this->validate($request, [
            'title' => '',
            'image_new' => '',
        ]);

        $tag = Tag::findOrFail($tagId);
        $data = $request->all();

        if (!isset($data['image_new'])) {
            unset($data['image']);
        }

        if (!isset($data['background_image_new'])) {
            unset($data['background_image']);
        }

        if ($request->get('delete_image')) {
            $data['image'] = NULL;
        }

        if ($request->get('delete_background_image')) {
            $data['background_image'] = NULL;
        }

        if (isset($data['image_new'])) {
            $image = Intervention::make($data['image_new']);
            $imageName = str_random(32);

            $image = $image->fit(40, 40)
                ->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/tags/' . $imageName . '.png',
                (string)$image
            );

            $data['image'] = 'images/tags/' . $imageName . '.png';

            unset($data['image_new']);

            $tag->deleteImage();
        }

        if (isset($data['background_image_new'])) {
            $backgroundImage = Intervention::make($data['background_image_new']);
            $backgroundImageName = str_random(32);

            $backgroundImage = $backgroundImage->fit(1920, 200)
                ->encode('jpg', 90)
                ->stream();

            Storage::disk('sftp')->put(
                'images/tag_backgrounds/' . $backgroundImageName . '.jpg',
                (string)$backgroundImage
            );

            $data['background_image'] = 'images/tag_backgrounds/' . $backgroundImageName . '.jpg';
            unset($data['background_image_new']);

            $tag->deleteBackgroundImage();
        }

        $tag->update($data);

        return Tag::find($tagId);
    }

    public function destroy($tagId)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('tags_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $tag = Tag::findOrFail($tagId);
        $tag->delete();
    }

    public function export(Request $request)
    {
        $tags = Tag::select('title')
                    ->orderBy('created_at')
                    ->withCount('articles', 'users')
                    ->get();

        Excel::create('tags', function ($excel) use ($tags) {
            $excel->sheet('Sheet 1', function ($sheet) use ($tags) {
                $sheet->fromArray($tags);
            });
        })->export('xls', [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function search($q = false, Request $request)
    {
        if(!$q){
            $q = $request->get('q', '');
        }


        $tags = Tag::where('title', 'LIKE', "%{$q}%")
            ->get();

        foreach ($tags as $tag) {
            $tag->type = 'tag';
        }

        $categories = collect();

        if ($request->has('include_categories')) {
            $categories = Category::where('title', 'LIKE', "%{$q}%")
                ->with('ancestors')
                ->get();

            foreach ($categories as $category) {
                $category->type = 'category';
            }
        }

        return $tags->merge($categories)->sortBy('title')->values()->take(10)->all();
    }
}
