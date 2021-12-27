<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;

class SidebarController extends Controller
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
        $data = \App\Models\Sidebar::where('status_id',1)->orderBy('position')->get()->toArray();
        return response()->json($data);
    }

    public function update()
    {
    	if(!$this->user){
            abort(401);
        }

        if(!$this->user->hasRole('admin')){
            abort(403);
        }


        if(isset($this->request->ids)){
        	$ids = explode(',', $this->request->ids);

        	for($i = 0; $i < count($ids); $i++){
        		$pos = $i+1;
        		\App\Models\Sidebar::where('id',$ids[$i])
        		->update(['position'=>$pos]);
        	}
        }

        return $this->index();
    	
    }

    public function livescorestatus(){
        $data = \App\Models\Params::where('status_id',1)->where('key', 'livescore_widget_status')->first();
        $res = ['status' => 0];
        if(!empty($data)){
            $res['status'] = $data->value;
        }
        return response()->json($res);
    }

    public function livescorestatus_update(){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->hasRole('admin')){
            abort(403);
        }

        if(!isset($this->request->status)){
            return response()->json(['status' => 0]);
        }

        $param = \App\Models\Params::where('status_id',1)->where('key', 'livescore_widget_status')->first();
        if(empty($param)){
            $param = new \App\Models\Params();
            $param->key = 'livescore_widget_status';
        }

        $param->value = $this->request->status;
        $param->save();

        return response()->json(['status' => $param->value]);

    }

}
