<?php

namespace App;

use App\Models\ApiLeagues;
use App\Models\ApiMatches;
use App\Models\ApiTeams;
use App\Models\ApiTeamsPlayers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserFavorite extends Model
{
    protected $fillable = ['user_id', 'device_id', 'fav_leagues', 'fav_teams', 'fav_players', 'fav_matches'];

    public function getLeagueIdsAttribute() {
        return $this->fav_leagues ? json_decode($this->fav_leagues) : [];
    }
    public function getTeamIdsAttribute() {
        return $this->fav_teams ? json_decode($this->fav_teams) : [];
    }
    public function getPlayerIdsAttribute() {
        return $this->fav_players ? json_decode($this->fav_players) : [];
    }
    public function getMatchIdsAttribute() {
        return $this->fav_matches ? json_decode($this->fav_matches) : [];
    }

    public function getByLeagues() {
        $cache_time = 60;
        $cache_prefix = 'matches_index_'.$this->device_id;

        $favMatchIds = $this->match_ids;

        Cache::forget($cache_prefix);
        $matches_res = Cache::remember($cache_prefix, $cache_time, function () use ($favMatchIds) {
            $matches = ApiMatches::whereIn('api_matches.match_id', $favMatchIds)
                ->leftjoin('api_teams as lt', 'lt.team_id','=','api_matches.localteam_id')
                ->leftjoin('api_teams as vt', 'vt.team_id','=','api_matches.visitorteam_id')
                ->addSelect(DB::raw('
                api_matches.match_id,
                api_matches.league_id,
                api_matches.localteam_id,
                api_matches.visitorteam_id,
                api_matches.localteam_score,
                api_matches.visitorteam_score,
                api_matches.time,
                api_matches.time_status,
                api_matches.time_minute,
                api_matches.starting_at,
                api_matches.updated_at,
                api_matches.match_json,
                api_matches.time_status_priority,

                if(length(lt.`name_geo`) > 0, lt.`name_geo`, lt.`name`) as localteam_name,
                lt.short_code as localteam_short_code,
                lt.logo_path as localteam_logo_path,
                if(length(vt.`name_geo`) > 0, vt.`name_geo`, vt.`name`) as visitorteam_name,
                vt.short_code as visitorteam_short_code,
                vt.logo_path as visitorteam_logo_path
             '))
                ->orderBy('api_matches.time_status_priority')->orderBy('api_matches.starting_at')->get()->toArray();
            $byleague = [];
            $byleague_n = [];
            foreach ($matches as $m){
                $jsn = json_decode($m['match_json']);
                $json_data = [
                    'highlights' => isset($jsn->highlights)?$jsn->highlights:[]
                ];

                unset($m['match_json']);

                $byleague[$m['league_id']][]= $m;
            }

            $leagues = ApiLeagues::where('api_leagues.id','>',0)
                ->leftjoin('league_l as l', function($join) {
                    $join->on('l.league_id','=','api_leagues.league_id');
                    $join->where('l.status_id','>','-1');
                })->addSelect(DB::raw('
                    distinct 
                    api_leagues.league_id, api_leagues.country_id,
                    l.priority, api_leagues.current_stage_id,
                   api_leagues.name_geo as name, api_leagues.slug                
                 '))
                ->orderBy('l.priority','desc')->get()->toArray();

            foreach ($leagues as $v) {
                if(isset($byleague[$v['league_id']])){
                    $byleague_n[]=[
                        'league_id' => $v['league_id'],
                        'country_id' => $v['country_id'],
                        'league_name' => $v['name'],
                        'slug' => $v['slug'],
                        'icon' => env('APP_URL').'/api/league-icons/'.$v['league_id'].'.png',
                        //'icon' => 'https:'.env('STORAGE_URL').'/images/countries/flags/'.$v['country_id'].'.svg', TODO
                        'data' => collect($byleague[$v['league_id']])->values()
                    ];
                }
            }

            return $byleague_n;

        });

        return $matches_res;
    }

    public function getLeagues() {
        return ApiLeagues::whereIn('league_id', $this->league_ids)->get(['league_id', 'name', 'slug']);
    }

    public function getTeams() {
        return ApiTeams::whereIn('team_id', $this->team_ids)->get(['team_id', 'name', 'slug', 'logo_path']);
    }

    public function getPlayers() {
        return ApiTeamsPlayers::whereIn('player_id', $this->player_ids)->get(['player_id', 'common_name', 'fullname', 'slug', 'image_path']);
    }

    public static function getFavorites($deviceId, $userId) {
        $favorite = UserFavorite::firstOrCreate(['device_id' => $deviceId]);

        if($userId) $favorite->update(['user_id' => $userId]);

        return $favorite;
    }

    private static function getData($favorites, $favTypes) {
        $data = collect();
        if($favorites) {
            $data = collect([
                'leagues' => in_array('leagues', $favTypes) ? $favorites->getLeagues() : [],
                'teams' => in_array('teams', $favTypes) ? $favorites->getTeams() : [],
                'players' => in_array('players', $favTypes) ? $favorites->getPlayers() : []
            ]);
        }

        return $data;
    }

    public static function byUser($userId, $favTypes = null) {
        if(!$favTypes) $favTypes = ['leagues', 'teams', 'players'];

        $favorites = UserFavorite::where('user_id', $userId)->first();

        return UserFavorite::getData($favorites, $favTypes);
    }

    public static function byDevice($deviceId, $favTypes = null) {
        if(!$favTypes) $favTypes = ['leagues', 'teams', 'players'];

        $favorites = UserFavorite::where('device_id', $deviceId)->first();

        return UserFavorite::getData($favorites, $favTypes);
    }

    public static function leaguesByUser($userId) {
        return UserFavorite::byUser($userId, ['leagues']);
    }

    public static function leaguesByDevice($deviceId) {
        return UserFavorite::byDevice($deviceId, ['leagues']);
    }

    public static function teamsByUser($userId) {
        return UserFavorite::byUser($userId, ['teams']);
    }

    public static function teamsByDevice($deviceId) {
        return UserFavorite::byDevice($deviceId, ['teams']);
    }

    public static function playersByUser($userId) {
        return UserFavorite::byUser($userId, ['players']);
    }

    public static function playersByDevice($deviceId) {
        return UserFavorite::byDevice($deviceId, ['players']);
    }
}
