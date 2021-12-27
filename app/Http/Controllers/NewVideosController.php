<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewVideos;
use JWTAuth;
use Intervention;
use Storage;


class NewVideosController extends Controller
{
    protected $user;
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        try {
            $this->user = JWTAuth::parseToken()->toUser();
        } catch (\Exception $e) {
        }
    }

    public function index(){
        $videos = NewVideos::where('status_id','>',0)->get();

        return $videos;
    }

    public function getItem(){

        $data = NewVideos::where('id', $this->request->id);

        if(empty($data)){
            abort(404);
        }

        $data = $data->where('status_id','>', 0);
        if($data->count() == 0){
            abort(404);
        }

        $data = $data->first();

        return response()->json($data);
    }

    public function UploadVideo()
    {
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->hasRole('admin')){
            abort(403);
        }


                $data = $this->request->all();
        // print_r( $data );
        // die;



        if(isset($data['video'])){
            $video = $data['video'];

            $videoName = str_random(32);

            $video_data = new \App\Models\NewVideoItem();
            $video_data->save();

            Storage::disk('sftp')->put(
                'videos/videogallery/'.$video_data->id,
                $video
            );

            $video_data->video_url = '/videos/videogallery/'.$video_data->id .'/'.$video->hashName();
            $video_data->save();

            return $video_data;
        }

        return ['error' => 1];

    }


    public function setItem(){

        if(!$this->user){
            abort(401);
        }

        if(!$this->user->hasRole('admin')){
            abort(403);
        }


        $item = isset($this->request->id)? NewVideos::find($this->request->id) : new NewVideos();

        $data = $this->request->all();

        if(isset($data['video_img_file'])){
            $image = Intervention::make($data['video_img_file']);
            $imageName = str_random(32);

            $image = $image->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/videos/' . $imageName . '.png',
                (string)$image
            );

            $data['video_img'] = '/images/videos/' . $imageName . '.png';

            unset($data['video_img_file']);
        }

        // return response()->json($data);

        if(isset($data['title'])) $item->title = $data['title'];
        if(isset($data['video_url'])) $item->video_url = $data['video_url'];
        if(isset($data['ad_url'])) $item->ad_url = $data['ad_url'];
        if(isset($data['video_img'])) $item->video_img = $data['video_img'];
        if(isset($data['video_id'])) $item->video_id = $data['video_id'];
        if(isset($data['embed'])) $item->embed = $data['embed'];
        if(isset($data['video_img'])) $item->video_img = $data['video_img'];


        $item->save();
        $this->request->id = $item->id;

        return $this->getItem();
    }

    public function unsetItem(){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        if(!isset($this->request->id)){
            return abort(404);
        }

        $item = NewVideos::find($this->request->id);

        $item->status_id = -1;
        $item->save();

        return "ok";
    }
}
