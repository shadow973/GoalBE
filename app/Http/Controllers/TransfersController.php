<?php

namespace App\Http\Controllers;

use App\Transfer;
use Illuminate\Http\Request;
use App\Tag;
use JWTAuth;

class TransfersController extends Controller
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
        $transfers = Transfer::where('date', '>=', $request->get('date_from'))
            ->where('date', '<=', $request->get('date_till'))
            ->with(['player', 'fromTeam', 'toTeam'])
            ->orderBy('date', 'desc');

        if(str_contains($request->get('options'), 'paginate')){
            return $transfers->paginate(25);
        }

        return $transfers->get();
    }

    public function store(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('transfers_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'date' => 'date',
            'amount' => '',
            'type' => '',
            'from_team_tag_id' => 'required_without:from_team_tag',
            'from_team_tag' => 'required_without:from_team_tag_id',
            'to_team_tag_id' => 'required_without:to_team_tag',
            'to_team_tag' => 'required_without:to_team_tag_id',
            'player_tag_id' => 'required_without:player_tag',
            'player_tag' => 'required_without:player_tag_id',
        ]);

        $transfer = new Transfer($request->all());

        if($request->get('player_tag')){
            $playerTag = new Tag([
                'title' => $request->get('player_tag'),
            ]);
            $playerTag->save();
            $transfer->player_tag_id = $playerTag->id;
        }

        if($request->get('from_team_tag')){
            $fromTeamTag = new Tag([
                'title' => $request->get('from_team_tag'),
            ]);
            $fromTeamTag->save();
            $transfer->from_team_tag_id = $fromTeamTag->id;
        }

        if($request->get('to_team_tag')){
            $toTeamTag = new Tag([
                'title' => $request->get('to_team_tag'),
            ]);
            $toTeamTag->save();
            $transfer->to_team_tag_id = $toTeamTag->id;
        }

        $transfer->save();

        return Transfer::find($transfer->id);
    }

    public function show($id){
        return Transfer::findOrFail($id)
            ->load(['player', 'fromTeam', 'toTeam']);
    }

    public function update($id, Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('transfers_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'date' => 'date',
            'amount' => '',
            'type' => '',
            'from_team_tag_id' => 'required_without:from_team_tag',
            'from_team_tag' => 'required_without:from_team_tag_id',
            'to_team_tag_id' => 'required_without:to_team_tag',
            'to_team_tag' => 'required_without:to_team_tag_id',
            'player_tag_id' => 'required_without:player_tag',
            'player_tag' => 'required_without:player_tag_id',
        ]);

        $transfer = Transfer::findOrFail($id);
        $data = $request->all();

        if($request->get('player_tag')){
            $playerTag = new Tag([
                'title' => $request->get('player_tag'),
            ]);
            $playerTag->save();
            $data['player_tag_id'] = $playerTag->id;
        }

        if($request->get('from_team_tag')){
            $fromTeamTag = new Tag([
                'title' => $request->get('from_team_tag'),
            ]);
            $fromTeamTag->save();
            $data['from_team_tag_id'] = $fromTeamTag->id;
        }

        if($request->get('to_team_tag')){
            $toTeamTag = new Tag([
                'title' => $request->get('to_team_tag'),
            ]);
            $toTeamTag->save();
            $data['to_team_tag_id'] = $toTeamTag->id;
        }

        $transfer->update($data);

        return Transfer::find($id);
    }

    public function destroy($id){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('transfers_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        Transfer::findOrFail($id)
            ->delete();
    }
}
