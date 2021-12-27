<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ads;
use App\AdsPosition;
use Intervention;
use Storage;
use JWTAuth;

class AdsController extends Controller
{
    public $request;
    protected $user;

    public function __construct(Request $request)
    {
        $this->request = $request;
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function index(){
        $data = Ads::where('status_id','>', -1);

        if(!$this->user || !$this->user->hasRole('admin')){
            $data->where('is_visible', true);
            $data->select(['id', 'position_id', 'title', 'iframe', 'image', 'ads_url']);
        }

        return response()->json($data->get()->toArray());
    }

    public function getItem(){
        if(!$this->user || !$this->user->hasRole('admin')) {
            abort(403);
        }

    	$data = Ads::where('id', $this->request->id)->where('status_id', '>', -1);
    	if(empty($data)){
    		abort(404);
    	}

    	if($data->count() == 0){
    		abort(404);
    	}

    	$data = $data->first()->toArray();


    	return response()->json($data);
    }

    public function setItem(){
        if(!$this->user || !$this->user->hasRole('admin')) {
            abort(403);
        }

    	$item = isset($this->request->id)? Ads::find($this->request->id) : new Ads();

    	$data = $this->request->all();

    	// return response()->json($data);

    	if(isset($data['title'])) $item->title = $data['title'];
    	$item->iframe = isset($data['iframe']) ? $data['iframe'] : null;
    	$item->ads_url = isset($data['ads_url']) ? $data['ads_url'] : null;
    	$item->is_visible = isset($data['is_visible']);
    	if(isset($data['position_id'])) $item->position_id = $data['position_id'];

    	if(!isset($data['image_new'])){
            unset($data['image']);
        }

        if (isset($data['delete_image'])) {
            $data['image'] = '';
        }

        if(isset($data['image_new'])){
            $image = Intervention::make($data['image_new']);
            $imageName = str_random(32);

            $image = $image->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/ads/' . $imageName . '.png',
                (string)$image
            );

            $data['image'] = 'images/ads/' . $imageName . '.png';

            unset($data['image_new']);
        }

        if(isset($data['image'])) $item->image = $data['image'];


    	$item->save();

    	$this->request->id = $item->id;

    	return $this->getItem();
    }

    public function resetViewCount() {
        if(!$this->user || !$this->user->hasRole('admin')){
            abort(403);
        }


        $ad = Ads::find($this->request->id);
        $ad->view_count = 0;
        $ad->save();

        return $this->getItem();
    }

    public function destroy() {
        if(!$this->user || !$this->user->hasRole('admin')){
            abort(403);
        }

        $ad = Ads::find($this->request->id);
        $ad->delete();
    }

    public function getPositions(){
    	$data = AdsPosition::where('status_id','>', -1)->get()->toArray();
        return response()->json($data);
    }
}
