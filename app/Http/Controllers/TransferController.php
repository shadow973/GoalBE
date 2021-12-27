<?php

namespace App\Http\Controllers;

use App\Models\ApiLeagues;
use App\Models\Transfers\Transfer;
use App\Models\Transfers\TransferSeason;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class TransferController extends Controller
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
        $seasonId = $request->season_id;

        if(!$seasonId) $seasonId = TransferSeason::latest()->first()->id;

        if(!$seasonId) return [];

        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 25;

        $transfers = Transfer::where('transfer_season_id', $seasonId)
            ->with(['player', 'fromTeam', 'toTeam'])
            ->orderByDesc('top_transfer_order');

        if($request->top) $transfers->where('top_transfer_order', '>', 0);

        if($request->league_id) {
            $transfers->whereHas('leagues', function($q) use ($request) {
                $q->where('transfer_leagues.league_id', $request->league_id);
            });
        }

        if($request->team_id) $transfers->where(function($q) use($request) {
            $q->where('from_team_id', $request->team_id);
            $q->orWhere('to_team_id', $request->team_id);
        });

        return $transfers->paginate($perPage);
    }

    public function getLeagues() {
        $leagues = ApiLeagues::
            where('transfer_top_league', true)->selectRaw('
        api_leagues.league_id,
        name
        ')->get();

        return $leagues;
    }

    public function store(Request $request){
        if(!$this->user || !$this->user->hasRole('admin')){
            abort(401);
        }

        $leagues = explode(',', $request->league_ids);
        $data = $request->except(['league_ids', 'status']);
        $data['top_transfer_order'] = isset($data['top_transfer_order']) ? 1 : 0;
        $transfer = Transfer::create($data);
        $transfer->leagues()->sync($leagues);
        return $transfer->load('leagues');
    }

    public function show($id){
        return Transfer::findOrFail($id)
            ->load(['player', 'fromTeam', 'toTeam', 'leagues', 'season']);
    }

    public function update($id, Request $request){
        if(!$this->user || !$this->user->hasRole('admin')){
            abort(401);
        }

        $transfer = Transfer::findOrFail($id);

        $leagues = explode(',', $request->league_ids);
        $transfer->update($request->except(['league_ids', 'status']));
        $transfer->leagues()->sync($leagues);

        return $transfer->load(['player', 'leagues', 'fromTeam', 'toTeam', 'season']);
    }

    public function destroy($id){
        if(!$this->user || !$this->user->hasRole('admin')){
            abort(401);
        }

        Transfer::findOrFail($id)
            ->delete();
    }
}
