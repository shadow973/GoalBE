<?php

namespace App\Http\Controllers;

use App\GalleryAlbum;
use App\GalleryItem;
use Illuminate\Http\Request;
use JWTAuth;

class GalleryAlbumsController extends Controller
{
    protected $user;

    public function __construct(){
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function index(){
        return GalleryAlbum::orderBy('created_at', 'desc')
            ->with('galleryItems')
            ->get();
    }

    public function store(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('gallery_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'title' => 'required',
        ]);

        $album = new GalleryAlbum($request->all());
        $album->visibility = $request->visibility;
        $album->save();

        $galleryItemIds = $request->get('gallery_item_ids', []);

        $galleryItemIds = explode(',', $galleryItemIds);

        if(count($galleryItemIds)){
            GalleryItem::whereIn('id', $galleryItemIds)
                ->update([
                    'album_id' => $album->id,
                ]);
        }

        return $album->load('galleryItems');
    }

    public function show($id){
        return GalleryAlbum::findOrFail($id)
            ->load('galleryItems');
    }

    public function update($id, Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('gallery_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'title' => 'required',
        ]);

        $album = GalleryAlbum::findOrFail($id);
        $album->update($request->all());
        $album->visibility = $request->visibility;
        $album->save();
        $galleryItemIds = $request->get('gallery_item_ids', []);

        $galleryItemIds = explode(',', $galleryItemIds);


        if(count($galleryItemIds)){
            GalleryItem::where('album_id', $id)
                ->update([
                    'album_id' => null
                ]);

            GalleryItem::whereIn('id', $galleryItemIds)
                ->update([
                    'album_id' => $album->id,
                ]);
        }

        return GalleryAlbum::find($id)
            ->load('galleryItems');
    }

    public function destroy($id){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('gallery_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        GalleryAlbum::findOrFail($id)
            ->delete();
    }
}
