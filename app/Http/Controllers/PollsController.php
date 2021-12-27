<?php

namespace App\Http\Controllers;

use App\Poll;
use App\PollAnswer;
use Illuminate\Http\Request;
use JWTAuth;

class PollsController extends Controller
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
        return Poll::with('answers.answerers')
            ->get();
    }

    public function latest(){
        return Poll::with('answers.answerers')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function store(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('polls_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'question' => 'required',
        ]);

        $poll = new Poll($request->all());
        $poll->user_id = $this->user->id;
        $poll->save();

        foreach($request->get('answers', []) as $answer){
            $pollAnswer = new PollAnswer([
                'answer'  => $answer['answer'],
                'poll_id' => $poll->id,
            ]);
            $pollAnswer->save();
        }

        return Poll::find($poll->id)
            ->load('answers');
    }

    public function show($pollId){
        return Poll::findOrFail($pollId)
            ->load('answers.answerers');
    }

    public function update($pollId, Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('polls_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'question' => 'required',
        ]);

        $poll = Poll::findOrFail($pollId);
        $poll->update($request->all());

        $answerIdsToRetain = [];

        foreach($request->get('answers', []) as $answer){
            if(isset($answer['id'])){
                $pollAnswer = PollAnswer::find($answer['id']);
                $answerIdsToRetain[] = $answer['id'];
                if($pollAnswer){
                    $pollAnswer->update([
                        'answer' => $answer['answer']
                    ]);
                }
            }
            else{
                $pollAnswer = new PollAnswer([
                    'answer'  => $answer['answer'],
                    'poll_id' => $pollId,
                ]);
                $pollAnswer->save();
                $answerIdsToRetain[] = $pollAnswer->id;
            }
        }

        $poll->answers()->whereNotIn('id', $answerIdsToRetain)->delete();

        return Poll::find($poll->id)
            ->load('answers');
    }

    public function answer(Request $request){
        if(!$this->user){
            return response()->json([
                'error' => 'ხმის მისაცემად გთხოვთ გაიაროთ ავტორიზაცია.'
            ], 401);
        }

        $answer = PollAnswer::findOrFail($request->get('answer_id'));
        $poll = Poll::find($answer->poll_id);
        $poll->load('answers.answerers');

        foreach($poll->answers as $pollAnswer){
            foreach($pollAnswer->answerers as $answerer){
                if($answerer->id == $this->user->id){
                    return response()->json([
                        'error' => 'თქვენ უკვე მიეცით ხმა.'
                    ], 403);
                }
            }
        }

        $answer->answerers()->attach($this->user);

        return $poll->load('answers.answerers');
    }

    public function destroy($pollId){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('polls_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $poll = Poll::findOrFail($pollId);
        $poll->delete();
    }
}
