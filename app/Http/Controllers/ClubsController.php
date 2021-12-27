<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clubs;
use JWTAuth;
use Illuminate\Support\Facades\DB;
use Storage;
use Intervention;

class ClubsController extends Controller
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
        if(isset($this->request->bytag) && !empty($this->request->bytag)){
            $this->request->id = $this->request->bytag;

            return $this->getItem($this->request->id, true);
        }
        $data = Clubs::where('clubs.status_id','>', -1);

//        print_r($data->toSql());
//        die;

        return $data->get()->toArray();
    }

    public function search(){
        if(!isset($this->request->s) || empty($this->request->s) ){
            return [];
        }

        $data = Clubs::where('clubs.status_id','>', -1);
        $data->leftjoin('tags as t', 't.id','=','clubs.tag_id');

        $data->addSelect(DB::raw('
            clubs.* ,
            t.title as tag_title,
            t.image as tag_image,
            t.background_image as tag_background_image
            
         '));

        $data->where('t.title', 'LIKE', "%{$this->request->s}%");

        return $data->get()->toArray();
    }

    public function getItem(){

        $data = Clubs::where('id', $this->request->id);

        if(empty($data)){
            abort(404);
        }

        $data = $data->where('status_id','>', -1);
        if($data->count() == 0){
            abort(404);
        }

        $data = $data->first();

        if(!empty($data->team_id)){
            $data->team_name = $data->team->name;
            $data->team->players = $data->team->players;
        }else{
            $data->team_name = "";
        }

        return response()->json($data);
    }


    public function setItem(){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $item = isset($this->request->id)? Clubs::find($this->request->id) : new Clubs();

        $data = $this->request->all();

        // return response()->json($data);

        if(isset($data['name'])) $item->name = $data['name'];
        if(isset($data['team_id'])) $item->team_id = $data['team_id'];
        if(isset($data['description'])) $item->description = $data['description'];

        if(isset($data['image_new'])){
            $image = Intervention::make($data['image_new']);
            $imageName = str_random(32);

            $image = $image->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/clubs/' . $imageName . '.png',
                (string)$image
            );

            $data['image'] = 'images/clubs/' . $imageName . '.png';

            unset($data['image_new']);
        }

        if(isset($data['image'])) $item->image = $data['image'];


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

        $item = Clubs::find($this->request->id);

        $item->status_id = -1;
        $item->save();

        return "ok";
    }


}
