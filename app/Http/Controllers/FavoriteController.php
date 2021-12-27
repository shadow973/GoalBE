<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JWTAuth;
use App;

class FavoriteController extends Controller
{
    protected $user;

    public function __construct(){
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function getRandomID() {
        $random = rand(10000000, 99999999);

        while(App\UserFavorite::where('device_id', $random)->count() > 0) {
            $random = $this->getRandomID();
        }

        return $random;
    }

    public function getDeviceId() {
        return App\UserFavorite::create([
            'user_id' => $this->user ? $this->user->id : null,
            'device_id' => $this->getRandomID()
        ]);
    }

    public function get(Request $request) {
        if(!$this->user && !$request->device_id) abort(500);

        $userId = $this->user ? $this->user->id : null;

        $favorites = App\UserFavorite::getFavorites($request->device_id, $userId);

        return $favorites->getByLeagues();
    }

    public function getLeagues(Request $request) {
        if(!$this->user && !$request->device_id) abort(500);

        if($this->user) return App\UserFavorite::leaguesByUser($this->user->id);

        return App\UserFavorite::leaguesByDevice($request->device_id);
    }

    public function getTeams(Request $request) {
        if(!$this->user && !$request->device_id) abort(500);

        if($this->user) return App\UserFavorite::teamsByUser($this->user->id);

        return App\UserFavorite::teamsByDevice($request->device_id);
    }

    public function getPlayers(Request $request) {
        if(!$this->user && !$request->device_id) abort(500);

        if($this->user) return App\UserFavorite::playersByUser($this->user->id);

        return App\UserFavorite::playersByDevice($request->device_id);
    }

    public function removeMatch(Request $request) {
        $userId = null;
        if($this->user) $userId = $this->user->id;

        if(!$request->device_id || !$request->match_id) abort(500);

        $favorite = App\UserFavorite::getFavorites($request->device_id, $userId);

        $matchIds = $favorite->match_ids;
        $pos = array_search($request->match_id, $matchIds);
        if($pos !== false) unset($matchIds[$pos]);

        $newMatches = [];
        foreach ($matchIds as $matchId) $newMatches[] = $matchId;

        $favorite->update([
            'fav_matches' => json_encode($newMatches)
        ]);

        return response()->json(['success' => 'true']);
    }

    public function saveMatch(Request $request) {
        $userId = null;
        if($this->user) $userId = $this->user->id;

        if(!$request->device_id || !$request->match_id) abort(500);

        $favorite = App\UserFavorite::getFavorites($request->device_id, $userId);

        $matchIds = $favorite->match_ids;
        if(!in_array($request->match_id, $matchIds)) {
            $matchIds[] = $request->match_id;
        }

        $favorite->update([
            'fav_matches' => json_encode($matchIds)
        ]);

        return response()->json(['success' => true]);
    }

    public function save(Request $request) {
        $userId = null;
        if($this->user) $userId = $this->user->id;

        if(!$request->device_id) abort(500);

        $leagueIds = [];
        if($request->league_ids) {
            $leagueIds = json_decode($request->league_ids);
            if(json_last_error() != JSON_ERROR_NONE) return abort(500);
        }

        $teamIds = [];
        if($request->team_ids) {
            $teamIds = json_decode($request->team_ids);
            if(json_last_error() != JSON_ERROR_NONE) return abort(500);
        }

        $playerIds = [];
        if($request->player_ids) {
            $playerIds = json_decode($request->player_ids);
            if(json_last_error() != JSON_ERROR_NONE) return abort(500);
        }

        $favorite = App\UserFavorite::getFavorites($request->device_id, $userId);
        $favorite->update([
            'fav_leagues' => json_encode($leagueIds),
            'fav_teams' => json_encode($teamIds),
            'fav_players' => json_encode($playerIds)
        ]);
    }

    public function saveLeagues(Request $request) {
        $userId = null;
        if($this->user) $userId = $this->user->id;

        if(!$request->device_id) abort(500);

        $leagueIds = json_decode($request->league_ids);
        if(json_last_error() != JSON_ERROR_NONE) return abort(500);

        $favorite = App\UserFavorite::getFavorites($request->device_id, $userId);
        $favorite->update(['fav_leagues' => json_encode($leagueIds)]);
    }

    public function saveTeams(Request $request) {
        $userId = null;
        if($this->user) $userId = $this->user->id;

        if(!$request->device_id) abort(500);

        $teamIds = json_decode($request->team_ids);
        if(json_last_error() != JSON_ERROR_NONE) return abort(500);

        $favorite = App\UserFavorite::getFavorites($request->device_id, $userId);
        $favorite->update(['fav_teams' => json_encode($teamIds)]);
    }

    public function savePlayers(Request $request) {
        $userId = null;
        if($this->user) $userId = $this->user->id;

        if(!$request->device_id) abort(500);

        $playerIds = json_decode($request->player_ids);
        if(json_last_error() != JSON_ERROR_NONE) return abort(500);

        $favorite = App\UserFavorite::getFavorites($request->device_id, $userId);
        $favorite->update(['fav_players' => json_encode($playerIds)]);
    }
}
