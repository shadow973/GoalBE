<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Article;
use App\Comment;
use Illuminate\Http\Request;
use JWTAuth;
use App\Models\CommentsRate;
use App\Models\MatchCommentsRate;
use App\Models\CommentsReplyRate;
use App\Models\MatchCommentsReplyRate;

class CommentsController extends Controller
{
    protected $user;

    public function __construct(){
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function myComments(Request $request) {
        if (!$this->user) {
            abort(401);
        }

        $page = $request->page ? $request->page : 1;
        $perPage = $request->per_page ? $request->per_page : 10;

        $userId = $this->user->id;

        $articleComments = Article::whereHas('comments', function($q) {
            $q->where('user_id', $this->user->id);
        })->select('id', 'title')->with(['comments' => function($q) use ($userId) {
            $q->where('user_id', $userId);
            $q->with('author');
            $q->withCount('replies');
            $q->with('replies');
            $q->select(['comments.user_id', 'comments.id', 'comments.content', 'comments.article_id', 'comments.likes', 'comments.dislikes', 'created_at']);
        }]);

        return $articleComments->paginate($perPage);
    }

    public function comments(Request $request){

        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        return Comment::with('author')
        ->paginate(30);
    }

    public function commentsDelete(Request $request){
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        $item =  Comment::findOrFail($request->itemid);
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
            $item =  Comment::findOrFail($id);
            $item->delete();
        }


    }

    public function commentsItem(Request $request){

        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }
        
        $item =  Comment::findOrFail($request->itemid);

        if($request->isMethod('post')){
            $item->content = $request->content;
            $item->save();
        }

        $item->article_title = $item->article->title;
        $item->user_first_name = $item->author->first_name;
        $item->user_last_name = $item->author->last_name;
        $item->user_email = $item->author->email;

        return $item;

    }

    public function checkRate($user_id, $comment_id, $type){
        $return = 1;
        $check = CommentsRate::where('user_id',$user_id)->where('comment_id',$comment_id)->where('type',$type)->count();
        $rate = new CommentsRate();
        $rate->user_id = $user_id;
        $rate->comment_id = $comment_id;
        $rate->type = $type;
        $rate->save();

        $secType = 'like';
        if($type == 'like'){
            $secType = 'dislike';
        }

        $secCheck = CommentsRate::where('user_id',$user_id)->where('comment_id',$comment_id)->where('type', $secType);

        if($secCheck->count() > 0){
            $secCheck = $secCheck->delete();
            $comm = Comment::findOrFail($comment_id);
            if($secType == 'like'){
                $comm->likes = $comm->likes-1;
            }else{
                $comm->dislikes = $comm->dislikes-1;
            }
            $comm->save();
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


        $comm = Comment::findOrFail($request->com_id);
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


        $comm = Comment::findOrFail($request->com_id);
        $comm->dislikes = $comm->dislikes+1;
        $comm->save();

        return ['status' => 'ok', 'code' => $rscheck];
    }

    public function index(Request $request, $articleId){
        $query = Comment::where('article_id', $articleId)
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

    public function store($articleId, Request $request){
        if(!$this->user){
            abort(401);
        }

        $this->validate($request, [
            'content'    => 'required|min:30|max:8000',
        ]);

        $article = Article::findOrFail($articleId);

        $comment = new Comment($request->all());
        $comment->article_id = $articleId;
        $comment->user_id = $this->user->id;
        $comment->save();

        return $comment->load('author', 'replies');
    }

    public function show($articleId, $commentId){
        return Comment::findOrFail($commentId)
            ->load('author', 'replies.author');
    }

    public function update($articleId, $commentId, Request $request){
        if(!$this->user){
            abort(401);
        }

        $this->validate($request, [
            'content'    => 'required',
        ]);

        $comment = Comment::findOrFail($commentId);

        if($this->user->id != $comment->user_id){
            abort(403);
        }

        $comment->content = $request->input('content');
        $comment->save();

        return $comment->load('author', 'replies.author');
    }

    public function destroy($articleId, $commentId){
        if(!$this->user){
            abort(401);
        }

        $comment = Comment::findOrFail($commentId);

        if($this->user->id != $comment->user_id){
            abort(403);
        }

        $comment->delete();
    }

    public function getStats(){
        $countTotal = Comment::count();
        $countToday = Comment::whereDate('created_at','=',Carbon::today()->format('Y-m-d'))->count();
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
        $comment = new Comment();
        $latestComments = $comment->with(['author', 'article'])->orderBy('created_at', 'desc')->take(10)->get();
        return $latestComments;
    }

    public function unlike(Request $request) {
        if(!$this->user){
            abort(401);
        }

        $rate = CommentsRate::where('user_id', $this->user->id)->where('comment_id', $request->com_id)->where('type', 'like');

        if($rate->count() == 0) {
            return ['status' => 'failed'];
        }

        $rate->delete();

        $comm = Comment::findOrFail($request->com_id);
        $comm->likes = $comm->likes-1;
        $comm->save();

        return ['status' => 'ok'];
    }
}
