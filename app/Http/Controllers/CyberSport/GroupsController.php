<?php

namespace App\Http\Controllers\CyberSport;

use App\CyberSport\Group;
use App\CyberSport\Match;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use JWTAuth;

class GroupsController extends Controller
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
        $groups = Group::orderBy('id');

        if($request->has('stage')){
            if($request->get('stage') == 'semifinal-final'){
                $groups->where('stage', '1/2')
                    ->orWhere('stage', 'final');
            }
            elseif($request->get('stage') == 'grand-final'){
                $groups->where('stage', 'gf_1/2')
                    ->orWhere('stage', 'gf_repechage_1/2')
                    ->orWhere('stage', 'gf_final')
                    ->orWhere('stage', 'gf_repechage_final')
                    ->orWhere('stage', 'gf_grand_final');
            }
            else{
                $groups->where('stage', $request->get('stage'));
            }
        }

        if($request->has('date')){
            $groups->where('date', $request->get('date'));
        }

        if($request->has('with_players')){
            $stage = $request->get('stage');

            if($stage == 'semifinal-final'){
                $groups->with(['players.homeMatches' => function($query) use($stage){
                    $query->where('cs_matches.stage', '1/2')
                        ->orWhere('cs_matches.stage', 'final');
                }, 'players.awayMatches' => function($query) use($stage){
                    $query->where('cs_matches.stage', '1/2')
                        ->orWhere('cs_matches.stage', 'final');
                }]);
            }
            elseif($stage == 'grand-final'){
                $groups->with(['players.homeMatches' => function($query) use($stage){
                    $query->where('stage', 'gf_1/2')
                        ->orWhere('stage', 'gf_repechage_1/2')
                        ->orWhere('stage', 'gf_final')
                        ->orWhere('stage', 'gf_repechage_final')
                        ->orWhere('stage', 'gf_grand_final');
                }, 'players.awayMatches' => function($query) use($stage){
                    $query->where('stage', 'gf_1/2')
                        ->orWhere('stage', 'gf_repechage_1/2')
                        ->orWhere('stage', 'gf_final')
                        ->orWhere('stage', 'gf_repechage_final')
                        ->orWhere('stage', 'gf_grand_final');
                }]);
            }
            else{
                $groups->with(['players.homeMatches' => function($query) use($stage){
                    $query->where('cs_matches.stage', $stage);
                }, 'players.awayMatches' => function($query) use($stage){
                    $query->where('cs_matches.stage', $stage);
                }]);
            }
        }

        $groups = $groups->get();

        foreach($groups as $groupKey => $group){
            foreach($group->players as $playerKey => $player){
                $group->players[$playerKey]->points = 0;

                foreach($player->homeMatches as $homeMatchKey => $homeMatch){
                    if($homeMatch->status == 'finished' && $homeMatch->stage == $group->stage){
                        if($homeMatch->player_1_score > $homeMatch->player_2_score){
                            $group->players[$playerKey]->points += 3;
                        }
                        elseif($homeMatch->player_1_score == $homeMatch->player_2_score){
                            $group->players[$playerKey]->points += 1;
                        }
                    }
                    else{
                        unset($player->homeMatches[$homeMatchKey]);
                    }
                }

                foreach($player->awayMatches as $awayMatchKey => $awayMatch){
                    if($awayMatch->status == 'finished' && $awayMatch->stage == $group->stage){
                        if($awayMatch->player_1_score < $awayMatch->player_2_score){
                            $group->players[$playerKey]->points += 3;
                        }
                        elseif($awayMatch->player_1_score == $awayMatch->player_2_score){
                            $group->players[$playerKey]->points += 1;
                        }
                    }
                    else{
                        unset($player->awayMatches[$awayMatchKey]);
                    }
                }
            }

            if($group->stage == 'group'){
                $sortedPlayers = $group->players->sortBy('pivot.order')->sortByDesc('points')->values()->all();
                unset($group->players);
                $group->players = $sortedPlayers;
            }
        }

        foreach($groups as $group){
            foreach($group->players as $player){
                $homeMatches = array_values($player->homeMatches->toArray());
                unset($player->homeMatches);
                $player->home_matches = $homeMatches;

                $awayMatches = array_values($player->awayMatches->toArray());
                unset($player->awayMatches);
                $player->away_matches = $awayMatches;
            }
        }

        return response()->json($groups);
    }

    public function store(Request $request){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'name'  => 'required',
            'stage' => 'required',
            'date'  => 'required|date',
            'room'  => 'required',
        ]);

        $group = new Group($request->all());
        $group->save();

        $group->players()->sync(array_filter($request->get('players')));

        return $group;
    }

    public function show($groupId){
        $group = Group::findOrFail($groupId);

        $stage = $group->stage;

        $group->load(['players.homeMatches' => function($query) use($stage){
            $query->where('cs_matches.stage', $stage);
        }, 'players.awayMatches' => function($query) use($stage){
            $query->where('cs_matches.stage', $stage);
        }]);

        foreach($group->players as $key => $player){
            $group->players[$key]->points = 0;

            foreach($player->homeMatches as $homeMatch){
                if($homeMatch->status == 'finished'){
                    if($homeMatch->player_1_score > $homeMatch->player_2_score){
                        $group->players[$key]->points += 3;
                    }
                    elseif($homeMatch->player_1_score == $homeMatch->player_2_score){
                        $group->players[$key]->points += 1;
                    }
                }
            }

            foreach($player->awayMatches as $awayMatch){
                if($awayMatch->status == 'finished'){
                    if($awayMatch->player_1_score < $awayMatch->player_2_score){
                        $group->players[$key]->points += 3;
                    }
                    elseif($awayMatch->player_1_score == $awayMatch->player_2_score){
                        $group->players[$key]->points += 1;
                    }
                }
            }
        }

        $sortedPlayers = $group->players->sortBy('pivot.order')->sortByDesc('points')->values()->all();

        unset($group->players);

        $group->players = $sortedPlayers;

        return response()->json($group);
    }

    public function update($groupId, Request $request){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $group = Group::findOrFail($groupId);

        $this->validate($request, [
            'name'  => 'required',
            'stage' => 'required',
            'date'  => 'required|date',
            'room'  => 'required',
        ]);

        $group->update($request->all());

        $group->players()->detach();

        $playersToAdd = [];
        $index = 0;

        foreach(array_unique(array_filter($request->get('players', []))) as $playerId){
            $playersToAdd[] = [
                'group_id'  => $groupId,
                'player_id' => $playerId,
                'order'     => $index++,
            ];
        }

        DB::table('cs_group_player')->insert($playersToAdd);

        if($group->stage == 'group'){

        }
        elseif($group->stage == '1/64'){
            if(count($request->get('player_ids', []))){
                DB::table('cs_group_player')
                    ->where('group_id', $groupId)
                    ->where('player_id', $request->get('player_ids')[0])
                    ->update(['advances_to_next_stage' => true]);

                $loserPlayerId = $group->players()->whereNotIn('id', [$request->get('player_ids')[0]])
                    ->first()->id;

                DB::table('cs_group_player')
                    ->where('group_id', $groupId)
                    ->where('player_id', $loserPlayerId)
                    ->update(['advances_to_next_stage' => false]);
            }
            else{
                DB::table('cs_group_player')
                    ->where('group_id', $groupId)
                    ->update(['advances_to_next_stage' => null]);
            }
        }
        else{
            if(count($request->get('player_ids', []))){
                DB::table('cs_group_player')
                    ->where('group_id', $groupId)
                    ->where('player_id', $request->get('player_ids')[0])
                    ->update(['advances_to_next_stage' => true]);

                $loserPlayerId = $group->players()->whereNotIn('id', [$request->get('player_ids')[0]])
                    ->first()->id;

                DB::table('cs_group_player')
                    ->where('group_id', $groupId)
                    ->where('player_id', $loserPlayerId)
                    ->update(['advances_to_next_stage' => false]);
            }
            else{
                DB::table('cs_group_player')
                    ->where('group_id', $groupId)
                    ->update(['advances_to_next_stage' => null]);
            }
        }

        return Group::find($groupId);
    }

    public function destroy($groupId){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        return (string)Group::findOrFail($groupId)
            ->delete();
    }

    public function generateMatches($groupId){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $group = Group::findOrFail($groupId);
        $matchesToInsert = [];

        if(
            ($group->stage == 'group' && $group->players->count() < 4)
            ||
            ($group->stage != 'group' && $group->players->count() < 2)
        ){
            return response()
                ->json(
                    ['status' => 'pizdec', 'message' => 'Can\'t generate matches. Not enough players in a group.'],
                    500
                );
        }

        if($group->stage == 'group'){
            $matchesToInsert = [
                [
                    'player_1_id'   => $group->players[0]->id,
                    'player_2_id'   => $group->players[1]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[2]->id,
                    'player_2_id'   => $group->players[3]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                ///////////////////////////////////////////
                [
                    'player_1_id'   => $group->players[0]->id,
                    'player_2_id'   => $group->players[3]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[2]->id,
                    'player_2_id'   => $group->players[1]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                ///////////////////////////////////////////
                [
                    'player_1_id'   => $group->players[0]->id,
                    'player_2_id'   => $group->players[2]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[1]->id,
                    'player_2_id'   => $group->players[3]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                ///////////////////////////////////////////
                [
                    'player_1_id'   => $group->players[1]->id,
                    'player_2_id'   => $group->players[0]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[3]->id,
                    'player_2_id'   => $group->players[2]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                ///////////////////////////////////////////
                [
                    'player_1_id'   => $group->players[3]->id,
                    'player_2_id'   => $group->players[0]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[1]->id,
                    'player_2_id'   => $group->players[2]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                ///////////////////////////////////////////
                [
                    'player_1_id'   => $group->players[2]->id,
                    'player_2_id'   => $group->players[0]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[3]->id,
                    'player_2_id'   => $group->players[1]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
            ];
        }
        elseif($group->stage == '1/64' || $group->stage == '1/32' || $group->stage == '1/16' || $group->stage == '1/8' || $group->stage == '1/4'){
            $matchesToInsert = [
                [
                    'player_1_id'   => $group->players[0]->id,
                    'player_2_id'   => $group->players[1]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[1]->id,
                    'player_2_id'   => $group->players[0]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
            ];
        }
        elseif($group->stage == '1/2' || $group->stage == 'final' || $group->stage == 'gf_1/2' || $group->stage == 'gf_repechage_1/2' || $group->stage == 'gf_final' || $group->stage == 'gf_repechage_final' || $group->stage == 'gf_grand_final'){
            $matchesToInsert = [
                [
                    'player_1_id'   => $group->players[0]->id,
                    'player_2_id'   => $group->players[1]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[1]->id,
                    'player_2_id'   => $group->players[0]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
                [
                    'player_1_id'   => $group->players[0]->id,
                    'player_2_id'   => $group->players[1]->id,
                    'stage'         => $group->stage,
                    'datetime'      => $group->date,
                    'status'        => 'upcoming',
                    'room'          => $group->room,
                ],
            ];
        }

        try{
            Match::insert($matchesToInsert);
        }
        catch(\Exception $e){
            return response()
                ->json(
                    ['status' => 'pizdec', 'message' => $e->getMessage()],
                    500
                );
        }

        return ['status' => 'OK'];
    }

    public function advanceToNextStage($groupId, Request $request){
        if(!$this->user){
            return abort(401);
        }

        if(!$this->user->hasRole('cybersport_admin') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $group = Group::findOrFail($groupId);

        if($group->stage == 'group'){
            $firstPlayerNextGroupId = explode(':', $group->next_group_ids)[0];
            $secondPlayerNextGroupId = explode(':', $group->next_group_ids)[1];

            Group::findOrFail($firstPlayerNextGroupId)->players()->attach($request->get('player_ids')[0]);
            Group::findOrFail($secondPlayerNextGroupId)->players()->attach($request->get('player_ids')[1]);

            return ['status' => 'OK'];
        }
        elseif($group->stage == '1/64'){
            Group::findOrFail($group->next_group_ids)->players()->attach($request->get('player_ids')[0]);

            return ['status' => 'OK'];
        }
        else{
            return ['status' => 'OK'];
        }
    }
}
