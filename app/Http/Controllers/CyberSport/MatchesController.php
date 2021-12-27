<?php

namespace App\Http\Controllers\CyberSport;

use App\CyberSport\Match;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Redis;
use JWTAuth;

class MatchesController extends Controller
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
        $matches = Match::orderBy('datetime')
            ->with('playerOne', 'playerTwo');

        if($request->has('stage')){
            $matches->where('stage', $request->get('stage'));
        }

        if($request->has('status')){
            $matches->whereIn('status', $request->get('status'));
        }

        if($request->has('date')){
            $matches->whereRaw('DATE(datetime) = ?', $request->get('date'));
        }

        return $matches->get();
    }

    public function store(Request $request){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'player_1_id'   => 'required|exists:cs_players,id',
            'player_2_id'   => 'required|exists:cs_players,id',
            'stage'         => 'required',
            'datetime'      => 'required|date',
            'status'        => 'required',
            'room'          => 'required',
        ]);

        $match = new Match($request->all());
        $match->save();

        return $match;
    }

    public function show($id){
        return Match::findOrFail($id)
            ->load('playerOne', 'playerTwo');
    }

    public function update($id, Request $request){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $match = Match::findOrFail($id);

        $this->validate($request, [
            'player_1_id'   => 'required|exists:cs_players,id',
            'player_2_id'   => 'required|exists:cs_players,id',
            'stage'         => 'required',
            'datetime'      => 'required|date',
            'status'        => 'required',
            'room'          => 'required',
        ]);

        $match->update($request->all());

        $redis = Redis::connection();
        $redis->publish('message', json_encode(Match::find($id)->load('playerOne', 'playerTwo')));

        return Match::find($id);
    }

    public function destroy($id){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        return (string)Match::findOrFail($id)
            ->delete();
    }
}
