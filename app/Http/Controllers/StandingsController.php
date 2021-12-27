<?php

namespace App\Http\Controllers;

use App\League;
use App\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;


class StandingsController extends Controller
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
        $leagues = League::orderBy('order')
            ->with(['category', 'teams.tag'])
            ->get();

        return $leagues;
    }

    public function store(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('standings_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'category_id' => 'required|exists:categories,id',
            'teams'       => 'array',
        ]);

        $teams = array_filter($request->get('teams'), function($val){
            return !empty($val['tag_id']);
        });

        if(!count($teams)){
            return response()
                ->json([
                    'teams' => ['You can\'t create an empty league without any teams.']
                ], 422);
        }

        $league = new League($request->all());
        $league->save();

        $teamsToInsert = [];

        foreach($teams as $team){
            $teamsToInsert[] = [
                'league_id'     => $league->id,
                'tag_id'        => $team['tag_id'],
                'matches'       => $team['matches'],
                'points'        => $team['points'],
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ];
        }

        Team::insert($teamsToInsert);

        return null;
    }

    public function show($leagueId){
        $league = League::findOrFail($leagueId)
            ->load(['category', 'teams.tag']);

        return $league;
    }

    public function update($leagueId, Request $request){
        $league = League::findOrFail($leagueId);

        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('standings_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'category_id' => 'required|exists:categories,id',
            'teams'       => 'array',
        ]);

        $teams = array_filter($request->get('teams'), function($val){
            return !empty($val['tag_id']);
        });

        if(!count($teams)){
            return response()
                ->json([
                    'teams' => ['You can\'t save an empty league without any teams.']
                ], 422);
        }

        $league->update($request->all());
        $league->teams()->delete();

        $teamsToInsert = [];

        foreach($teams as $team){
            $teamsToInsert[] = [
                'league_id'     => $league->id,
                'tag_id'        => $team['tag_id'],
                'matches'       => $team['matches'],
                'points'        => $team['points'],
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ];
        }

        Team::insert($teamsToInsert);

        return null;
    }

    public function destroy($leagueId){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('standings_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        League::findOrFail($leagueId)
            ->delete();
    }

    public function reorder(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('standings_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $leagues = League::whereIn('id', array_keys($request->all()))->get();

        foreach($leagues as $league){
            foreach($request->all() as $key => $val){
                if($league->id == $key){
                    $league->order = $val;
                    $league->save();
                }
            }
        }
    }

    public function reorderTeams($leagueId, Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('standings_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        League::findOrFail($leagueId);

        $teams = Team::whereIn('id', array_keys($request->all()))->where('league_id', $leagueId)->get();

        foreach($teams as $team){
            foreach($request->all() as $key => $val){
                if($team->id == $key){
                    $team->order = $val;
                    $team->save();
                }
            }
        }
    }
}
