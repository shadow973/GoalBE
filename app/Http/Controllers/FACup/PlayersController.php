<?php

namespace App\Http\Controllers\FACup;

use App\FACup\Player;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;

class PlayersController extends Controller
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
        return Player::orderBy('name')
            ->get();
    }

    public function store(Request $request){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'name' => 'required'
        ]);

        $player = new Player($request->all());
        $player->save();

        return $player;
    }

    public function show($id){
        return Player::findOrFail($id)
            ->load('homeMatches', 'awayMatches');
    }

    public function update($id, Request $request){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $player = Player::findOrFail($id);

        $this->validate($request, [
            'name' => 'required'
        ]);

        $player->update($request->all());

        return Player::find($id);
    }

    public function destroy($id){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        return (string)Player::findOrFail($id)
            ->delete();
    }
}
