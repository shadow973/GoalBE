<?php

namespace App\Models;

use App\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApiTeams extends Model
{

    protected $primaryKey = 'team_id';
    protected $connection =  'mysql';

    // protected $hidden = ['pivot'];
    protected $table = 'api_teams';

    public function getMainLeague() {
        $countryId = $this->country_id;
        return $this->leagues()->join('api_leagues', function($join) use ($countryId) {
            $join->on('api_leagues.league_id', '=', 'api_team_leagues.league_id');
            $join->on('api_leagues.current_season_id', '=', 'api_team_leagues.season_id');
            $join->where('api_leagues.country_id', '=', $countryId);
        })->where('live_standings', true)->where('is_cup', 0)->first();
    }

    public function getStandingsQuery() {
        $teamId = $this->team_id;
        $standingsQuery = ApiTeamsStandings::join('api_team_leagues', function($join) use ($teamId) {
                $join->on('api_teams_standings.league_id', '=', 'api_team_leagues.league_id');
                $join->on('api_teams_standings.season_id', '=', 'api_team_leagues.season_id');
                $join->where('api_team_leagues.team_id', $teamId);
            })
            ->join('api_leagues', function($join) {
                $join->on('api_leagues.league_id', '=', 'api_team_leagues.league_id');
                $join->on('api_leagues.current_season_id', '=', 'api_team_leagues.season_id');
                $join->where('api_leagues.live_standings', true);
            })
            ->selectRaw('api_teams_standings.*')
        ;

        return $standingsQuery;
    }

    public function getStandings() {
        $standings = $this->getStandingsQuery()->orderBy('position')->get();
        $leagueIds = $standings->unique('league_id')->pluck('league_id')->values();

        $data = collect();
        foreach ($leagueIds as $leagueId) {
            $league = ApiLeagues::where('league_id', $leagueId)->select(['name', 'name_geo', 'league_id', 'country_id', 'slug'])->first()->append('icon');
            if($this->getMainLeague()->league_id == $league->league_id) $league->is_main_league = true;
            else $league->is_main_league = false;

            $standingsData = $standings->where('league_id', $leagueId);

            $data->push([
                'league' => $league,
                'standings' => $standingsData
            ]);
        }

        return $data;
    }

    public function standings(){
        return $this->hasMany('App\Models\ApiTeamsStandings', 'team_id', 'team_id');
    }

    public function standingsByseason(){
        return $this->hasMany('App\Models\ApiTeamsStandings', 'season_id', 'current_season_id', 'position')->orderBy('position');
    }

    public function players(){
        return $this->hasMany('App\Models\ApiTeamsPlayers', 'team_id', 'team_id');
    }

    public function club(){
        return $this->hasOne('App\Models\Clubs', 'team_id', 'team_id');
    }

    public function getLogoPlaceholder() {
        return 'https://cdn.sportmonks.com/images/soccer/team_placeholder.png';
    }

    public function country() {
        return $this->belongsTo('App\Models\ApiCountries', 'country_id', 'country_id');
    }

    public function articles() {
        return $this->belongsToMany(Article::class, 'article_team', 'team_id', 'article_id', 'team_id')
            ->with(['mainGalleryItem']);
    }

    public function getTopPlayers($limit = 5) {
        return $this->players->makeHidden(['data', 'stats'])->sortByDesc('rating')->values()->take($limit);
    }

    public function getTopGoalscorerPlayers($limit = 5) {
        return $this->players->makeHidden(['data', 'stats'])->sortByDesc('goals')->values()->take($limit);
    }

    public function getTopAssistPlayers($limit = 5) {
        return $this->players->makeHidden(['data', 'stats'])->sortByDesc('assists')->values()->take($limit);
    }

    public function getTopCardPlayers($limit = 5) {
        return $this->players->makeHidden(['data', 'stats'])->sortByDesc('cards')->values()->take($limit);
    }

    public function getArticles($limit = 30, $cachePrefix = null){
        $id = $this->team_id;

        $articles = \App\Article::with('mainGalleryItem', 'author', 'categories', 'matche')->Where('id','>',0)->whereIn('id',function ($q)use($id){
            $q->select('article_id')->from('article_team')->where('team_id',$id);
        })->orderBy('id','desc')->paginate($limit);

        return $articles;
    }

    // public function getMatches(){
    //     $matches = \App\Models\ApiMatches::where('localteam_id',$this->team_id)->orWhere('visitorteam_id',$this->team_id)
    //         ->orderBy('starting_date','asc')->get();
    //     return $matches;
    // }

    public function getOldAndFutureMatches($matches, $limit) {
        $matches_old = clone($matches);
        $matches_new = clone($matches);

        $matches_old = $matches_old->where('api_matches.starting_at','<',DB::raw('now()'))
            ->orderBy('api_matches.starting_at','desc')->limit($limit/2)->get()->pluck('match_id')->toArray();

        $matches_new = $matches_new->where('api_matches.starting_at','>',DB::raw('now()'))
            ->orderBy('api_matches.starting_at','asc')->limit($limit/2)->get()->pluck('match_id')->toArray();

        return $matches->whereIn('match_id', array_merge($matches_new, $matches_old))->paginate($limit);
    }

    public function getFutureMatches($matches, $limit) {
        $matches = $matches->where('api_matches.starting_at','>',DB::raw('now()'));
        $matches->orderBy('api_matches.starting_at','asc')->limit($limit);

        return $matches->paginate($limit);
    }

    public function getOldMatches($matches, $limit) {
        $matches->where('api_matches.starting_at','<',DB::raw('now()'));
        $matches->orderBy('api_matches.starting_at','desc')->limit($limit);

        return $matches->paginate($limit);
    }



    public function matches($page = 0, $order = 1, $short = true, $perPage = 16)
    {

        $id = $this->team_id;
        $matches = \App\Models\ApiMatches::where(function($q) use ($id)
        {
           $q->where('api_matches.localteam_id',$id )->orWhere('api_matches.visitorteam_id',$id);
        })
        // where('api_matches.season_id',$this->current_season_id)
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

            if(length(lt.`name_geo`) > 0, lt.name_geo, lt.name) as localteam_name,
            lt.short_code as localteam_short_code,
            lt.logo_path as localteam_logo_path,
            if(length(vt.`name_geo`) > 0, vt.name_geo, vt.name) as visitorteam_name,
            vt.short_code as visitorteam_short_code,
            vt.logo_path as visitorteam_logo_path
            
         '));

        if($order == 0){
            return $this->getOldAndFutureMatches($matches, $perPage);
        } elseif ($order > 0) {
            return $this->getFutureMatches($matches, $perPage);
        }

        return $this->getOldMatches($matches, $perPage);
    }

    public function leagues() {
        return $this->hasMany(ApiTeamLeagues::class, 'team_id', 'team_id');
    }

    public function getShareLink() {
        return 'https://goal.ge/club/'.$this->team_id.'/'.$this->slug.'/1';
    }

    public function getLinkAttribute() {
        return $this->getShareLink();
    }

    public function getNameAttribute() {
        return isset($this->attributes['name_geo']) && $this->attributes['name_geo'] != '' ? $this->attributes['name_geo'] : $this->attributes['name'];
    }

    public function getShortData() {
        return [
            'team_id' => $this->team_id,
            'name' => $this->name,
            'logo_path' => $this->logo_path,
            'link' => $this->link
        ];
    }
}
