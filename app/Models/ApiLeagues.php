<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApiLeagues extends Model
{
    protected $table = 'api_leagues';
    protected $connection =  LIVESCORE_CONNECTION;

    protected $appends = ['icon'];

    protected $primaryKey = 'league_id';

    public function getNameAttribute() {
        return isset($this->attributes['name_geo']) && $this->attributes['name_geo'] != '' ? $this->attributes['name_geo'] : $this->attributes['name'];
    }

    public function getNameEngAttribute() {
        return $this->attributes['name'];
    }

    public function teams() {
        return ApiTeams::join('api_team_leagues', 'api_teams.team_id', '=', 'api_team_leagues.team_id')
            ->where('api_team_leagues.league_id', $this->league_id)->where('api_team_leagues.season_id', $this->current_season_id);
    }

    public function getArticles($limit = 30){
        $id = $this->league_id;
        $articles = \App\Article::with('mainGalleryItem', 'author', 'categories',  'matche')->Where('id','>',0)->whereIn('id',function ($q)use($id){
            $q->select('article_id')->from('article_league')->where('league_id',$id);
        })->orderBy('id','desc')->paginate($limit);


        return $articles;
    }

    public function getTeamArticles($limit = 30){
        $season_id = $this->current_season_id;

        $articles = \App\Article::with('mainGalleryItem', 'author', 'categories', 'matche')->Where('id','>',0)->whereIn('id',function ($q)use($season_id){
            $q->select('article_id')->from('article_team')->whereIn('team_id',function ($qq) use ($season_id){
                $qq->select('team_id')->from('api_teams')->where('current_season_id', $season_id);
            });
        })->orderBy('id','desc')->limit($limit)->paginate(30);

        return $articles;
    }

    public function league()
    {
        return $this->hasOne('App\League', 'league_id', 'league_id');
    }

    public function country()
    {
        return $this->hasOne('App\Models\ApiCountries', 'country_id', 'country_id');
    }

    public function getIconAttribute() {
        return env('APP_URL').'/api/league-icons/'.$this->league_id.'.png';
        //return 'https:'.env('STORAGE_URL').'/images/countries/flags/'.$this->country_id.'.svg'; TODO
    }

    public function matchesByRound($season, $stage, $round)
    {
        $matches = \App\Models\ApiMatches::where('api_matches.id','>',1)
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

            lt.`name_geo` as localteam_name,
            lt.short_code as localteam_short_code,
            lt.logo_path as localteam_logo_path,
            vt.`name_geo` as visitorteam_name,
            vt.short_code as visitorteam_short_code,
            vt.logo_path as visitorteam_logo_path
            
         '));

        if((!$stage && !$round) || !$season) return [];

        if($season > 0){
            $matches->where('api_matches.season_id', $season);
        }

        if($stage > 0){
            $matches->where('api_matches.stage_id', $stage);
        }

        if($round > 0){
            $matches->where('api_matches.round_id', $round);
        }

        $matches->orderBy('api_matches.starting_at','asc');
        return $matches->get();
    }

    public function matches($page = 0, $order = 1, $perPage = 10)
    {
        $matches = \App\Models\ApiMatches::where('api_matches.season_id',$this->current_season_id)
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

            lt.`name` as localteam_name,
            lt.short_code as localteam_short_code,
            lt.logo_path as localteam_logo_path,
            vt.`name` as visitorteam_name,
            vt.short_code as visitorteam_short_code,
            vt.logo_path as visitorteam_logo_path
            
         '));

        if($page == 0){
            $matches_old = clone($matches);

            $matches_old = $matches_old->where('api_matches.starting_at','<',DB::raw('now()'))
            ->orderBy('api_matches.starting_at','desc')->limit(8)->get();



           $matches = $matches->where('api_matches.starting_at','>',DB::raw('now()'))
           ->orderBy('api_matches.starting_at','asc')->limit(8)->get();

           // return $matches;
           $matches_arr = [ 'current_page' => 1, 'data' => []];

           for ($i=count($matches_old) - 1; $i > 0 ; $i--) {
               $matches_arr['data'][] = $matches_old[$i];
           }


           foreach ($matches as $m) {
               $matches_arr['data'][] = $m;
           }
            return $matches_arr;
        }


        // $matches->where('api_matches.starting_at','>',DB::raw('now()'));

        if($order > 0){
            $matches->where('api_matches.starting_at','>',DB::raw('now()'));
        }else{
            $matches->where('api_matches.starting_at','<',DB::raw('now()'));
        }

        $matchnotin = clone($matches);

        if($order > 0){
            $matchnotin->orderBy('api_matches.starting_at','asc');
        }else{
            $matchnotin->orderBy('api_matches.starting_at','desc');
        }

        $matchnotin = $matchnotin->limit(8)->get();

        $notinarr = [];

        foreach ($matchnotin as $m) {
            $notinarr[] = $m->match_id;
        }


        $matches->whereNotIn('api_matches.match_id',$notinarr);


        if($order > 0){
            $matches->orderBy('api_matches.starting_at','asc');
        }else{
            $matches->orderBy('api_matches.starting_at','desc');
        }



        // return $matches->toSql();

        return $matches->paginate($perPage);
    }

    public function goalscorers()
    {
        // return $this->hasMany('App\Models\ApiGoalscorers', 'season_id', 'current_season_id');

        $st = \App\Models\ApiGoalscorers::where('season_id',$this->current_season_id)
        // ->join('api_teams_players as tp', 'tp.player_id','=','api_goalscorers.player_id')
        ->join('api_teams_players as tp', function($join){
            $join->on('tp.player_id','=','api_goalscorers.player_id');
            //$join->on('tp.status_id','=',DB::raw("1"));
        })
            ->distinct()
        ->addSelect(DB::raw('
            api_goalscorers.*,

            tp.`common_name` as player_name,
            tp.`image_path` as player_image,
            tp.`slug` as slug

            
         '))
            //->where('tp.status_id', 1)
        ->orderBy('api_goalscorers.position','ASC')
        ->limit(10)
//        ->toSql();
        ->get()->toArray();
        return $st;
    }

    public function assistscorers()
    {
        // return $this->hasMany('App\Models\ApiAssistscorers', 'season_id', 'current_season_id');

        $st = \App\Models\ApiAssistscorers::where('season_id',$this->current_season_id)
        ->leftjoin('api_teams_players as tp', 'tp.player_id','=','api_assistscorers.player_id')
            ->distinct()
            ->addSelect(DB::raw('
            api_assistscorers.*,

            tp.`common_name` as player_name,
            tp.`image_path` as player_image,
            tp.`slug` as slug

            
         '))
        //->where('tp.status_id', 1)
        ->orderBy('api_assistscorers.position','ASC')
        ->limit(10)
        // ->toSql();
        ->get()->toArray();
        return $st;
    }

    public function cardscorers()
    {
        // return $this->hasMany('App\Models\ApiCardscorers', 'season_id', 'current_season_id');

        $st = \App\Models\ApiCardscorers::where('season_id',$this->current_season_id)
            ->leftjoin('api_teams_players as tp', 'tp.player_id','=','api_cardscorers.player_id')
            ->distinct()
            ->addSelect(DB::raw('
            api_cardscorers.*,

            tp.`common_name` as player_name,
            tp.`image_path` as player_image,
            tp.`slug` as slug

            
         '))
        //->where('tp.status_id', 1)
        ->orderBy('api_cardscorers.position','ASC')
        ->limit(10)
        // ->toSql();
        ->get()->toArray();
        return $st;
    }



    public function standings(){
        $st = \App\Models\ApiStandings::where('league_id', $this->league_id);

        if($st->count() == 0){
            return [];
        }

        $st = $st->get()->toArray();

        $new = [];
        foreach ($st as $v) {
            $standings = $v['standings'];
            if(empty($v['standings'])){
                $v['standings'] = [];
                $new[] = $v;
                continue;
            }


            $standings = json_decode($standings);
            $v['standings'] = $standings->data;

            $new[] = $v;

        }

        return $new;
    }

    public function seasons($short = false){
        $seasons = \App\Models\ApiSeasons::where('league_id', $this->league_id)
            ->orderByDesc('name')
            ->take(5)->get();

        if($short) $seasons->makeHidden('stats');

        return $seasons;
    }

    public function rounds(){
        return \App\Models\ApiRounds::where('season_id', $this->current_season_id)->get();
        // return $this->current_season_id;
        // return $this->hasMany('App\Models\ApiRounds', 'season_id', 'current_season_id')->toSql(). ' ==== '.$this->current_season_id;
        return $this->hasMany('App\Models\ApiRounds', 'season_id', 'current_season_id');
    }

    public function stages() {
        return ApiStage::where('league_id', $this->league_id)->where('season_id', $this->current_season_id)->get();
    }

    public function getShareLink() {
        return 'https://goal.ge/championship/'.$this->league_id.'/'.$this->slug.'/1';
    }


    public function getNationalTeam() {
        return ApiTeams::where('country_id', $this->country_id)
            ->where('national_team', 1)
            ->where('name_geo', 'not like', '%W.')
            ->where('name_geo', 'not like', '%U21%')
            ->first();
    }

    public function getTeams() {
        $query = $this->teams()
            ->select(['api_teams.team_id', 'name', 'name_geo', 'logo_path', 'slug'])
            ->distinct()
            ->orderByDesc('top_team_order');

        return $query->get()->map(function ($item) {
            return $item->append('link');
        });
    }

    public static function teamLeagues() {
        return DB::table('api_team_leagues as tl')
            ->join('api_teams as t', 't.team_id', '=', 'tl.team_id')
            ->join('api_leagues as l', 'l.league_id', '=', 'tl.league_id')
            ->join(DB::raw('(select league_id, team_id, max(season_id) as season_id from api_team_leagues group by league_id, team_id) as tl2'),
            function($join) {
                $join->on('tl.team_id', '=', 'tl2.team_id');
                $join->on('tl.league_id', '=', 'tl2.league_id');
                $join->on('tl.season_id', '=', 'tl2.season_id');
            })
            ->where('l.live_standings', true)
            ->select([
                't.team_id',
                't.name_geo as team_name',
                't.country_id as team_country_id',
                't.logo_path as team_image',
                't.current_season_id as team_season_id',
                't.slug as team_slug',

                'l.league_id',
                'l.name_geo as league_name',
                'l.country_id',
                'l.current_season_id as league_season_id',
                'l.slug as league_slug',
                'tl.season_id',
                'tl.stats',
            ]);
    }
}
