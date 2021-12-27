<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Models\MatchComments;
use App\Models\MatchCommentsReplies;
use App\Models\ApiMatches;
use App\Models\MatchCommentsReplyRate;

class MatchCommentRepliesController extends Controller
{
    protected $user;

    public function __construct(){
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function index(int $matchId, int $commentId) {
        $matchComment = MatchComments::find($commentId);

        return $matchComment->load(['author', 'replies', 'replies.author']);
    }

    public function checkRate($user_id, $comment_id, $type){
        $return = 1;
        $check = MatchCommentsReplyRate::where('user_id',$user_id)->where('comment_id',$comment_id)->where('type',$type)->count();
        $rate = new MatchCommentsReplyRate();
        $rate->user_id = $user_id;
        $rate->comment_id = $comment_id;
        $rate->type = $type;
        $rate->save();

        $secType = 'like';
        if($type == 'like'){
            $secType = 'dislike';
        }


        $secCheck = MatchCommentsReplyRate::where('user_id',$user_id)->where('comment_id',$comment_id)->where('type', $secType);

        if($secCheck->count() > 0){
            $secCheck = $secCheck->delete();
            $reply = MatchCommentsReplies::findOrFail($comment_id);
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

        $rscheck = $this->checkRate($this->user->id, $request->repl_id, 'like');
        if(!$rscheck){
            return ['status' => 'failed'];
        }

        $reply = MatchCommentsReplies::findOrFail($request->repl_id);
        $reply->likes = $reply->likes+1;
        $reply->save();

        return ['status' => 'ok', 'code' => $rscheck];
    }

    public function dislike(Request $request){
        if(!$this->user){
            abort(401);
        }

        $rscheck = $this->checkRate($this->user->id, $request->repl_id, 'dislike');
        if(!$rscheck){
            return ['status' => 'failed'];
        }

        $reply = MatchCommentsReplies::findOrFail($request->repl_id);
        $reply->dislikes = $reply->dislikes+1;
        $reply->save();

        return ['status' => 'ok', 'code' => $rscheck];
    }

    public function store($articleId, $commentId, Request $request){
        if(!$this->user){
            abort(401);
        }

        $this->validate($request, [
            'content'    => 'required',
        ]);

        $comment = MatchComments::findOrFail($commentId);

        $reply = new MatchCommentsReplies();
        $reply->comment_id = $commentId;
        $reply->user_id = $this->user->id;
        $reply->content = $request->content;
        $reply->save();

        return $reply->load('author');
    }

    public function update($articleId, $commentId, $replyId, Request $request){
        if(!$this->user){
            abort(401);
        }

        $this->validate($request, [
            'content'    => 'required',
        ]);

        $reply = MatchCommentsReplies::findOrFail($replyId);

        if($this->user->id != $reply->user_id){
            abort(403);
        }

        $reply->content = $request->input('content');
        $reply->save();

        return $reply->load('author');
    }

    public function destroy($articleId, $commentId, $replyId){
        if(!$this->user){
            abort(401);
        }

        $reply = MatchCommentsReplies::findOrFail($replyId);

        if($this->user->id != $reply->user_id){
            abort(403);
        }

        $reply->delete();
    }

    public function unlike(Request $request) {
        if(!$this->user){
            abort(401);
        }

        $rate = MatchCommentsReplyRate::where('user_id', $this->user->id)->where('comment_id', $request->repl_id)->where('type', 'like');

        if($rate->count() == 0) {
            return ['status' => 'failed'];
        }

        $rate->delete();

        $comm = MatchCommentsReplies::findOrFail($request->repl_id);
        $comm->likes = $comm->likes-1;
        $comm->save();

        return ['status' => 'ok'];
    }
}
