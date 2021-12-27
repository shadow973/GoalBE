<?php

namespace App\Http\Controllers;

use App\TopTeam;
use Illuminate\Http\Request;
use JWTAuth;

class TopTeamsController extends Controller
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
        $topTeams = TopTeam::orderBy('order')
            ->with('tag')
            ->orderBy('created_at')
            ->get();

        return $topTeams;
    }

    public function store(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('top_teams_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'tag_id' => 'required|exists:tags,id',
        ]);

        $topTeam = new TopTeam($request->all());
        $topTeam->save();

        return $topTeam;
    }

    public function show($topTeamId){
        return TopTeam::findOrFail($topTeamId);
    }

    public function reorder(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('top_teams_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $topTeams = TopTeam::whereIn('id', array_keys($request->all()))->get();

        foreach($topTeams as $topTeam){
            foreach($request->all() as $key => $val){
                if($topTeam->id == $key){
                    $topTeam->order = $val;
                    $topTeam->save();
                }
            }
        }
    }

    public function update($topTeamId, Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('top_teams_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'tag_id' => 'required|exists:tags,id',
        ]);

        $topTeam = TopTeam::findOrFail($topTeamId);
        $topTeam->update($request->all());

        return TopTeam::find($topTeamId);
    }

    public function destroy($topTeamId){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('top_teams_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $topTeam = TopTeam::findOrFail($topTeamId);
        $topTeam->delete();
    }
}
