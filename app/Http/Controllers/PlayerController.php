<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Players;
use JWTAuth;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
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
        $data = Players::where('players.status_id','>', -1);


//        print_r($data->toSql());
//        die;

        return $data->get()->toArray();
    }

    public function search(){
        if(!isset($this->request->s) || empty($this->request->s) ){
            return [];
        }

        $data = Players::where('players.status_id','>', -1);
        $data->leftjoin('tags as t', 't.id','=','players.tag_id');

        $data->addSelect(DB::raw('
            players.* ,
            t.title as tag_title,
            t.image as tag_image,
            t.background_image as tag_background_image
            
         '));

        $data->where('t.title', 'LIKE', "%{$this->request->s}%");

        return $data->get()->toArray();
    }

    public function getItem($id, $bytag = false){

        $data = Players::where('id', $this->request->id);
        if($bytag){
            $data = Players::where('tag_id', $this->request->id);

            if($data->count() == 0)
                return abort(404);

            $data = $data->first();
        }

        if(empty($data)){
            abort(404);
        }

        $data = $data->where('status_id','>', -1);
        if($data->count() == 0){
            abort(404);
        }

        $data = $data->first();

        if(!empty($data->player_id)){
            $data->player_name = $data->player->fullname;
        }else{
            $data->player_name = "";
        }
        if(!empty($data->tag_id)){
            $data->tag_name = $data->tag->title;
        }else{
            $data->tag_name = "";
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


        $item = isset($this->request->id)? Players::find($this->request->id) : new Players();

        $data = $this->request->all();

        // return response()->json($data);

        if(isset($data['name'])) $item->name = $data['name'];
        if(isset($data['player_id'])) $item->player_id = $data['player_id'];
        if(isset($data['description'])) $item->description = $data['description'];


        $item->save();
        $this->request->id = $item->id;

        return $this->getItem($item->id);
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

        $item = Players::find($this->request->id);

        $item->status_id = -1;
        $item->save();

        return "ok";
    }

}
