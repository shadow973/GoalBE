<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewPolls;
use App\Models\NewPollAnswers;
use App\Models\NewPollAnswerData;
use JWTAuth;

class NewPollsController extends Controller
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
        $polls = NewPolls::where('status_id','>',0)->get();

        return $polls;
    }

    public function getItem(){

        $data = NewPolls::where('id', $this->request->id);

        if(empty($data)){
            abort(404);
        }

        $data = $data->where('status_id','>', 0);
        if($data->count() == 0){
            abort(404);
        }

        $data = $data->first();
        $data->answers = $data->getAnswers();

        return response()->json($data);
    }


    public function setItem(){

        if(!$this->user){
            abort(401);
        }

        if(!$this->user->hasRole('admin')){
            abort(403);
        }


        $item = isset($this->request->id)? NewPolls::find($this->request->id) : new NewPolls();

        $data = $this->request->all();


		if($this->isJson($data['answer_id']) && !is_numeric($data['answer_id'])){
			$data['answer_id'] = json_decode($data['answer_id']);
		}else{
			$data['answer_id']= [$data['answer_id']];
		}

		if($this->isJson($data['answer_title']) && !is_numeric($data['answer_id'])){
			$data['answer_title'] = json_decode($data['answer_title']);
		}else{
			$data['answer_title']= [$data['answer_title']];
		}


        if(isset($data['title'])) $item->title = $data['title'];
        if(isset($data['description'])) $item->description = $data['description'];

        NewPollAnswers::where('poll_id', $item->id)->where('status_id', 1)->update(['status_id' => -1]);
        if(isset($data['answer_id'])){
        	for($i = 0; $i < count($data['answer_id']); $i++){
                $option = (empty($data['answer_id'][$i]))? new NewPollAnswers():NewPollAnswers::find($data['answer_id'][$i]);

                $option->title= $data['answer_title'][$i];
                $option->poll_id= $item->id;
                $option->status_id= 1;
                $option->save();
            }
        }

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

        $item = NewPolls::find($this->request->id);

        $item->status_id = -1;
        $item->save();

        return "ok";
    }

    public function setAnswer(){

        if((isset($this->request->poll_id) && (int)$this->request->poll_id > 0) && (isset($this->request->answer_id) && (int)$this->request->answer_id > 0)){

            $poll_anser = new NewPollAnswerData();
            $poll_anser->poll_id = $this->request->poll_id;
            $poll_anser->answer_id = $this->request->answer_id;
            $poll_anser->save();

            return ['status' => ' success'];
        }

        return ['status'=>'error'];

    }


    function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

}
