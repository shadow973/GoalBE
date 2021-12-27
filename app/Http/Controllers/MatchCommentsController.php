<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Article;
use App\Comment;
use Illuminate\Http\Request;
use JWTAuth;
use App\Models\MatchComments;
use App\Models\MatchCommentsReplies;
use App\Models\ApiMatches;
use App\Models\MatchCommentsRate;

class MatchCommentsController extends Controller
{
    protected $user;

    public function __construct(){
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function comments(Request $request){

        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        return MatchComments::leftjoin('api_matches as a', 'a.match_id','=','match_comments.match_id')
            ->leftjoin('users as u', 'u.id','=','match_comments.user_id')
            ->addSelect('comments.*' ,
                'a.match_id as match_id',
                'u.first_name as user_first_name',
                'u.last_name as user_last_name',
                'u.email as user_email')
            ->paginate(30);
    }

    public function commentsDelete(Request $request){
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        $item =  MatchComments::findOrFail($request->itemid);
        $item->delete();
    }


    public function commentsDeleteItems(Request $request){
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        $items = explode(',', $request->items);

        foreach ($items as $id) {
            $item =  MatchComments::findOrFail($id);
            $item->delete();
        }


    }

    public function commentsItem(Request $request){

        if (!$this->user) {
            abort(401);
        }

        $item =  MatchComments::findOrFail($request->itemid);

        if($request->isMethod('post')){
            $item->content = $request->content;
            $item->save();
        }


        $item->user_first_name = $item->author->first_name;
        $item->user_last_name = $item->author->last_name;
        $item->user_email = $item->author->email;

        return $item;

    }

    public function checkRate($user_id, $comment_id, $type){
        $return = 1;
        $check = MatchCommentsRate::where('user_id',$user_id)->where('comment_id',$comment_id)->where('type',$type)->count();
        $rate = new MatchCommentsRate();
        $rate->user_id = $user_id;
        $rate->comment_id = $comment_id;
        $rate->type = $type;
        $rate->save();

        $secType = 'like';
        if($type == 'like'){
            $secType = 'dislike';
        }


        $secCheck = MatchCommentsRate::where('user_id',$user_id)->where('comment_id',$comment_id)->where('type', $secType);
            
        if($secCheck->count() > 0){
            $secCheck = $secCheck->delete();
            $reply = MatchComments::findOrFail($comment_id);
            if($secType == 'like'){
                $reply->likes = $reply->likes-1;
            }else{
                $reply->dislikes = $reply->dislikes-1;
            }
            $reply->save();
            $return = 2;
        }


        if($check > 0){
            return false;
        }else{
            return $return;
        }
        
    }

    public function like(Request $request){
        if(!$this->user){
            abort(401);
        }

        $rscheck = $this->checkRate($this->user->id, $request->com_id, 'like');
        if(!$rscheck){
            return ['status' => 'failed'];
        }

        $comm = MatchComments::findOrFail($request->com_id);
        $comm->likes = $comm->likes+1;
        $comm->save();

        return ['status' => 'ok', 'code' => $rscheck];
    }

    public function dislike(Request $request){
        if(!$this->user){
            abort(401);
        }

        $rscheck = $this->checkRate($this->user->id, $request->com_id, 'dislike');
        if(!$rscheck){
            return ['status' => 'failed'];
        }


        $comm = MatchComments::findOrFail($request->com_id);
        $comm->dislikes = $comm->dislikes+1;
        $comm->save();

        return ['status' => 'ok', 'code' => $rscheck];
    }

    public function index(Request $request, $matchId){
        $query = MatchComments::where('match_id', $matchId)
            ->orderBy('created_at', 'desc')
            ->with('author', 'replies.author');

        if(!isset($request->from) && $request->from != 'web') {
            return $query->get();
        }

        $page = $request->page ? $request->page : 1;
        $perPage = $request->per_page ? $request->per_page : 30;
        $result = $query->get();

        $top = $result->sortByDesc('likes')->first();
        if($top && $top->likes > 0) {
            $index = $result->search(function($comm) use ($top) {
                return $comm->id == $top->id;
            });
            $result->splice($index, 1);
            $top->is_top = 1;
            $result->prepend($top);
        }

        $comments = $result->forPage($page, $perPage);
        if($request->last_page) {
            $comments = $result->forPage(1, $perPage * $request->last_page);
        }

        return ['data' => $comments, 'current_page' => $page, 'last_page' => (int)($query->count() / ($page * $perPage)) + 1];
    }

    public function store($matchId, Request $request){
        if(!$this->user){
            abort(401);
        }

        $this->validate($request, [
            'content'    => 'required|min:30|max:8000',
        ]);

        $match = ApiMatches::where('match_id',$matchId);

        if($match->count() == 0){
            abort(404);
        }

        $comment = new MatchComments();
        $comment->match_id = $matchId;
        $comment->content = $request->input('content');
        $comment->user_id = $this->user->id;
        $comment->save();

        return $comment->load('author', 'replies');
    }

    public function show($matchId, $commentId){
        return MatchComments::findOrFail($commentId)
            ->load('author', 'replies.author');
    }

    public function update($matchId, $commentId, Request $request){
        if(!$this->user){
            abort(401);
        }

        $this->validate($request, [
            'content'    => 'required',
        ]);

        $comment = MatchComments::findOrFail($commentId);

        if($this->user->id != $comment->user_id){
            abort(403);
        }

        $comment->content = $request->input('content');
        $comment->save();

        return $comment->load('author', 'replies.author');
    }

    public function destroy($matchId, $commentId){
        if(!$this->user){
            abort(401);
        }

        $comment = MatchComments::findOrFail($commentId);

        if($this->user->id != $comment->user_id){
            abort(403);
        }

        $comment->delete();
    }

    public function getStats(){
        $countTotal = MatchComments::count();
        $countToday = MatchComments::whereDate('created_at','=',Carbon::today()->format('Y-m-d'))->count();
        $start = Carbon::parse('2017-09-15 00:00:00');
        $diff = $start->diffInDays(Carbon::now());
        $countAverage = round( $countTotal / $diff, 0);

        return [
            'total' => $countTotal,
            'today' => $countToday,
            'average' => $countAverage
        ];
    }

    public function latest(){
        $comment = new MatchComments();
        $latestComments = $comment->with(['author', 'matche'])->orderBy('created_at', 'desc')->take(10)->get();
        return $latestComments;
    }

    public function unlike(Request $request) {
        if(!$this->user){
            abort(401);
        }

        $rate = MatchCommentsRate::where('user_id', $this->user->id)->where('comment_id', $request->com_id)->where('type', 'like');

        if($rate->count() == 0) {
            return ['status' => 'failed'];
        }

        $rate->delete();

        $comm = MatchComments::findOrFail($request->com_id);
        $comm->likes = $comm->likes-1;
        $comm->save();

        return ['status' => 'ok'];
    }
}
