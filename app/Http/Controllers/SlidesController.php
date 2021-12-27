<?php

namespace App\Http\Controllers;

use App\Slide;
use Illuminate\Http\Request;
use Intervention;
use JWTAuth;

class SlidesController extends Controller
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
        $slides = Slide::orderBy('order')
            ->orderBy('created_at', 'desc')
            ->with('article.mainGalleryItem')
            ->get();

        return $slides;
    }

    public function store(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('slides_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'article_id' => 'required_without:image',
            'image'      => 'required_without:article_id',
            'link'       => '',
        ]);

        $slide = new Slide($request->only(['article_id']));

        if(!$request->has('article_id')){
            $image = Intervention::make($request->get('image'));
            $path = 'images/slides/' . str_random(32) . '.jpg';

            $image = $image->encode('jpg', 90)
                ->resize(800, 450)
                ->stream();

            Storage::disk('s3')->put(
                $path,
                (string)$image,
                'public'
            );

            $slide->image = $path;
            $slide->link = $request->get('link');
        }

        $slide->save();

        return $slide;
    }

    public function show($slideId){
        $slide = Slide::findOrFail($slideId)
            ->load('article.mainGalleryItem');

        return $slide;
    }

    public function update(Request $request, $slideId){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('slides_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'article_id' => 'required_without:image_new',
            'image_new'  => 'required_without:article_id',
            'link'       => '',
        ]);

        $slide = Slide::findOrFail($slideId);
        $data = $request->only(['article_id', 'link']);

        if($request->has('image_new')){
            $image = Intervention::make($request->get('image_new'));
            $path = 'images/slides/' . str_random(32) . '.jpg';

            $image->encode('jpg', 90)
                ->resize(800, 450)
                ->stream();

            Storage::disk('s3')->put(
                $path,
                (string)$image,
                'public'
            );

            $data['image'] = $path;
            $slide->deleteImage();
        }

        if($request->has('article_id')){
            $data['image'] = null;
            $data['link'] = null;
            $slide->deleteImage();
        }

        $slide->update($data);

        return Slide::find($slideId);
    }

    public function reorder(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('slides_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $slides = Slide::whereIn('id', array_keys($request->all()))->get();

        foreach($slides as $slide){
            foreach($request->all() as $key => $val){
                if($slide->id == $key){
                    $slide->order = $val;
                    $slide->save();
                }
            }
        }
    }

    public function destroy($slideId){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('slides_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $slide = Slide::findOrFail($slideId);
        $slide->delete();
    }
}
