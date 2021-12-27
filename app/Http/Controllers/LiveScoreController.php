<?php

namespace App\Http\Controllers;

use App\Article;
use App\Models\ApiSeasons;
use App\Models\ApiTeamLeagues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use \App\Models\ApiMatches;
use \App\Models\ApiTeams;
use \App\Models\ApiCountries;
use \App\Models\ApiLeagues;
use App\Models\ApiStandings;
use App\Models\ApiTeamsStandings;
use Illuminate\Support\Facades\DB;
use App\Models\ApiTeamsPlayers;
use Illuminate\Support\Facades\Cache;

class LiveScoreController extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function rateMatche(){
        if(isset($this->request->answer)){
            $rate = new \App\Models\MatchRate();
            $rate->match_id = $this->request->id;
            $rate->answer = $this->request->answer;
            $rate->save();
            return $rate->id;
        }

        return 'error';
    }

    public function GetMatches(){
        // die('123');
        if (
            (
                !isset($this->request->date) || (isset($this->request->date) && $this->request->date == date('Y-m-d'))
            )
            && !isset($this->request->league)
            && !isset($this->request->round)
            && !isset($this->request->season)
        ){
            if(isset($this->request->byleague) && !isset($this->request->docache)){
                if(file_exists(base_path().'/current_day_json.json')){
                    $json = file_get_contents(base_path().'/current_day_json.json');
                    $json = json_decode($json);
                    return $json;
                }
            }
        }
        // if(file_exists(base_path().'/current_day_json.json')){
        //     // die('123');
        //     $json = file_get_contents(base_path().'/current_day_json.json');
        //     $json = json_decode($json);
        //     return $json;
        // }

        $request = $this->request;
        $request_all = $request->all();
        if(isset($this->request->id)){
            $request_all['match_id_'] = $this->request->id;
        }
        // print_r($request_all);
        // die;

        $cashe_index = implode('__', $request_all);
        //$cache_time = 60 * 60;
        $cache_time = 60;
        // $cache_time = 0;
        $cache_prefix = 'matches_index_';

        if(isset($this->request->id)){
            $cache_time = 1;
        }

        //Cache::forget($cache_prefix.$cashe_index);

        $matches_res = Cache::tags(['match-list'])->remember($cache_prefix.$cashe_index, $cache_time, function () use ($request) {

            if(isset($this->request->id)){
                $matches = ApiMatches::where('api_matches.match_id', $request->id)->with('article.mainGalleryItem');
                // => function($q) {
                //     $q->select(['title','image']);
                // }

            }elseif(isset($request->match_ids)) {
                $matches = ApiMatches::whereIn('api_matches.match_id', json_decode($request->match_ids, true));
            } else {

                $matches = ApiMatches::where('api_matches.id', '>', 0);

                if (isset($request->date)) {
                    $matches->date($request->date);
                } else {
                    if(!isset($request->round)){
                        // $matches->where('api_matches.starting_date', DB::raw('DATE(now())'));

                        $matches->where('api_matches.starting_at', '>', DB::raw('now() - INTERVAL 12 HOUR'));
                        $matches->where('api_matches.starting_at', '<',  DB::raw('date(now()) + INTERVAL \'24:01\' HOUR_MINUTE'));

                        // return $matches->toSql();
                    }
                }
            }

            if(isset($request->league) && (int)$request->league > 0){
                $matches->where('api_matches.league_id', (int)$request->league);
            }

            if(isset($request->round) && (int)$request->round > 0){
                $matches->where('api_matches.round_id', (int)$request->round);
            }

            if(isset($request->season) && (int)$request->season > 0){
                $matches->where('api_matches.season_id', (int)$request->season);
            }

            $matches =  $matches->leftjoin('api_teams as lt', 'lt.team_id','=','api_matches.localteam_id');
            $matches =  $matches->leftjoin('api_teams as vt', 'vt.team_id','=','api_matches.visitorteam_id');

            $matches =  $matches->addSelect(DB::raw('
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

                if(length(lt.`name_geo`) > 1, lt.name_geo, lt.name) as localteam_name,
                lt.short_code as localteam_short_code,
                lt.logo_path as localteam_logo_path,
                if(length(vt.`name_geo`) > 0, vt.name_geo, vt.name) as visitorteam_name,
                vt.short_code as visitorteam_short_code,
                vt.logo_path as visitorteam_logo_path

             '));


            if(isset($request->match_ids)) {
                $matches->whereIn('api_matches.match_id', json_decode($request->match_ids));
            }

            // print_r($matches->toSql());
            // die;


            if(isset($this->request->id)){
                $matches = $matches->get()->first();
                $matche = $matches->toArray();

                for($i=0;$i < count($matches['article']); $i++){
                    $matche['article'][$i]['content'] = \App\Models\ShortCode::ContentShortcode($matche['article'][$i]['content']);
                    $matche['article'][$i]['main_video'] = \App\Models\ShortCode::ContentShortcode($matche['article'][$i]['main_video']);
                }

                $match_json = json_decode($matche['match_json']);

                $pl_ids = [];
                if(isset($match_json->lineup->data))
                    foreach($match_json->lineup->data as $p){
                        $pl_ids[] = $p->player_id;
                    }

                $players = ApiTeamsPlayers::whereIn('player_id', $pl_ids)->where('status_id', '>', -1)->get();

                $player_imgs = [];
                foreach ( $players as $p) {
                    $dt = json_decode($p->data);

                    $cntr_id = isset($dt->player->data->country_id)?$dt->player->data->country_id:0;
                    $img_path = isset($dt->player->data->image_path)?$dt->player->data->image_path:'';

                    $player_imgs[$p->player_id] = ['country_id' =>$cntr_id, 'image_path' =>  $img_path ];
                }

                $matche['rate'] =  $matches->getRates();
                $matche['player_img_data'] = $player_imgs;

                return $matche;
            }else{
                $matches = $matches->orderBy('api_matches.time_status_priority')->orderBy('api_matches.starting_at')->get()->toArray();
            }

            if(isset($request->byleague)){
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
                            'unique_id' => $v['league_id'].'.'.$request->date,
                            'country_id' => $v['country_id'],
                            'league_name' => $v['name'],
                            'slug' => $v['slug'],
                            'icon' => env('APP_URL').'/api/league-icons/'.$v['league_id'].'.png',
                            //'icon' => 'https:'.env('STORAGE_URL').'/images/countries/flags/'.$v['country_id'].'.svg', TODO
                            'data' => collect($byleague[$v['league_id']])->values()
                        ];
                    }
                }

                // foreach ($byleague as $k => $v){
                //     $byleague_n[]=[
                //         'league_id' => $k,
                //         'data' => $v
                //     ];
                // }

                return $byleague_n;
            }

            return $matches;

        });

        $matches = collect($matches_res);

        if(isset($request->id)) return $matches;

        $page = $this->request->page ? $this->request->page : 1;
        $perPage = $this->request->per_page ? $this->request->per_page : 10;

        $data = $matches->forPage($page, $perPage);
        if($this->request->last_page) {
            $data = $matches->forPage(1, $perPage * $this->request->last_page);
        }

        return is_object($data) ? $data : $data->values();
    }

    public function cmp($a, $b)
    {
        return $b['priority'] - $a['priority'];
        // return strcmp($a['priority'], $b['priority']);
    }

    public function GetLeaguesMobile()
    {
        $league = [

            [ 'id'=>2, 'src'=>"https://storage.goal.ge//images/countries/flags/2.svg", 'name'=>'ჩემპიონთა ლიგა'],
            [ 'id'=>5, 'src'=>"https://storage.goal.ge//images/countries/flags/5.svg", 'name'=>'ევროპა ლიგა'],
            [ 'id'=>319, 'src'=>"https://storage.goal.ge//images/countries/flags/119.svg", 'name'=>'ეროვნული ლიგა'],
            [ 'id'=>8, 'src'=>"https://storage.goal.ge//images/countries/flags/462.svg", 'name'=>'პრემიერ ლიგა '],
            [ 'id'=>564, 'src'=>"https://storage.goal.ge//images/countries/flags/32.svg", 'name'=>'ლა ლიგა'],
            [ 'id'=>384, 'src'=>"https://storage.goal.ge//images/countries/flags/251.svg", 'name'=>'სერია ა'],
            [ 'id'=>82, 'src'=>"https://storage.goal.ge//images/countries/flags/11.svg", 'name'=>'ბუნდესლიგა'],
            [ 'id'=>301, 'src'=>"https://storage.goal.ge//images/countries/flags/17.svg", 'name'=>'ლიგა 1'],
            [ 'id'=>72, 'src'=>"https://storage.goal.ge//images/countries/flags/38.svg", 'name'=>'ერედივიზია'],
            [ 'id'=>486, 'src'=>"https://storage.goal.ge//images/countries/flags/227.svg", 'name'=>'პრემიერ ლიგა'],
            [ 'id'=>462, 'src'=>"https://storage.goal.ge//images/countries/flags/20.svg", 'name'=>'პრიმეირა ლიგა'],
            [ 'id'=>609, 'src'=>"https://storage.goal.ge//images/countries/flags/86.svg", 'name'=>'პრიმეირ ლიგა'],
            [ 'id'=>603, 'src'=>"https://storage.goal.ge//images/countries/flags/404.svg", 'name'=>'სუპერ ლიგა'],
            [ 'id'=>648, 'src'=>"https://storage.goal.ge//images/countries/flags/5.svg", 'name'=>'სერია ა'],
            [ 'id'=>636, 'src'=>"https://storage.goal.ge//images/countries/flags/44.svg", 'name'=>'სუპერლიგა '],
            [ 'id'=>732, 'src'=>"https://storage.goal.ge//images/countries/flags/732.jpg", 'name'=>'მსოფლიო ჩემპიონატი'],
            [ 'id'=>1325, 'src'=>"https://storage.goal.ge//images/countries/flags/1325.svg", 'name'=>'ევროპის ჩემპიონატი'],
            [ 'id'=>1538, 'src'=>"https://storage.goal.ge//images/countries/flags/1538.svg", 'name'=>'ერთა ლიგა'],
        ];

        return $league;

    }

    public function GetLeagues(){

        $request_all = $this->request->all();
        $request = $this->request;
        $cashe_index = implode('__', $request_all);

        //Cache::forget('1leagues_'.$cashe_index);

        $leagues_res = Cache::remember('1leagues_'.$cashe_index, 0, function () use ($request) {
            $listing = ApiLeagues::where('api_leagues.id','>', 0)
                //->where('l.status_id', '>', 0)
                ->leftjoin('api_countries as c', 'c.country_id','=','api_leagues.country_id')
                ->leftjoin('league_l as l', 'l.league_id','=','api_leagues.league_id');

            $listing = $listing->addSelect(DB::raw("
                api_leagues.league_id ,
                api_leagues.is_cup ,
                api_leagues.name_geo as name ,
                api_leagues.slug ,
                concat('".env('APP_URL')."/api/league-icons/', api_leagues.league_id, '.png') as image_path,
                api_leagues.name_geo as translated_name,
                l.priority as priority,
                api_leagues.current_season_id ,
                c.country_id as country_id,
                c.name as country_name,
                concat(c.name, ' - ', api_leagues.name_geo) as complete_name
             "));

            if(isset($request->is_cup)){
                $listing = $listing->where('api_leagues.is_cup', $request->is_cup);
            }

            if(isset($this->request->menu)){

                $listing = $listing->where('l.menu',1);
            }

            if(isset($this->request->haveTodayMatches)){
                $listing = $listing->whereRaw('EXISTS (
                SELECT
                    *
                FROM
                    `api_matches`
                WHERE
                    league_id = api_leagues.league_id
                    AND `api_matches`.`starting_at` > now() - INTERVAL 12 HOUR
                    AND `api_matches`.`starting_at` < date(
                    now()) + INTERVAL \'24:01\' HOUR_MINUTE
                )');
            }

            if(isset($request->search)) {
                $keyword = $request->search;

                $listing->where(function($q) use ($keyword) {
                    $q->where('api_leagues.name', 'like', '%'.$keyword.'%');
                    $q->orWhere('api_leagues.name_geo', 'like', '%'.$keyword.'%');
                });
            }

            $listing = $listing
                ->orderBy('l.priority','desc');
            // ->orderBy('api_leagues.league_id','desc')
            // ->orderBy('api_leagues.name','asc');

            $listing->distinct()->get()->toArray();

            $listing = $listing->get()->toArray();
            $ret = [
                'data' => $listing,
            ];

            return $ret;
        });

        return $leagues_res;
    }




    public function GetLeague()
    {

        $league = ApiLeagues::where('league_id',$this->request->id);

        if($league->count() == 0){
            return abort('404');
        }

        $league =$league->first();

        $league->country = $league->country;
        $league->league = $league->league;
        $league->goalscorers = $league->goalscorers();
        $league->assistscorers = $league->assistscorers();
        $league->cardscorers = $league->cardscorers();
        $league->articles = $league->getArticles();
        $league->matche_articles = $league->getTeamArticles();
        $league->standings = $league->standings();
        $league->seasons = $league->seasons(true);
        $league->rounds = $league->rounds();
        // $league->matches = $league->matches();

        return $league;

    }


    public function GetLeagueInfo()
    {
        $league = ApiLeagues::where('league_id',$this->request->id);

        if($league->count() == 0){
            return abort('404');
        }

        $cachePrefix = 'league_info_'.implode('__', $this->request->all());
        $cacheTime = 3600;
        $cacheTags = ['league_info', 'league_'.$this->request->id, 'league_info_'.$this->request->id];

        if($this->request->clear_cache == 1){
            Cache::tags($cacheTags)->flush();
        }

        $data = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function () use ($league) {
            $league =$league->first();

            $data = $league->toArray();
            $data['history'] = $league->league;
            $data['stages'] = $league->stages();
            $data['rounds'] = $league->rounds();
            $data['seasons'] = $league->seasons();
            $data['slug'] = $league->slug;
            $data['shareLink'] = $league->getShareLink();

            $nationalTeam = $league->getNationalTeam();
            $data['national_team'] = $nationalTeam ? $nationalTeam->getShortData() : null;
            $data['teams'] = $league->getTeams();

            return $data;
        });

        return $data;
    }

    public function GetTeamOverallStat(int $teamId) {
        $team = ApiTeams::where('team_id', $teamId)->firstOrFail();

        $data = is_object(json_decode($team->stats)) ? json_decode($team->stats) : [];

        return ['data' => $data];
    }

    public function GetLeagueArticles(Request $request)
    {
        $league = ApiLeagues::where('league_id',$this->request->id);

        if($league->count() == 0){
            return abort('404');
        }

        $league =$league->first();
        /*
                $league->country = $league->country;
                $league->league = $league->league;
                $league->goalscorers = $league->goalscorers();
                $league->assistscorers = $league->assistscorers();
                $league->cardscorers = $league->cardscorers();*/
        //$league->articles = $league->getArticles();
        /*        $league->matche_articles = $league->getTeamArticles();
                $league->standings = $league->standings();
                $league->seasons = $league->seasons();
                $league->rounds = $league->rounds();*/
        // $league->matches = $league->matches();


        return $league->getArticles($request->per_page);

    }

    public function GetLeagueMatches(Request $request)
    {

        $league = ApiLeagues::where('league_id',$this->request->id);

        if($league->count() == 0){
            return abort('404');
        }


        $league = $league->first();

        $order = 1;

        if(isset($this->request->order) && $this->request->order < 0){
            $order = -1;
        }

        $page = 0;
        if(isset($this->request->page)) $page = (int)$this->request->page;

        $perPage = 10;
        if(isset($this->request->per_page)) $perPage = $this->request->per_page;



        $cachePrefix = 'league_matches_'.implode('__', $this->request->all());
        $cacheTime = 3600;
        $cacheTags = ['league_'.$this->request->id, 'league_matches_'.$this->request->id];
        return Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function() use ($league, $perPage, $order, $page) {
            return $league->matches($page, $order, $perPage);
        });
    }

    public function GetLeagueMatchesByRound()
    {
        $league = ApiLeagues::where('league_id',$this->request->id);

        if($league->count() == 0){
            return abort('404');
        }

        $cachePrefix = 'league_matches_byround_'.implode('__', $this->request->all());
        $cacheTime = 60 * 10;
        $cacheTags = ['league_'.$this->request->id, 'league_matches_byround_'.$this->request->id];

        if($this->request->clear_cache == 1) {
            Cache::tags($cacheTags)->flush();
        }

        $matches = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function() use ($league) {
            $league = $league->first();
            $byRound = !$league->is_cup;

            $matches = $league->matchesByRound($this->request->season, $this->request->stage, $this->request->round);

            $matchesCollection = collect();
            foreach ($matches as $match) {
                $data = json_decode($match->match_json);
                $match->scores = $data->scores;
                unset($match->match_json);
                $matchesCollection->push($match);
            }
            return $matchesCollection;
        });

        return $matches;
    }



    public function GetLeaguesStats(){
        $league = ApiLeagues::where('league_id',$this->request->id);

        if($league->count() == 0){
            return abort('404');
        }

        $cachePrefix = 'league_matches_byround_'.implode('__', $this->request->all());
        $cacheTime = 3600;
        $cacheTags = ['league_'.$this->request->id, 'league_matches_byround_'.$this->request->id];
        $stats = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function() use ($league) {
            $league =$league->first();
            $res = [];
            $res['goalscorers'] = $league->goalscorers();
            $res['assistscorers'] = $league->assistscorers();
            $res['cardscorers'] = $league->cardscorers();

            return $res;
        });

        return $stats;
    }

    public function GetTeams(){
        if(isset($this->request->teams) && !empty($this->request->teams)){
            $ids = explode(',', $this->request->teams);
            $teams = ApiTeams::whereIn('team_id',$ids);
            $teams->selectRaw('team_id, if(length(name_geo) > 0, name_geo, name) as name, logo_path');

            if($teams->count() == 0){
                return [];
            }

            return $teams->get();

        }

        $teams = ApiTeams::where('api_teams.id','>', 0);
        if(isset($this->request->league_id)) {
            $league_id = $this->request->league_id;
            $league = ApiLeagues::where('league_id', $league_id)->first();
            if($league) {
                $teams->join('api_team_leagues', function($join) use ($league) {
                    $join->on('api_team_leagues.team_id', '=', 'api_teams.team_id');
                    $join->where('api_team_leagues.season_id', '=', $league->current_season_id);
                    $join->where('api_team_leagues.league_id', '=', $league->league_id);
                });
            }
        }
        // return ['test'];
        if(isset($this->request->search)){
            $teams->where(function( $query) {
                $query->where('name','like',"%".$this->request->search."%");
                $query->orWhere('name_geo','like',"%".$this->request->search."%");
            });
        }
        if(isset($this->request->short)){
            $teams->select('api_teams.team_id','api_teams.name', 'api_teams.name_geo', 'api_teams.logo_path');
        }

        return $teams->paginate(30);
    }

    public function GetTeamsByLeague(){

        if(!isset($this->request->league_id)){
            return [];
        }

        $ids = explode(',', $this->request->league_id);

        $leagues = ApiLeagues::whereIn('league_id', $ids );



        if($leagues->count() == 0){
            return [];
        }

        $leagues = $leagues->get();

        $league_s = [];
        $seasons = [];

        foreach ($leagues as  $league) {
            $league_s[$league->current_season_id] = $league->league_id;
            $seasons[] = $league->current_season_id;
        }


        $teams = ApiTeams::whereIn('current_season_id', $seasons)->get();


        $teams_by_league = [];

        foreach ($teams as $team) {
            if(!isset($teams_by_league[$league_s[$team->current_season_id]])){
                $teams_by_league[$league_s[$team->current_season_id]] = [];
            }

            $teams_by_league[$league_s[$team->current_season_id]][] = [
                'team_id' => $team->team_id,
                'country_id' => $team->country_id,
                'name' => $team->name,
                'logo_path' => $team->logo_path,
                'slug' => $team->slug
            ];
        }

        return $teams_by_league;
    }

    public function GetTeamsSearch(){
        if(!isset($this->request->s) || empty($this->request->s) ){
            return [];
        }

        $perPage = $this->request->per_page ? $this->request->per_page : 10;

        $teams = ApiTeams::where('id','>',0)
            ->where('name','LIKE', "%{$this->request->s}%" )
            ->orWhere('name_geo','LIKE', "%{$this->request->s}%" )
            ->with('country');

        //return $countries->paginate($perPage);
        return $teams->paginate($perPage);
    }

    public function GetTeamMatches(Request $request)
    {
        $cachePrefix = 'team_matches_'.implode('__', $request->all());
        $cacheTime = 3600;
        $cacheTags = ['team_'.$this->request->id, 'team_matches_'.$this->request->id];
        $matches = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function() {
            $teams = ApiTeams::where('id','>', 0)->where('team_id',$this->request->id);

            if($teams->count() == 0){
                return abort(404);
            }

            $team = $teams->first();


            $order = 0;
            if(isset($this->request->order) && $this->request->order < 0){
                $order = -1;
            } elseif ($this->request->order > 0) {
                $order = 1;
            }

            $page = 0;

            if(isset($this->request->page)){
                $page = (int)$this->request->page;
            }

            $short=false;
            if(isset($this->request->short)){
                $short=true;
            }

            $perPage = $this->request->per_page ? $this->request->per_page : 30;

            $matches = $team->matches($page, $order, $short, $perPage);

            if($page == 0) return $matches;

            $matches->getCollection()->transform(function ($value) {
                $data = json_decode($value->match_json);
                $value->scores = $data->scores;
                unset($value->match_json);
                return $value;
            });

            return $matches;
        });

        return $matches;
    }

    public function GetTeamGeneralData() {
        $teams = ApiTeams::where('id','>', 0)->where('team_id',$this->request->id);
 
        if($teams->count() == 0){
            return abort(404);
        }

        $team = $teams->first();
        $cachePrefix = 'team_data_'.$this->request->id.implode('__', $this->request->all());
        $cacheTime = 60 * 60;

        $matchesLimit = $this->request->matches_limit ? $this->request->matches_limit : 2;

        //Cache::forget($cachePrefix);
        return Cache::remember($cachePrefix, $cacheTime, function() use ($team, $matchesLimit) {
            $this->request->byseason = 1;
            $standings = collect($this->GetTeamStandings($this->request)['data']['data']);
            $teamPosition = $standings->pluck('team_id')->search($this->request->id);
            if($standings->count() > 0 && property_exists('group_id', $standings->first()) && $standings->first()->group_id != null) {
                $teamStanding = $standings->where('team_id', $team->team_id)->first();
                if($teamStanding) {
                    $standings = $standings->where('group_id', $teamStanding->group_id)->values();
                }
            }

            $from = $teamPosition - 2;
            $to = $teamPosition + 2;
            if($teamPosition <= 2) {
                $from = 0;
                $to = $standings->count() > 4 ? 4 : $standings->count() - 1;
            } elseif($teamPosition > $standings->count() - 2) {
                $from = $standings->count() > 4 ? $standings->count() - 5 : 0;
                $to = $standings->count() > 4 ? $standings->count() : $standings->count() - 1;
            }

            $standingsData = [];
            for($i = $from; $i <= $to; $i++) {
                if($standings[$i]->team_id == $this->request->id) $standings[$i]->is_current = true;
                else $standings[$i]->is_current = false;

                $standingsData[] = $standings[$i];
            }

            return [
                'matches' => $team->matches(1, 0, true, $matchesLimit)->makeHidden(['match_json']),
                'articles' => $team->articles()->orderByDesc('publish_date')->take(5)->get(),
                'standings' => $standingsData,
                'players' => [
                    'rating' => $team->getTopPlayers(),
                    'goalscorers' => $team->getTopGoalscorerPlayers(),
                    'assists' => $team->getTopAssistPlayers(),
                    'cards'    => $team->getTopCardPlayers(),
                ]
            ];
        });
    }

    public function GetTeam(){
        $teams = ApiTeams::with(['standings','standingsByseason'])->where('id','>', 0)->where('team_id',$this->request->id);

        if($teams->count() == 0){
            return abort(404);
        }

        $team = $teams->first();

        $team->players = $team->players;
        $team->club = $team->club;
        $team->articles = $team->getArticles();
        // $team->matches = $team->getMatches();

        for($i = 0; $i < count($team->standings); $i++){
            $team->standings[$i]->overall = json_decode($team->standings[$i]->overall);
            $team->standings[$i]->home = json_decode($team->standings[$i]->home);
            $team->standings[$i]->away = json_decode($team->standings[$i]->away);
            $team->standings[$i]->total = json_decode($team->standings[$i]->total);
        }

        // dd($standings_byseason);

        for($i = 0; $i < count($team->standingsByseason); $i++){
            $team->standingsByseason[$i]->overall = json_decode($team->standingsByseason[$i]->overall);
            $team->standingsByseason[$i]->home = json_decode($team->standingsByseason[$i]->home);
            $team->standingsByseason[$i]->away = json_decode($team->standingsByseason[$i]->away);
            $team->standingsByseason[$i]->total = json_decode($team->standingsByseason[$i]->total);
        }

        if(empty($team->json_data)){
            return $team;
        }

        $team->json_data = json_decode($team->json_data)->data;
        $team->json_data->transfers = isset($team->json_data->transfers->data)?$team->json_data->transfers->data:[];
        $players = [];

        if(isset($team->json_data->goalscorers->data)){
            foreach ($team->json_data->goalscorers->data as $v) {
                if(in_array( $v->player_id, $players )){ continue; }

                $players[] = $v->player_id;
            }
        }

        if(isset($team->json_data->assistscorers->data)){
            foreach ($team->json_data->assistscorers->data as $v) {
                if(in_array( $v->player_id, $players )){ continue; }

                $players[] = $v->player_id;
            }
        }

        if(isset($team->json_data->cardscorers->data)){
            foreach ($team->json_data->cardscorers->data as $v) {
                if(in_array( $v->player_id, $players )){ continue; }

                $players[] = $v->player_id;
            }
        }

        $team_ids = [];
        if(!empty($team->json_data->current_season_id)){
            $new_transfers = [];
            foreach ($team->json_data->transfers as $v) {
                if($v->season_id != $team->json_data->current_season_id){ continue; }
                $new_transfers[] = $v;

                if(!in_array($v->from_team_id, $team_ids)){
                    $team_ids[] = $v->from_team_id;
                }

                if(!in_array($v->to_team_id, $team_ids)){
                    $team_ids[] = $v->to_team_id;
                }

                if(!in_array($v->player_id, $players)){
                    $players[] = $v->player_id;
                }


            }

            $team->json_data->transfers = $new_transfers;
        }else{
            foreach ($team->json_data->transfers as $v) {
                if(!in_array($v->from_team_id, $team_ids)){
                    $team_ids[] = $v->from_team_id;
                }

                if(!in_array($v->to_team_id, $team_ids)){
                    $team_ids[] = $v->to_team_id;
                }

                if(!in_array($v->player_id, $players)){
                    $players[] = $v->player_id;
                }

            }
        }

        // foreach ($team->matches as $v) {
        //     if(!in_array($v->localteam_id, $team_ids)){
        //             $team_ids[] = $v->localteam_id;
        //         }

        //         if(!in_array($v->visitorteam_id, $team_ids)){
        //             $team_ids[] = $v->visitorteam_id;
        //     }
        // }


        $p_res = ApiTeamsPlayers::whereIn('player_id',$players)->get()->toArray();

        $player_byid = [];
        foreach ($p_res as $v) {
            $player_byid[$v['player_id']] = $v;
        }

        if(isset($team->json_data->goalscorers->data)){

            for($i = 0; $i < count($team->json_data->goalscorers->data); $i++){
                $team->json_data->goalscorers->data[$i]->player_name =
                    isset($player_byid[$team->json_data->goalscorers->data[$i]->player_id])?
                        $player_byid[$team->json_data->goalscorers->data[$i]->player_id]['common_name']
                        :'';
                $team->json_data->goalscorers->data[$i]->player_image =
                    isset($player_byid[$team->json_data->goalscorers->data[$i]->player_id])?
                        $player_byid[$team->json_data->goalscorers->data[$i]->player_id]['image_path']
                        :'';
            }

        }

        if(isset($team->json_data->assistscorers->data)){
            for($i = 0; $i < count($team->json_data->assistscorers->data); $i++){
                $team->json_data->assistscorers->data[$i]->player_name =
                    isset($player_byid[$team->json_data->assistscorers->data[$i]->player_id])?
                        $player_byid[$team->json_data->assistscorers->data[$i]->player_id]['common_name']
                        :'';
                $team->json_data->assistscorers->data[$i]->player_image =
                    isset($player_byid[$team->json_data->assistscorers->data[$i]->player_id])?
                        $player_byid[$team->json_data->assistscorers->data[$i]->player_id]['image_path']
                        :'';
            }

        }

        if(isset($team->json_data->cardscorers->data)){
            for($i = 0; $i < count($team->json_data->cardscorers->data); $i++){
                $team->json_data->cardscorers->data[$i]->player_name =
                    isset($player_byid[$team->json_data->cardscorers->data[$i]->player_id])?
                        $player_byid[$team->json_data->cardscorers->data[$i]->player_id]['common_name']
                        :'';
                $team->json_data->cardscorers->data[$i]->player_image =
                    isset($player_byid[$team->json_data->cardscorers->data[$i]->player_id])?
                        $player_byid[$team->json_data->cardscorers->data[$i]->player_id]['image_path']
                        :'';
            }
        }

        $team->json_data->goalscorers = isset($team->json_data->goalscorers->data)?$team->json_data->goalscorers->data:[];
        $team->json_data->assistscorers = isset($team->json_data->assistscorers->data)?$team->json_data->assistscorers->data:[];
        $team->json_data->cardscorers = isset($team->json_data->cardscorers->data)?$team->json_data->cardscorers->data:[];



        $tms = ApiTeams::selectRaw('if(length(name_geo) > 0, name_geo, name) as name, logo_path, team_id')->whereIn('team_id', $team_ids)->get()->toArray();


        $tms_byid = [];
        foreach ($tms as $v) {
            $tms_byid[$v['team_id']] = $v;
        }

        for($i = 0; $i < count($team->json_data->transfers); $i++){


            if(isset($tms_byid[$team->json_data->transfers[$i]->from_team_id])){
                $team->json_data->transfers[$i]->from_team_name = $tms_byid[$team->json_data->transfers[$i]->from_team_id]['name'];
                $team->json_data->transfers[$i]->from_team_image = $tms_byid[$team->json_data->transfers[$i]->from_team_id]['logo_path'];
            }else{
                $team->json_data->transfers[$i]->from_team_name = '';
                $team->json_data->transfers[$i]->from_team_image = '';
            }

            if(isset($tms_byid[$team->json_data->transfers[$i]->to_team_id])){
                $team->json_data->transfers[$i]->to_team_name = $tms_byid[$team->json_data->transfers[$i]->to_team_id]['name'];
                $team->json_data->transfers[$i]->to_team_image = $tms_byid[$team->json_data->transfers[$i]->to_team_id]['logo_path'];
            }else{
                $team->json_data->transfers[$i]->to_team_name = '';
                $team->json_data->transfers[$i]->to_team_image = '';
            }

            if(isset($player_byid[$team->json_data->transfers[$i]->player_id])){

                $p = $player_byid[$team->json_data->transfers[$i]->player_id];

                if(!empty($p['data'])){
                    $p['data'] = json_decode($p['data']);

                    if(isset($p['data']->player->data->country_id)){
                        $c_id = $p['data']->player->data->country_id;
                    }else{
                        $c_id = 0;
                    }

                    if(isset($p['data']->position->data->name)){
                        $pos = $p['data']->position->data->name;
                    }else{
                        $pos = '';
                    }

                }else{
                    $c_id = 0;
                    $pos = '';
                }

                $team->json_data->transfers[$i]->player_name = $p['common_name'];
                $team->json_data->transfers[$i]->player_image = $p['image_path'];
                // $team->json_data->transfers[$i]->player_nationality = $p['nationality'];
                $team->json_data->transfers[$i]->player_country_id =  $c_id;
                $team->json_data->transfers[$i]->player_position =  $pos;
            }else{
                $team->json_data->transfers[$i]->player_name = '';
                $team->json_data->transfers[$i]->player_image = '';
                $team->json_data->transfers[$i]->player_country_id =  0;
                $team->json_data->transfers[$i]->player_position =  '';
            }



        }

        // for($i = 0; $i < count($team->matches); $i++){
        //     if(isset($tms_byid[
        //         isset($team->json_data->transfers[$i]->from_team_id)?$team->json_data->transfers[$i]->from_team_id:'error'
        //     ])){
        //         $team->matches[$i]->localteam_name =
        //         isset($tms_byid[$team->matches[$i]->localteam_id]['name'])?$tms_byid[$team->matches[$i]->localteam_id]['name']:'';

        //         $team->matches[$i]->localteam_image =
        //         isset($tms_byid[$team->matches[$i]->localteam_id]['logo_path'])? $tms_byid[$team->matches[$i]->localteam_id]['logo_path']:'';
        //         $team->matches[$i]->visitorteam_name =
        //         isset($tms_byid[$team->matches[$i]->visitorteam_id]['name'])? $tms_byid[$team->matches[$i]->visitorteam_id]['name']:'';
        //         $team->matches[$i]->visitorteam_image =
        //          isset($tms_byid[$team->matches[$i]->visitorteam_id]['logo_path'])? $tms_byid[$team->matches[$i]->visitorteam_id]['logo_path']:'';
        //    }else{
        //         $team->matches[$i]->localteam_name = '';
        //         $team->matches[$i]->visitorteam_name = '';
        //    }
        // }


        return $team;
    }

    public function GetMatchStandings(Request $request) {
        $match = ApiMatches::where('match_id', $request->id)->first();

        if(!$match) abort(404);

        $this->request->league = $match->league_id;
        $this->request->visitorteam = $match->visitorteam_id;
        $this->request->localteam = $match->localteam_id;

        return $this->GetStandings($request);
    }

    public function GetStandings(Request $request){

        $league = ApiLeagues::where('league_id', $this->request->league)->first();

        if(!$league) abort(404);

        $season = $this->request->season ? $this->request->season : $league->current_season_id;
        $standings = ApiStandings::where('league_id', $this->request->league)
            ->where('season_id', $season)->get();

        if(count($standings) == 0) {
            $prevStanding = ApiStandings::where('league_id', $this->request->league)
                ->where('season_id', '<', $season)->orderByDesc('season_id')->first();

            if($prevStanding) {
                $standings = ApiStandings::where('league_id', $this->request->league)
                    ->where('season_id', $prevStanding->season_id)->get();
            }
        }

        if($standings->count() > 0){
            $standings = $standings->toArray();

            for($i = 0; $i < count($standings); $i++){
                $standings[$i]['standings'] = json_decode($standings[$i]['standings']);

                if(isset($standings[$i]['standings']->data)){
                    for($j = 0;$j < count($standings[$i]['standings']->data); $j++){
                        if(isset($standings[$i]['standings']->data[$j]))
                            $standings[$i]['standings']->data[$j]->slug =
                                url_slug($standings[$i]['standings']->data[$j]->team_name,['transliterate' => true]);

                        $team = ApiTeams::where('team_id', $standings[$i]['standings']->data[$j]->team_id)
                            ->select(['logo_path', 'name_geo', 'name'])
                            ->first();
                        $standings[$i]['standings']->data[$j]->logo_path = $team ? $team->logo_path : ApiTeams::getLogoPlaceholder();
                        $standings[$i]['standings']->data[$j]->team_name = $team->name_geo == '' ? $team->name : $team->name_geo;
                        if($this->request->visitorteam && $this->request->localteam) {
                            $standings[$i]['standings']->data[$j]->is_visitorteam = $this->request->visitorteam == $standings[$i]['standings']->data[$j]->team_id;
                            $standings[$i]['standings']->data[$j]->is_localteam = $this->request->localteam == $standings[$i]['standings']->data[$j]->team_id;
                        }
                    }
                }
            }

            if(count($standings) > 1) {
                $standings = collect($standings)->sortBy('name')->values()->toArray();
            }

            return $standings;
        }

        return [];
    }

    public function GetStandingsMobile(){
        $cachePrefix = 'league_standings_mobile_'.implode('__', $this->request->all());
        $cacheTime = 60 * 60;
        $cacheTags = ['league_standings', 'league_standings_mobile'];
        $data = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function() {
            $leagues = ApiLeagues::leftjoin('league_l as l', 'l.league_id','=','api_leagues.league_id');

            if(isset($this->request->search)) {
                $leagues->where('api_leagues.name', 'like', $this->request->search.'%')
                    ->orWhere('api_leagues.name_geo', 'like', $this->request->search.'%');
            } else {
                $leagues->where('l.menu', 1);
            }

            $leagues->select(DB::raw("
                api_leagues.league_id ,
                api_leagues.is_cup ,
                if(length(api_leagues.name_geo) > 0, api_leagues.name_geo, api_leagues.name) as name ,
                api_leagues.slug ,
                concat('".env('APP_URL')."/api/league-icons/', api_leagues.league_id, '.png') as image_path,
                api_leagues.name_geo as translated_name,
                l.priority as priority,
                api_leagues.current_season_id
             "))->where('api_leagues.is_cup', 0)->orderBy('l.priority', 'desc');

            $data = [];
            foreach ($leagues->get() as $league) {
                $season = $this->request->season ? $this->request->season : $league->current_season_id;

                $leagueData = [
                    'league' => $league,
                    'standings' => [],
                    'goalscorers' => $league->goalscorers()
                ];

                $standings = ApiStandings::where('league_id', $league->league_id)
                    ->where('season_id', $season);

                if($standings->count() > 0){
                    $standings = $standings->get()->toArray();

                    for($i = 0; $i < count($standings); $i++){
                        $standings[$i]['standings'] = json_decode($standings[$i]['standings']);
                        // dd($standings[$i]['standings']->data);
                        if(isset($standings[$i]['standings']->data)){
                            for($j = 0;$j < count($standings[$i]['standings']->data); $j++){
                                if(isset($standings[$i]['standings']->data[$j]))
                                    $standings[$i]['standings']->data[$j]->slug =
                                        url_slug($standings[$i]['standings']->data[$j]->team_name,['transliterate' => true]);

                                $team = ApiTeams::where('team_id', $standings[$i]['standings']->data[$j]->team_id)
                                    ->select(['logo_path', 'name_geo', 'name'])
                                    ->first();
                                $standings[$i]['standings']->data[$j]->logo_path = $team ? $team->logo_path : ApiTeams::getLogoPlaceholder();
                                $standings[$i]['standings']->data[$j]->team_name = $team->name_geo == '' ? $team->name : $team->name_geo;
                            }
                        }
                    }

                    $leagueData['standings'] = $standings[0]['standings']->data;
                }

                $data[] = $leagueData;
            }

            return $data;
        });

        return $data;
    }

    public function GetPlayers(){
        $players = ApiTeamsPlayers::where('status_id','>',0);
//        $players = ApiTeamsPlayers::query();

        if($this->request->search) {
            $keyword = $this->request->search;
            $players->where(function($q) use ($keyword) {
                $q->where('fullname', 'like', '%'.$keyword.'%')
                    ->orWhere('common_name', 'like', '%'.$keyword.'%');
            });
        }

        if(isset($this->request->short)){
            $players->select('player_id','fullname', 'image_path');
        }
        // $json_data = json_decode($player)

        return $players->paginate(30);
    }

    public function GetPlayer(){
        $players = ApiTeamsPlayers::where('player_id',$this->request->id);

        if($players->count() == 0){
            return abort(404);
        }

        $players = $players->first();
        $players->team = $players->team;
        $players->articles = $players->getArticles();

        return $players;
    }

    public function GetPlayerInfo(){
        $players = ApiTeamsPlayers::where('player_id',$this->request->id);

        if($players->count() == 0){
            return abort(404);
        }

        $players = $players->first();
        $players->team = $players->team;
        $players->shareLink = $players->getShareLink();
        unset($players->team->json_data);
        $players->data = json_decode($players->data);

        return $players;
    }

    public function GetPlayerArticles(){
        $players = ApiTeamsPlayers::where('player_id',$this->request->id);

        if($players->count() == 0){
            return abort(404);
        }

        $players = $players->first();

        $perPage = $this->request->per_page ? $this->request->per_page : 30;

        return $players->getArticles($perPage);
    }

    public function GetPlayersForPlayers(){
        $players = ApiTeamsPlayers::select('player_id','fullname')->where('status_id','>',0)->get();

        return $players;
    }

    public function GetPlayersSearch(){

        if(!isset($this->request->s) || empty($this->request->s) ){
            return [];
        }
//        $players = ApiTeamsPlayers::where('status_id','>',0)
        $players = ApiTeamsPlayers::where('fullname','LIKE', "%{$this->request->s}%" )->get();

        return $players;
    }

    public function GetPlayerStats(){
        $players = ApiTeamsPlayers::where('status_id','>',0)->where('player_id',$this->request->id);

        if($players->count() == 0){
            return abort(404);
        }

        $player = $players->first();
        $player->data = json_decode($player->data);

        return $player;
    }


    public function GetRounds(){
        return \App\Models\ApiRounds::where('season_id', (int)$this->request->season)->get();
    }

    public function GetTeamLeagues(Request $request, $teamId) {
        $leagues = ApiLeagues::teamLeagues()->where('t.team_id', $teamId)->get();

        $data = [];
        foreach ($leagues as $league) {
            $league->stats = json_decode($league->stats);
            $data[] = $league;
        }

        return ['data' => $leagues];
    }

    public function GetTeamArticles(Request $request) {
        $tags = ['articles', 'article-list', 'team-'.$request->id];
        $cache_prefix = 'articles_team_';
        $cashe_index = implode('__', array_map(
            function ($v, $k) { return sprintf("%s=%s", $k, $v); },
            $request->all(),
            array_keys($request->all())
        ));

        if($request->clear_cache) {
            Cache::tags($tags)->flush();
        }

        $articles = Cache::tags($tags)->remember($cache_prefix . $cashe_index, 24 * 60 * 60, function () use ($request) {
            $page = $request->page ? $request->page : 1;
            $perPage = $request->per_page ? $request->per_page : 10;

            $team = ApiTeams::where('team_id', $request->id)->where('id', '>', 0)->first();
            return $team->getArticles($perPage);
        });

        return $articles;
    }

    public function GetTeamPlayers(Request $request) {
        $cachePrefix = 'team_players_'.implode('__', $request->all());
        $cacheTime = 3600;
        $cacheTags = ['team_'.$this->request->id, 'team_matches_'.$this->request->id];
        $page = $request->page ? $request->page : 1;
        $perPage = $request->per_page ? $request->per_page : 10;

        //Cache::tags($cacheTags)->forget($cachePrefix);
        $players = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function () use ($page, $perPage) {
            $team = ApiTeams::where('team_id', $this->request->id)->where('id', '>', 0)->first();
            return $team->players()->paginate($perPage);
        });

        return $players;
    }


    public function GetTeamStats(Request $request) {
        $page = $request->page ? $request->page : 1;
        $perPage = $request->per_page ? $request->per_page : 10;
        $cachePrefix = 'team_players_stats_'.implode('__', $request->all());
        $cacheTime = 3600;
        $cacheTags = ['team_'.$this->request->id, 'team_player_stats_'.$this->request->id];

        $stats = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function() use ($page, $perPage) {
            $teams = ApiTeams::where('id','>', 0)->where('team_id',$this->request->id);

            if($teams->count() == 0){
                return abort(404);
            }

            $team = $teams->first();

            $team->players = $team->players;
            $team->club = $team->club;
            $team->articles = $team->getArticles();
            // $team->matches = $team->getMatches();

            if(empty($team->json_data)){
                return $team;
            }

            $team->json_data = json_decode($team->json_data)->data;
            $team->json_data->transfers = isset($team->json_data->transfers->data)?$team->json_data->transfers->data:[];
            $players = [];

            if(isset($team->json_data->goalscorers->data)){
                foreach ($team->json_data->goalscorers->data as $v) {
                    if(in_array( $v->player_id, $players )){ continue; }

                    $players[] = $v->player_id;
                }
            }

            if(isset($team->json_data->assistscorers->data)){
                foreach ($team->json_data->assistscorers->data as $v) {
                    if(in_array( $v->player_id, $players )){ continue; }

                    $players[] = $v->player_id;
                }
            }

            if(isset($team->json_data->cardscorers->data)){
                foreach ($team->json_data->cardscorers->data as $v) {
                    if(in_array( $v->player_id, $players )){ continue; }

                    $players[] = $v->player_id;
                }
            }

            $team_ids = [];
            if(!empty($team->json_data->current_season_id)){
                $new_transfers = [];
                foreach ($team->json_data->transfers as $v) {
                    if($v->season_id != $team->json_data->current_season_id){ continue; }
                    $new_transfers[] = $v;

                    if(!in_array($v->from_team_id, $team_ids)){
                        $team_ids[] = $v->from_team_id;
                    }

                    if(!in_array($v->to_team_id, $team_ids)){
                        $team_ids[] = $v->to_team_id;
                    }

                    if(!in_array($v->player_id, $players)){
                        $players[] = $v->player_id;
                    }


                }

                $team->json_data->transfers = $new_transfers;
            }else{
                foreach ($team->json_data->transfers as $v) {
                    if(!in_array($v->from_team_id, $team_ids)){
                        $team_ids[] = $v->from_team_id;
                    }

                    if(!in_array($v->to_team_id, $team_ids)){
                        $team_ids[] = $v->to_team_id;
                    }

                    if(!in_array($v->player_id, $players)){
                        $players[] = $v->player_id;
                    }

                }
            }

            $p_res = ApiTeamsPlayers::whereIn('player_id',$players)->get()->toArray();

            $player_byid = [];
            foreach ($p_res as $v) {
                $player_byid[$v['player_id']] = $v;
            }

            if(isset($team->json_data->goalscorers->data)){

                for($i = 0; $i < count($team->json_data->goalscorers->data); $i++){
                    $team->json_data->goalscorers->data[$i]->player_name =
                        isset($player_byid[$team->json_data->goalscorers->data[$i]->player_id])?
                            $player_byid[$team->json_data->goalscorers->data[$i]->player_id]['common_name']
                            :'';
                    $team->json_data->goalscorers->data[$i]->player_image =
                        isset($player_byid[$team->json_data->goalscorers->data[$i]->player_id])?
                            $player_byid[$team->json_data->goalscorers->data[$i]->player_id]['image_path']
                            :'';
                }

            }

            if(isset($team->json_data->assistscorers->data)){
                for($i = 0; $i < count($team->json_data->assistscorers->data); $i++){
                    $team->json_data->assistscorers->data[$i]->player_name =
                        isset($player_byid[$team->json_data->assistscorers->data[$i]->player_id])?
                            $player_byid[$team->json_data->assistscorers->data[$i]->player_id]['common_name']
                            :'';
                    $team->json_data->assistscorers->data[$i]->player_image =
                        isset($player_byid[$team->json_data->assistscorers->data[$i]->player_id])?
                            $player_byid[$team->json_data->assistscorers->data[$i]->player_id]['image_path']
                            :'';
                }

            }

            if(isset($team->json_data->cardscorers->data)){
                for($i = 0; $i < count($team->json_data->cardscorers->data); $i++){
                    $team->json_data->cardscorers->data[$i]->player_name =
                        isset($player_byid[$team->json_data->cardscorers->data[$i]->player_id])?
                            $player_byid[$team->json_data->cardscorers->data[$i]->player_id]['common_name']
                            :'';
                    $team->json_data->cardscorers->data[$i]->player_image =
                        isset($player_byid[$team->json_data->cardscorers->data[$i]->player_id])?
                            $player_byid[$team->json_data->cardscorers->data[$i]->player_id]['image_path']
                            :'';
                }
            }

            $team->json_data->goalscorers = isset($team->json_data->goalscorers->data)?$team->json_data->goalscorers->data:[];
            $team->json_data->assistscorers = isset($team->json_data->assistscorers->data)?$team->json_data->assistscorers->data:[];
            $team->json_data->cardscorers = isset($team->json_data->cardscorers->data)?$team->json_data->cardscorers->data:[];

            return [
                'goalscorers' => collect($team->json_data->goalscorers)->where('season_id', $team->current_season_id)->unique(['player_id'])->sortByDesc('goals', SORT_NUMERIC)->forPage($page, $perPage)->values()->toArray(),
                'assistscorers' => collect($team->json_data->assistscorers)->where('season_id', $team->current_season_id)->unique(['player_id'])->sortByDesc('assists', SORT_NUMERIC)->forPage($page, $perPage)->values()->toArray(),
                'cardscorers' => collect($team->json_data->cardscorers)->where('season_id', $team->current_season_id)->unique(['player_id'])->sortByDesc('yellowcards', SORT_NUMERIC)->forPage($page, $perPage)->values()->toArray()
            ];
        });

        return $stats;
    }

    public function GetTeamStandings(Request $request) {
        $page = $request->page ? $request->page : 1;
        $perPage = $request->per_page ? $request->per_page : 10;

        $cachePrefix = 'team_standingss_'.implode('__', $request->all());
        $cacheTime = 60 * 10;
        $cacheTags = ['team_'.$this->request->id, 'team_standingss_'.$this->request->id];

        $standings = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function () {
            $teams = ApiTeams::with(['standings','standingsByseason'])->where('id','>', 0)->where('team_id',$this->request->id);

            if($teams->count() == 0){
                return abort(404);
            }
            $team = $teams->first();

            $standings = $team->getStandings();

            foreach ($standings as $key => $standing) {
                for($i = 0; $i < count($standing['standings']); $i++){
                    $teamData = ApiTeams::where('team_id', $standing['standings'][$i]->team_id)
                        ->select('name', 'name_geo', 'logo_path', 'slug')
                        ->first();
                    $standing['standings'][$i]->logo_path = $teamData->logo_path;
                    $standing['standings'][$i]->slug = $teamData->slug;
                    $standing['standings'][$i]->team_name = $teamData->name_geo == '' ? $teamData->name : $teamData->name_geo;
                    $standing['standings'][$i]->overall = $standing['standings'][$i]->overall;
                    $standing['standings'][$i]->home = $standing['standings'][$i]->home;
                    $standing['standings'][$i]->away = $standing['standings'][$i]->away;
                    $standing['standings'][$i]->total = $standing['standings'][$i]->total;
                }
            }

            return $standings;
        });

        return ['data' => $standings];
    }

    public function GetTeamTransfers(Request $request) {
        $page = $request->page ? $request->page : 1;
        $perPage = $request->per_page ? $request->per_page : 10;

        $cachePrefix = 'team_transfers_'.implode('__', $request->all());
        $cacheTime = 3600;
        $cacheTags = ['team_'.$this->request->id, 'team_transfers_'.$this->request->id];

        $transfers = Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function() use ($page, $perPage) {
            $teams = ApiTeams::with(['standings','standingsByseason'])->where('id','>', 0)->where('team_id',$this->request->id);

            if($teams->count() == 0){
                return abort(404);
            }

            $team = $teams->first();

            $team->json_data = json_decode($team->json_data)->data;
            $team->json_data->transfers = isset($team->json_data->transfers->data)?$team->json_data->transfers->data:[];
            $players = [];

            $team_ids = [];
            if(!empty($team->json_data->current_season_id)){
                $new_transfers = [];
                foreach ($team->json_data->transfers as $v) {
                    if($v->season_id != $team->json_data->current_season_id){ continue; }
                    $new_transfers[] = $v;

                    if(!in_array($v->from_team_id, $team_ids)){
                        $team_ids[] = $v->from_team_id;
                    }

                    if(!in_array($v->to_team_id, $team_ids)){
                        $team_ids[] = $v->to_team_id;
                    }

                    if(!in_array($v->player_id, $players)){
                        $players[] = $v->player_id;
                    }


                }

                $team->json_data->transfers = $new_transfers;
            }else{
                foreach ($team->json_data->transfers as $v) {
                    if(!in_array($v->from_team_id, $team_ids)){
                        $team_ids[] = $v->from_team_id;
                    }

                    if(!in_array($v->to_team_id, $team_ids)){
                        $team_ids[] = $v->to_team_id;
                    }

                    if(!in_array($v->player_id, $players)){
                        $players[] = $v->player_id;
                    }

                }
            }

            $p_res = ApiTeamsPlayers::whereIn('player_id',$players)->get()->toArray();

            $player_byid = [];
            foreach ($p_res as $v) {
                $player_byid[$v['player_id']] = $v;
            }

            $tms = ApiTeams::selectRaw('if(length(name_geo) > 0, name_geo, name) as name, logo_path, team_id')->whereIn('team_id', $team_ids)->get()->toArray();


            $tms_byid = [];
            foreach ($tms as $v) {
                $tms_byid[$v['team_id']] = $v;
            }

            for($i = 0; $i < count($team->json_data->transfers); $i++){


                if(isset($tms_byid[$team->json_data->transfers[$i]->from_team_id])){
                    $team->json_data->transfers[$i]->from_team_name = $tms_byid[$team->json_data->transfers[$i]->from_team_id]['name'];
                    $team->json_data->transfers[$i]->from_team_image = $tms_byid[$team->json_data->transfers[$i]->from_team_id]['logo_path'];
                }else{
                    $team->json_data->transfers[$i]->from_team_name = '';
                    $team->json_data->transfers[$i]->from_team_image = '';
                }

                if(isset($tms_byid[$team->json_data->transfers[$i]->to_team_id])){
                    $team->json_data->transfers[$i]->to_team_name = $tms_byid[$team->json_data->transfers[$i]->to_team_id]['name'];
                    $team->json_data->transfers[$i]->to_team_image = $tms_byid[$team->json_data->transfers[$i]->to_team_id]['logo_path'];
                }else{
                    $team->json_data->transfers[$i]->to_team_name = '';
                    $team->json_data->transfers[$i]->to_team_image = '';
                }

                if(isset($player_byid[$team->json_data->transfers[$i]->player_id])){

                    $p = $player_byid[$team->json_data->transfers[$i]->player_id];

                    if(!empty($p['data'])){
                        $p['data'] = json_decode($p['data']);

                        if(isset($p['data']->player->data->country_id)){
                            $c_id = $p['data']->player->data->country_id;
                        }else{
                            $c_id = 0;
                        }

                        if(isset($p['data']->position->data->name)){
                            $pos = $p['data']->position->data->name;
                        }else{
                            $pos = '';
                        }

                    }else{
                        $c_id = 0;
                        $pos = '';
                    }

                    $team->json_data->transfers[$i]->player_name = $p['common_name'];
                    $team->json_data->transfers[$i]->player_image = $p['image_path'];
                    // $team->json_data->transfers[$i]->player_nationality = $p['nationality'];
                    $team->json_data->transfers[$i]->player_country_id =  $c_id;
                    $team->json_data->transfers[$i]->player_position =  $pos;
                }else{
                    $team->json_data->transfers[$i]->player_name = '';
                    $team->json_data->transfers[$i]->player_image = '';
                    $team->json_data->transfers[$i]->player_country_id =  0;
                    $team->json_data->transfers[$i]->player_position =  '';
                }



            }

            $transfers = collect($team->json_data->transfers)->forPage($page, $perPage);
            return ['data' => $transfers->values()->toArray(), 'current_page' => $page, 'last_page' => (int)(count($team->json_data->transfers) / ($page * $perPage)) + 1];
        });

        return $transfers;
    }

    public function GetTeamInfo(Request $request) {
        $team = ApiTeams::where('team_id', $request->id)->where('id', '>', 0)->first();

        return [
            'id' => $team->id,
            'team_id' => $team->team_id,
            'current_season_id' => $team->current_season_id,
            'name' => $team->name,
            'slug' => $team->slug,
            'shareLink' => $team->getShareLink(),
            'history' => $team->club
        ];
    }

    public function GetMatchPlayerStats(Request $request, $matchId, $playerId) {
        $match = ApiMatches::where('match_id', $matchId)->firstOrFail();

        $matchData = json_decode($match->match_json, true);

        $lineupData = $matchData['lineup']['data'] ? collect($matchData['lineup']['data']) : collect();

        $playerStat = $lineupData->where('player_id', $playerId)->first();

        return $playerStat['stats'];
    }

    public function GetSeasonStats(Request $request, $seasonId) {
        $season = ApiSeasons::where('season_id', $seasonId)->firstOrFail();

        $seasonStats = $season->stats ? json_decode($season->stats)->data : [];

        return ['data' => $seasonStats];
    }

    public function GetTeamStatsByLeague($teamId, $leagueId, $seasonId = null) {
        $teamLeagueStats = ApiTeamLeagues::where('team_id', $teamId)->where('league_id', $leagueId);

        if(isset($this->request->only_seasons)) return ['data' => $teamLeagueStats->select('season_id')->get()->pluck('season_id')->toArray()];

        if(!$seasonId) $seasonId = ApiLeagues::where('league_id', $leagueId)->first()->current_season_id;

        $stats = $teamLeagueStats->where('season_id', $seasonId)->first();

        return ['data' => $stats ? json_decode($stats->stats) : []];
    }

    public function GetTeamStatsByAllLeague($teamId) {
        $teamLeagueStats = ApiTeamLeagues::where('team_id', $teamId);

        $leagues = ApiLeagues::where('live_standings', 1)->whereHas('teams', function($q) use ($teamId) {
            $q->where('team_id', $teamId);
        })->groupBy('current_season_id');
        $leagues = $leagues->select('current_season_id');
dd($leagues->get());
        $statsData = [];
        foreach ($teamLeagueStats->get() as $teamLeagueStat) {
            $league = ApiLeagues::where('league_id', $teamLeagueStat->league_id)
                ->where('live_standings', true)
                ->first();


            if($league) {
                $stats = $teamLeagueStats->where('season_id', $league->current_season_id)->first();

                if($stats) {
                    $statsData[] = json_decode($stats->stats);
                }
            }

        }


        return ['data' => $statsData];
    }

    public function GetMatchInfo(Request $request, int $id) {
        $match = ApiMatches::where('match_id', $id)->first();

        if(!$match) abort(404);

        $data = json_decode($match->match_json);

        $localTeam = $data->localTeam;
        $visitorTeam = $data->visitorTeam;
        $lTeam = ApiTeams::where('team_id', $localTeam->data->id)->first();
        $vTeam = ApiTeams::where('team_id', $visitorTeam->data->id)->first();
        $localTeam->data->name = $lTeam ? $lTeam->name : '';
        $visitorTeam->data->name = $vTeam ? $vTeam->name : '';

        $stats = $this->GetMatchPlayersStats($request, $id);
        $hasStats = 0;
        foreach ($stats as $key => $value) {
            if(count($value) > 0) {
                $hasStats++;
            }
        }

        $articles = Article::where('match_id', $match->match_id)->with('mainGalleryItem')->get();
        $articlesNew = collect();
        foreach ($articles as $article) {
            $article->content = \App\Models\ShortCode::ContentShortcode($article->content);
            $article->main_video = \App\Models\ShortCode::ContentShortcode($article->main_video);
            $articlesNew->push($article);
        }

        $jsonData = [
            'match_id' => $data->id,
            'league_id' => $data->league_id,
            'localteam_id' => $data->localteam_id,
            'visitorteam_id' => $data->visitorteam_id,
            'scores' => $data->scores,
            'time' => $data->time,
            'localTeam' => $localTeam,
            'visitorTeam' => $visitorTeam,
            'articles' => $articlesNew,
            'rate'  => $match->getRates(),
            'formations' => $data->formations,
            'substitutions' => $data->substitutions,
            'shareLink' => $match->getShareLink(),
            'has_player_stats' => $hasStats > 0
        ];

        $pl_ids = [];
        if(isset($data->lineup->data))
            foreach($data->lineup->data as $p){
                $pl_ids[] = $p->player_id;
            }

        $players = ApiTeamsPlayers::whereIn('player_id', $pl_ids)->where('status_id', '>', -1)->get();

        $player_imgs = [];
        foreach ($players as $p) {
            $dt = json_decode($p->data);

            $cntr_id = isset($dt->player->data->country_id)?$dt->player->data->country_id:0;
            $img_path = isset($dt->player->data->image_path)?$dt->player->data->image_path:'';

            $player_imgs[$p->player_id] = ['country_id' =>$cntr_id, 'image_path' =>  $img_path ];
        }

        $jsonData['player_img_data'] = $player_imgs;

        return ['data' => $jsonData];
    }

    public function GetMatchHighlights(int $id) {
        $match = ApiMatches::where('match_id', $id)->first();

        if(!$match) abort(404);

        $data = json_decode($match->match_json);

        return ['data' => $data->highlights->data];
    }

    public function GetMatchOverview(int $id) {
        $match = ApiMatches::where('match_id', $id)->first();

        if(!$match) abort(404);

        $data = json_decode($match->match_json);

        return [
            'substitutions' => $data->substitutions->data,
            'goals' => $data->goals->data,
            'cards' => $data->cards->data,
        ];
    }

    public function GetMatchOverviewMobile(int $id) {
        $match = ApiMatches::where('match_id', $id)->first();

        if(!$match) abort(404);
        $data = json_decode($match->match_json);

        $scores = (array)$data->scores;
        $scores['1st_time'] = $scores['ht_score'];
        $scores['2nd_time'] = null;

        $ftScores = explode('-', $scores['ft_score']);
        $htScores = explode('-', $scores['ht_score']);

        if(count($ftScores) == 2 && count($htScores) == 2)
            $scores['2nd_time'] = ($ftScores[0]-$htScores[0]).'-'.($ftScores[1]-$htScores[1]);

        $jsonData = [
            'scores' => $scores,
            'substitutions' => [
                'local' => [],
                'visitor' => []
            ],
            'goals' => [
                'local' => [],
                'visitor' => []
            ],
            'cards' => [
                'local' => [],
                'visitor' => []
            ]
        ];

        foreach ($data->substitutions->data as $substitution) {
            if ($substitution->team_id == $match->localteam_id) $jsonData['substitutions']['local'][] = $substitution;
            if ($substitution->team_id == $match->visitorteam_id) $jsonData['substitutions']['visitor'][] = $substitution;
        }

        foreach ($data->goals->data as $goal) {
            if ($goal->team_id == $match->localteam_id) $jsonData['goals']['local'][] = $goal;
            if ($goal->team_id == $match->visitorteam_id) $jsonData['goals']['visitor'][] = $goal;
        }

        foreach ($data->cards->data as $card) {
            if ($card->team_id == $match->localteam_id) $jsonData['cards']['local'][] = $card;
            if ($card->team_id == $match->visitorteam_id) $jsonData['cards']['visitor'][] = $card;
        }

        return $jsonData;
    }

    public function GetMatchStatistics(int $id) {
        $match = ApiMatches::where('match_id', $id)->first();

        if(!$match) abort(404);

        $data = json_decode($match->match_json);

        return ['data' => $data->stats->data];
    }

    public function GetMatchPlayersStats(Request $request, $matchId) {
        $match = ApiMatches::where('match_id', $matchId)->firstOrFail();
        $cachePrefix = 'match_player_stats_'.$matchId;
        $cacheTime = 3000;
        $cacheTags = ['match', 'match_player_stats'];
        if($match->time_status == 'FT') $cacheTime = 60 * 60 * 24;
        //Cache::tags($cacheTags)->forget($cachePrefix);

        return Cache::tags($cacheTags)->remember($cachePrefix, $cacheTime, function () use ($request, $matchId) {
            $lineupPlayers = collect($this->GetMatchPlayers($request, $matchId)['lineup']);
            $benchPlayers = collect($this->GetMatchPlayers($request, $matchId)['bench']);
            $players = $lineupPlayers->merge($benchPlayers->values());

            $totalRatingSorted = $players->sortByDesc(function($value, $key) {
                return isset($value['stats']) ? $value['stats']['rating'] : 0;
            })->values()->map(function($item) {
                $item['stat'] = isset($item['stats']) ? $item['stats']['rating'] : null;
                unset($item['stats']);
                return $item;
            });
            $totalShotsSorted = $players->sortByDesc(function($value, $key) {
                return isset($value['stats'], $value['stats']['shots']) ? $value['stats']['shots']['shots_total'] : null;
            })->values()->map(function($item) {
                $item['stat'] = isset($item['stats'], $item['stats']['shots']) ? $item['stats']['shots']['shots_total'] : null;
                unset($item['stats']);
                return $item;
            });
            $totalShortsOnTargetSorted = $players->sortByDesc(function($value, $key) {
                return isset($value['stats'], $value['stats']['shots']) ? $value['stats']['shots']['shots_on_goal'] : null;
            })->values()->map(function($item) {
                $item['stat'] = isset($item['stats'], $item['stats']['shots']) ? $item['stats']['shots']['shots_on_goal'] : null;
                unset($item['stats']);
                return $item;
            });
            $totalTackles = $players->sortByDesc(function($value, $key) {
                return isset($value['stats'], $value['stats']['other']) ? $value['stats']['other']['tackles'] : null;
            })->values()->map(function($item) {
                $item['stat'] = isset($item['stats'], $item['stats']['other']) ? $item['stats']['other']['tackles'] : null;
                unset($item['stats']);
                return $item;
            });

            return [
                'rating' => $totalRatingSorted->where('stat', '!=', null),
                'shots' => $totalShotsSorted->where('stat', '!=', null),
                'shots_on_goal' => $totalShortsOnTargetSorted->where('stat', '!=', null),
                'tackles' => $totalTackles->where('stat', '!=', null)
            ];
        });
    }

    public function GetMatchPlayers(Request $request, $matchId) {
        $match = ApiMatches::where('match_id', $matchId)->firstOrFail();

        $matchData = json_decode($match->match_json, true);

        $lineupData = $matchData['lineup']['data'] ? $matchData['lineup']['data'] : [];
        $lineupData = array_map(function ($item) {
            $team = ApiTeams::where('team_id', $item['team_id'])->first();
            $player = ApiTeamsPlayers::where('player_id', $item['player_id'])->first();
            if(!$player) {
                $player = ApiTeamsPlayers::createPlayerFromApi($item['player_id']);
            }

            $names = explode(' ', $item['player_name']);
            $item['player_name'] = count($names) > 1 ? mb_substr($names[0], 0,1).'.'.$names[1] : $names[0];
            $item['player_slug'] = $player->slug;
            $item['image_path'] = $player->image_path;
            $item['country_flag'] = $player->country_flag;
            $item['team_logo_path'] = $team->logo_path;
            $item['team_name'] = $team->name;
            return $item;
        }, $lineupData);

        $benchData = $matchData['bench']['data'] ? $matchData['bench']['data'] : [];
        $benchData = array_map(function ($item) {
            $team = ApiTeams::where('team_id', $item['team_id'])->first();
            $player = ApiTeamsPlayers::where('player_id', $item['player_id'])->first();
            if(!$player) {
                $player = ApiTeamsPlayers::createPlayerFromApi($item['player_id']);

                if($item['player_id'] == null) {
                    $player = new ApiTeamsPlayers();
                    $player->image_path = 'https://cdn.sportmonks.com/images/soccer/placeholder.png';
                    $player->country_flag = 'https://v2.goal.ge/assets/img/default.png';
                }
            }

            $names = explode(' ', $item['player_name']);
            $item['player_name'] = count($names) > 1 ? mb_substr($names[0], 0,1).'.'.$names[1] : end($names);
            $item['image_path'] = $player->image_path;
            $item['country_flag'] = $player->country_flag;
            $item['team_logo_path'] = $team->logo_path;
            $item['team_name'] = $team->name;
            return $item;
        }, $benchData);

        $formationData = $matchData['formations'] ? $matchData['formations'] : [];

        $data = [];
        if($request->with_top2_players) {
            $players = $lineupData;
            if(count($benchData) > 0 && count($players) > 0)
                array_push($players, ...$benchData);

            $players = collect($players)->where('stats.rating', '!=', null);

            $localTeamPlayers = $players->where('team_id', $match->localteam_id)->values();
            $visitorTeamPlayers = $players->where('team_id', $match->visitorteam_id)->values();

            $localTopPlayer = $localTeamPlayers->where('position', '!=','G')->sortByDesc('stats.rating')->first();
            $visitorTopPlayer = $visitorTeamPlayers->where('position', '=', $localTopPlayer['position'])->sortByDesc('stats.rating')->first();

            $data['top_players'] = [
                'localteam_top_player' => $localTopPlayer,
                'visitorteam_top_player' => $visitorTopPlayer,
            ];
        }

        if($request->partial) {
            $lineupData = array_map(function ($item) {
                unset($item['stats']);
                return $item;
            }, $lineupData);

            $benchData = array_map(function ($item) {
                unset($item['stats']);
                return $item;
            }, $benchData);
        }

        $data['lineup'] = $lineupData;
        $data['bench'] = $benchData;
        $data['formations'] = $formationData;

        return $data;
    }

    public function GetLiveMatches() {
        $matches = ApiMatches::where('starting_at', '>', (new \DateTime())->modify('-1 day')->format('Y-m-d H:i:s'))->where(function($q) {
            $q->where('time', 'like', '%"status":"LIVE"%')
                ->orWhere('time', 'like', '%"status":"HT"%')
                ->orWhere('time', 'like', '%"status":"ET"%')
                ->orWhere('time', 'like', '%"status":"PEN_LIVE"%')
                ->orWhere('time', 'like', '%"status":"BREAK"%');
        });

        $this->request->match_ids = json_encode($matches->select('match_id')->get()->pluck('match_id')->toArray());

        return $this->GetMatches();
    }

    public function GetH2HMatches(Request $request, int $id) {
        $match = ApiMatches::where('match_id', $id)->first();

        if(!$match) abort(404);

        $perPage = $request->per_page ? $request->per_page : 5;

        $localTeamMatches = ApiMatches::lastMatches($perPage)->where(function($q) use ($match) {
            $q->where('localteam_id', $match->localteam_id);
            $q->orWhere('visitorteam_id', $match->localteam_id);
        })
            ->addSelect(DB::raw('localteam_id = '.$match->localteam_id.' as is_home_match'))
            ->get();

        $visitorTeamMatches = ApiMatches::lastMatches($perPage)->where(function($q) use ($match) {
            $q->where('localteam_id', $match->visitorteam_id);
            $q->orWhere('visitorteam_id', $match->visitorteam_id);
        })
            ->addSelect(DB::raw('localteam_id = '.$match->visitorteam_id.' as is_home_match'))
            ->get();

        $h2h = ApiMatches::lastMatches($perPage)->where(function($q) use ($match) {
            $q->where(function($q1) use ($match) {
                $q1->where('localteam_id', $match->localteam_id);
                $q1->where('visitorteam_id', $match->visitorteam_id);
            })->orWhere(function($q1) use ($match) {
                $q1->where('localteam_id', $match->visitorteam_id);
                $q1->where('visitorteam_id', $match->localteam_id);
            });
        })->get();

        return [
            'localteam_matches' => $localTeamMatches,
            'visitorteam_matches' => $visitorTeamMatches,
            'h2h'   => $h2h,
        ];
    }

    public function GetTopTeams() {
        // TODO

        $data = [
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/9/9.png',
                'id' => '9',
                'slug' => 'manchester-city',
                'name' => 'მან. სიტი'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/14/14.png',
                'id' => '14',
                'slug' => 'manchester-united',
                'name' => 'მან.იუნ'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/8/8.png',
                'id' => '8',
                'slug' => 'liverpool',
                'name' => 'ლივერპული'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/18/18.png',
                'id' => '18',
                'slug' => 'chelsea',
                'name' => 'ჩელსი'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/19/19.png',
                'id' => '19',
                'slug' => 'arsenal',
                'name' => 'არსენალი'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/6/6.png',
                'id' => '6',
                'slug' => 'tottenham-hotspur',
                'name' => 'ტოტენჰემი'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/21/629.png',
                'id' => '629',
                'slug' => 'ajax',
                'name' => 'აიაქსი',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/15/591.png',
                'id' => '591',
                'slug' => 'paris-saint-germain',
                'name' => 'პსჟ',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/12/652.png',
                'id' => '652',
                'slug' => 'porto',
                'name' => 'პორტუ',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/12/3468.png',
                'id' => '3468',
                'slug' => 'real-madrid',
                'name' => 'რეალი'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/19/83.png',
                'id' => '83',
                'slug' => 'barcelona',
                'name' => 'ბარსელონა'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images/soccer/teams/12/7980.png',
                'id' => '7980',
                'slug' => 'atletico-madrid',
                'name' => 'ატლეტიკო'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/4/676.png',
                'id' => '676',
                'slug' => 'sevilla',
                'name' => 'სევილია'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/22/214.png',
                'id' => '214',
                'slug' => 'valencia',
                'name' => 'ვალენსია'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/17/625.png',
                'id' => '625',
                'slug' => 'juventus',
                'name' => 'იუვენტუსი'
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/21/597.png',
                'id' => '597',
                'slug' => 'napoli',
                'name' => 'ნაპოლი'
            ],

            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/15/79.png',
                'id' => '79',
                'slug' => 'olympique-lyonnais',
                'name' => 'ლიონი',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/29/605.png',
                'id' => '605',
                'slug' => 'benfica',
                'name' => 'ბენფიკა',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/17/113.png',
                'id' => '113',
                'slug' => 'milan',
                'name' => 'მილანი',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/18/2930.png',
                'id' => '2930',
                'slug' => 'inter',
                'name' => 'ინტერი',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/5/37.png',
                'id' => '37',
                'slug' => 'roma',
                'name' => 'რომა',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/11/43.png',
                'id' => '43',
                'slug' => 'lazio',
                'name' => 'ლაციო',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/23/503.png',
                'id' => '503',
                'slug' => 'bayern-munchen',
                'name' => 'ბაიერნი',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/4/68.png',
                'id' => '68',
                'slug' => 'borussia-dortmund',
                'name' => 'დორტმუნდი',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/3/67.png',
                'id' => '67',
                'slug' => 'schalke-04',
                'name' => 'შალკე 04',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/5/6789.png',
                'id' => '6789',
                'slug' => 'monaco',
                'name' => 'მონაკო',
            ],
            [
                'img' => 'https://cdn.sportmonks.com/images//soccer/teams/12/44.png',
                'id' => '44',
                'slug' => 'olympique-marseille',
                'name' => 'მარსელი',
            ]
        ];

        return $data;
    }
}
