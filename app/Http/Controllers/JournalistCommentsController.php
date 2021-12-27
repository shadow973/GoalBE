<?php

namespace App\Http\Controllers;

use App\Http\Requests\JournalistCommentAddRequest;
use App\Http\Requests\JournalistCommentUpdateRequest;
use App\JournalistComment;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;

class JournalistCommentsController extends Controller
{
    protected $user;

    public function __construct(){
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function index(Request $request){
        $from = Carbon::parse($request->get('from'));
        $to = Carbon::parse($request->get('to'));

        $comments = JournalistComment::whereBetween('date', array($from, $to))
            ->with('contentManager')
            ->get();

        $users = User::whereHas('roles', function($q){
            $q->where('name', 'journalist');
        })->with(['articlesWithOnlyDates' => function($q) use($from, $to){
            $q->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$from, $to]);
        }])->get();

        $dates = [];

        for($date = $from; $date->lte($to); $date->addDay()){
            $dates[$date->format('Y-m-d')] = [
                'count' => 0,
                'comment' => null,
            ];
        }

        foreach($users as $userKey => $user){
            $user->article_count_by_dates = $dates;
            foreach($user->articlesWithOnlyDates as $article){
                $articleCountByDates = $user->article_count_by_dates;
                $articleCountByDates[$article->created_at->format('Y-m-d')]['count']++;
                $user->article_count_by_dates = $articleCountByDates;

                foreach($comments as $comment){
                    if($user->id == $comment->journalist_id){
                        $articleCountByDates = $user->article_count_by_dates;
                        $articleCountByDates[Carbon::parse($comment->date)->format('Y-m-d')]['comment'] = $comment;
                        $user->article_count_by_dates = $articleCountByDates;
                    }
                }

                unset($user->articlesWithOnlyDates);
            }
        }

        return $users;
    }

    public function store(JournalistCommentAddRequest $request){
        $comment = new JournalistComment($request->all());
        $comment->content_manager_id = $this->user->id;

        $comment->save();

        return JournalistComment::find($comment->id);
    }

    public function update(JournalistCommentUpdateRequest $request, $id){
        $comment = JournalistComment::findOrFail($id);
        $comment->update($request->all());

        return JournalistComment::find($id);
    }

    public function destroy($id){
        JournalistComment::findOrFail($id)
            ->delete();
    }
}
