<?php

namespace App\Http\Controllers;

use App\GalleryItem;
use Illuminate\Http\Request;
use JWTAuth;

class VideosController extends Controller
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
        $videos = GalleryItem::where('type', 'video')
            ->where('show_in_video_gallery', true)
            ->orderBy('created_at', 'desc')
            ->with('videoGalleryCategories');

        if ($request->has('category_id') && $request->get('category_id') != 'null') {
            $videos->whereHas('videoGalleryCategories', function ($query) use ($request) {
                $query->where('id', $request->get('category_id'));
            });
        }

        if ($request->has('sport_id') && $request->get('sport_id') != 'null') {
            $videos->whereHas('videoGalleryCategories', function ($query) use ($request) {
                $query->where('id', $request->get('sport_id'));
            });
        }

        if (str_contains($request->get('options'), 'paginate')) {
            return $videos->paginate(30);
        }

        return $videos->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer'
        ]);

        $videoData = @file_get_contents('https://api.setantamedia.ge/videos/' . $request->get('id'));

        if (!$videoData) {
            return abort(400);
        }

        $videoData = json_decode($videoData);

        $galleryItem = new GalleryItem([
            'type' => 'video',
            'filename' => $request->get('id'),
            'filename_webp' => $fileName . '.' . $uploadedFile->getClientOriginalExtension(),
            'filename_preview' => $videoData->image,
            'title' => $request->get('title'),
            'show_in_video_gallery' => true
        ]);

        $galleryItem->save();
        $galleryItem->videoGalleryCategories()->sync($request->get('categories'));

        return $galleryItem;
    }

    public function show($id)
    {
        $video = GalleryItem::findOrFail($id)
            ->load('videoGalleryCategories');

        return $video;
    }

    public function destroy($id)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('gallery_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $video = GalleryItem::findOrFail($id);
        $video->delete();
    }
}
