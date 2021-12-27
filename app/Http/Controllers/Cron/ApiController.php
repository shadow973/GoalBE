<?php

namespace App\Http\Controllers\Cron;

use App\Models\ApiStage;
use App\Models\ApiTeamLeagues;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use SoccerAPI;
use App\Models\ApiLeagues;
use App\Models\ApiCountries;
use App\Models\ApiMatches;
use App\Models\ApiTeams;
use App\Models\ApiStandings;
use App\Models\ApiTeamsPlayers;
use Illuminate\Support\Facades\DB;
use \App\Http\Controllers\LiveScoreController;
use App\Models\ApiCardscorers;
use App\Models\ApiAssistscorers;
use App\Models\ApiGoalscorers;
use App\Models\ApiTeamsStandings;
use App\Models\ApiSeasons;
use App\Models\ApiRounds;

class ApiController extends Controller
{

    public $match_incs = "localTeam,visitorTeam,substitutions,goals,cards,other,corners,lineup,bench,stats,highlights";

    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function parseStages() {
        $seasons = ApiLeagues::where('live_standings', 1)->get()->pluck('current_season_id');

        foreach ($seasons as $season) {
            $response = $this->ApiCron('stages/season/'.$season, null);
            $response = json_decode($response);

            if(!isset($response->data)) continue;
            $data = $response->data;

            foreach ($data as $stageData) {
                $stage = ApiStage::where('stage_id', $stageData->id)->first();
                if(!$stage) {
                    $stage = new ApiStage();
                    $stage->stage_id = $stageData->id;
                }
                $stage->name = $stageData->name;
                $stage->type = $stageData->type;
                $stage->league_id = $stageData->league_id;
                $stage->season_id = $stageData->season_id;
                $stage->sort_order = $stageData->sort_order;
                $stage->has_standings = $stageData->has_standings;
                $stage->save();
            }
        }
    }

    public function parseSeasons()
    {
        $p = 1;
        $max_page = 1;
        do {
            $response = $this->ApiCron('seasons', null, '&page=' . $p);
            $response = json_decode($response);

            if (isset($response->meta->pagination->total_pages)) {
                $max_page = $response->meta->pagination->total_pages;
            }

            // $response = SoccerAPI::leagues()->all();
            foreach ($response->data as $l) {
                $season = ApiSeasons::where('season_id', $l->id);
                if ($season->count() > 0) {
                    $season = $season->get()->first();
                    $season->season_id  = $l->id;
                    $season->name  = $l->name;
                    $season->league_id  = $l->league_id;
                    $season->is_current_season  = $l->is_current_season;
                    $season->current_round_id  = $l->current_round_id;
                    $season->current_stage_id  = $l->current_stage_id;

                    $season->save();
                } else {
                    $season = new ApiSeasons();
                    $season->season_id  = $l->id;
                    $season->name  = $l->name;
                    $season->league_id  = $l->league_id;
                    $season->is_current_season  = $l->is_current_season;
                    $season->current_round_id  = $l->current_round_id;
                    $season->current_stage_id  = $l->current_stage_id;
                    $season->save();
                }

                echo $l->name . PHP_EOL;
            }
            $p++;
        } while ($p <= $max_page);

    }

    public function parseLeagues()
    {
        $p = 1;
        $max_page = 1;
        do {
            $response = $this->ApiCron('leagues', null, '&page=' . $p);
            $response = json_decode($response);

            if (isset($response->meta->pagination->total_pages)) {
                $max_page = $response->meta->pagination->total_pages;
            }

            // $response = SoccerAPI::leagues()->all();
            foreach ($response->data as $l) {
                $league = ApiLeagues::where('league_id', $l->id);
                if ($league->count() > 0) {
                    $league = $league->get()->first();

                    $league->legacy_id = $l->legacy_id;
                    $league->country_id = $l->country_id;
                    $league->name = $l->name;
                    $league->is_cup = ($l->is_cup) ? 1 : 0;
                    $league->current_season_id = $l->current_season_id;
                    $league->current_round_id = $l->current_round_id;
                    $league->current_stage_id = $l->current_stage_id;
                    $league->live_standings = ($l->live_standings) ? 1 : 0;
                    $league->coverage = json_encode($l->coverage);
                    $league->slug = url_slug($l->name,['transliterate' => true]);

                    $league->save();
                } else {
                    $league = new ApiLeagues();
                    $league->league_id = $l->id;
                    $league->legacy_id = $l->legacy_id;
                    $league->country_id = $l->country_id;
                    $league->name = $l->name;
                    $league->is_cup = ($l->is_cup) ? 1 : 0;
                    $league->current_season_id = $l->current_season_id;
                    $league->current_round_id = $l->current_round_id;
                    $league->current_stage_id = $l->current_stage_id;
                    $league->live_standings = ($l->live_standings) ? 1 : 0;
                    $league->coverage = json_encode($l->coverage);
                    $league->slug = url_slug($l->name,['transliterate' => true]);

                    $league->save();
                }

                echo $l->name . PHP_EOL;
            }
            $p++;
        } while ($p <= $max_page);

    }

    public function parseCountries()
    {
        $p = 1;
        $max_page = 1;
        do {
            $response = $this->ApiCron('countries', null, '&page=' . $p);
            $response = json_decode($response);

            if (isset($response->meta->pagination->total_pages)) {
                $max_page = $response->meta->pagination->total_pages;
            }

//            $response = SoccerAPI::countries()->all();
            foreach ($response->data as $l) {
                $country = ApiCountries::where('country_id', $l->id);
                if ($country->count() > 0) {
                    $country = $country->get()->first();

                    $country->name = $l->name;
                    $country->extra = json_encode($l->extra);
                    $country->flag = (!empty($l->extra)) ? $l->extra->flag : '';

                    $country->save();
                } else {

                    $country = new ApiCountries();
                    $country->country_id = $l->id;

                    $country->name = $l->name;
                    $country->extra = json_encode($l->extra);
                    $country->flag = (!empty($l->extra)) ? $l->extra->flag : '';

                    $country->save();
                }

                echo $l->name . PHP_EOL;
            }

            $p++;
        } while ($p <= $max_page);

    }

    public function parseToDay()
    {
//        $response = SoccerAPI::livescores()->today();

        $p = 1;
        $max_page = 1;
        do {

            echo PHP_EOL.$max_page.PHP_EOL;

            $response = $this->ApiCron('livescores', $this->match_incs, '&page=' . $p);
            $response = json_decode($response);

            if (isset($response->meta->pagination->total_pages)) {
                $max_page = $response->meta->pagination->total_pages;
            }
            $this->matchUpdater($response);
            $p++;
        } while ($p <= $max_page);


    }

    public function parseNow()
    {
//        $response = SoccerAPI::livescores()->now();
        $response = $this->ApiCron('livescores/now', $this->match_incs);
        $response = json_decode($response);
        $this->matchUpdater($response);
        $this->updateCurrentDayJson();
        Cache::tags(['match-list'])->flush();
        die('123');

    }

    public function updateCurrentDayJson()
    {
        $this->request->byleague = 1;
        $this->request->docache = 1;
        $cntr = new LiveScoreController($this->request);
        $json = $cntr->GetMatches();
        $json = json_encode($json);
        echo PHP_EOL . base_path() . PHP_EOL;
        file_put_contents(base_path() . '/current_day_json.json', $json);
        // $test = file_get_contents(base_path() . '/current_day_json.json');
        // echo $test;
        echo "putted".PHP_EOL;
    }

    public function parseLastMonth() {
        $dates = $this->fixtureDates(31, true);
        foreach ($dates as $date) {
            $p = 1;
            $max_page = 1;
            do {
                $url = 'fixtures/between/' . end($dates) . '/' . $dates[0];
                echo PHP_EOL . $max_page . PHP_EOL;
                $response = $this->ApiCron($url, $this->match_incs, '&page=' . $p);
                $response = json_decode($response);
                print_r($response->meta);
                if (isset($response->meta->pagination->total_pages)) {
                    $max_page = $response->meta->pagination->total_pages;
                }
                $this->matchUpdater($response);
                $p++;
            } while ($p <= $max_page);
        }
    }

    public function parseMonth()
    {
        $dates = $this->fixtureDates(90);


        foreach ($dates as $date) {


            $p = 1;
            $max_page = 1;
            do {
                $url = 'fixtures/between/' . $date . '/' . $date;
                echo PHP_EOL.$max_page.PHP_EOL;
                $response = $this->ApiCron($url, $this->match_incs, '&page=' . $p);
                $response = json_decode($response);
                print_r($response->meta);
                if (isset($response->meta->pagination->total_pages)) {
                    $max_page = $response->meta->pagination->total_pages;
                }
                $this->matchUpdater($response);
                $p++;
            } while ($p <= $max_page);


            // $url = 'fixtures/between/' . $date . '/' . $date;
            // $response = $this->ApiCron($url, $this->match_incs);
            // $response = json_decode($response);
            // $this->matchUpdater($response);
        }


    }

    public function matchUpdater($response)
    {
        if (!isset($response->data)) {
            echo PHP_EOL . " -- DATA NOT FOUND --" . PHP_EOL;
            return false;
        }

        foreach ($response->data as $m) {
            $startDate = ApiMatches::convertUtcToTbilisiTimezone($m->time->starting_at->date_time);
            $m->time->starting_at->date_time = $startDate->format('Y-m-d H:i:s');
            $m->time->starting_at->date = $startDate->format('Y-m-d');
            $m->time->starting_at->time = $startDate->format('H:i:s');

            echo $m->id . PHP_EOL;
            $match = ApiMatches::where('match_id', $m->id);
            if ($match->count() > 0) {
                $match = $match->get()->first();
                $match->league_id = $m->league_id;
                $match->season_id = $m->season_id;
                $match->stage_id = $m->stage_id;
                $match->round_id = $m->round_id;
                $match->group_id = $m->group_id;
                $match->aggregate_id = $m->aggregate_id;
                $match->venue_id = $m->venue_id;
                $match->referee_id = $m->referee_id;
                $match->localteam_id = $m->localteam_id;
                $match->visitorteam_id = $m->visitorteam_id;
                $match->weather_report = json_encode($m->weather_report);
                $match->commentaries = $m->commentaries;
                $match->attendance = $m->attendance;
                $match->pitch = $m->pitch;
                $match->winning_odds_calculated = $m->winning_odds_calculated;
                $match->formations = json_encode($m->formations);
                $match->scores = json_encode($m->scores);
                $match->localteam_score = $m->scores->localteam_score;
                $match->visitorteam_score = $m->scores->visitorteam_score;
                $match->localteam_pen_score = $m->scores->localteam_pen_score;
                $match->visitorteam_pen_score = $m->scores->visitorteam_pen_score;
                $match->ht_score = $m->scores->ht_score;
                $match->ft_score = $m->scores->ft_score;
                $match->et_score = $m->scores->et_score;
                $match->time = json_encode($m->time);
                $match->time_status = $m->time->status;
                $match->starting_at = $startDate->format('Y-m-d H:i:s');
                $match->time_minute = $m->time->minute;
                $match->time_second = $m->time->second;
                $match->time_added_time = $m->time->added_time;
                $match->time_extra_minute = $m->time->extra_minute;
                $match->time_injury_time = $m->time->injury_time;
                $match->localteam_coach_id = $m->coaches->localteam_coach_id;
                $match->visitorteam_coach_id = $m->coaches->visitorteam_coach_id;
                $match->standings_localteam_position = $m->standings->localteam_position;
                $match->standings_visitorteam_position = $m->standings->visitorteam_position;
                $match->leg = $m->leg;
                $match->colors = json_encode($m->colors);
                $match->deleted = $m->deleted;
                $match->starting_date = date('Y-m-d', $m->time->starting_at->timestamp);
                $match->date = date('Y-m-d');
                $match->match_json = json_encode($m);

                if(in_array($m->time->status, ['LIVE', 'HT', 'ET', 'PEN_LIVE'])){
                    $match->time_status_priority = 1;
                }elseif(in_array($m->time->status, ['FT', 'FT_PEN'])){
                    $match->time_status_priority = 2;
                }elseif($m->time->starting_at->time == '00:00:00'){
                    $match->time_status_priority = 4;
                }else{
                    $match->time_status_priority = 3;
                }

                $match->save();
            } else {

                $match = new ApiMatches();
                $match->match_id = $m->id;
                $match->league_id = $m->league_id;
                $match->season_id = $m->season_id;
                $match->stage_id = $m->stage_id;
                $match->round_id = $m->round_id;
                $match->group_id = $m->group_id;
                $match->aggregate_id = $m->aggregate_id;
                $match->venue_id = $m->venue_id;
                $match->referee_id = $m->referee_id;
                $match->localteam_id = $m->localteam_id;
                $match->visitorteam_id = $m->visitorteam_id;
                $match->weather_report = json_encode($m->weather_report);
                $match->commentaries = $m->commentaries;
                $match->attendance = $m->attendance;
                $match->pitch = $m->pitch;
                $match->winning_odds_calculated = $m->winning_odds_calculated;
                $match->formations = json_encode($m->formations);
                $match->scores = json_encode($m->scores);
                $match->localteam_score = $m->scores->localteam_score;
                $match->visitorteam_score = $m->scores->visitorteam_score;
                $match->localteam_pen_score = $m->scores->localteam_pen_score;
                $match->visitorteam_pen_score = $m->scores->visitorteam_pen_score;
                $match->ht_score = $m->scores->ht_score;
                $match->ft_score = $m->scores->ft_score;
                $match->et_score = $m->scores->et_score;
                $match->time = json_encode($m->time);
                $match->time_status = $m->time->status;
                $match->starting_at = date('Y-m-d H:i:s', $m->time->starting_at->timestamp);
                $match->time_minute = $m->time->minute;
                $match->time_second = $m->time->second;
                $match->time_added_time = $m->time->added_time;
                $match->time_extra_minute = $m->time->extra_minute;
                $match->time_injury_time = $m->time->injury_time;
                $match->localteam_coach_id = $m->coaches->localteam_coach_id;
                $match->visitorteam_coach_id = $m->coaches->visitorteam_coach_id;
                $match->standings_localteam_position = $m->standings->localteam_position;
                $match->standings_visitorteam_position = $m->standings->visitorteam_position;
                $match->leg = $m->leg;
                $match->colors = json_encode($m->colors);
                $match->deleted = $m->deleted;
                $match->starting_date = date('Y-m-d', $m->time->starting_at->timestamp);
                $match->date = date('Y-m-d');
                $match->match_json = json_encode($m);

                if(in_array($m->time->status, ['LIVE', 'HT', 'ET', 'PEN_LIVE'])){
                    $match->time_status_priority = 1;
                }elseif(in_array($m->time->status, ['FT', 'FT_PEN'])){
                    $match->time_status_priority = 2;
                }elseif($m->time->starting_at->time == '00:00:00'){
                    $match->time_status_priority = 4;
                }else{
                    $match->time_status_priority = 3;
                }

                $match->save();
            }
        }
    }

    public function parseTodayTeams()
    {
        $matches = ApiMatches::where('updated_at', ">=", DB::raw("DATE(NOW())"))->get();
        //$matches = ApiMatches::where('updated_at', ">", '2021-03-15 00:00:00')->get();
        $teams = [];

        foreach ($matches as $m) {

            $teams[] = $m->localteam_id;
            $teams[] = $m->visitorteam_id;

        }


        foreach ($teams as $tid) {
            $brck = false;

            $dbtm = ApiTeams::where('team_id', $tid);

            if ($dbtm->count() > 0) {
                $dbtm = $dbtm->first();

                $date1 = $dbtm->updated_at;
                $date2 = date('Y-m-d H:i:s');

                $diff = abs(strtotime($date2) - strtotime($date1));

                $H = floor($diff / (60 * 60));

                if ($H < 48) {
                    $brck = true;
                }

            }

            if ($brck) {
                echo "exist.." . PHP_EOL;
                continue;
            } else {
                echo 'parse' . PHP_EOL;
            }


            $url = 'teams/' . $tid;
            $response = $this->ApiCron($url, 'transfers,venue,goalscorers,assistscorers,cardscorers');
            $response = json_decode($response);
//            $response = SoccerAPI::teams()->byId($tid);
            echo $tid . PHP_EOL;
            if (!isset($response->data)) {
                continue;
            }

            $team = ApiTeams::where('team_id', $tid);
            if ($team->count() > 0) {
                $team = $team->get()->first();

                $team->team_id = $response->data->id;
                $team->legacy_id = $response->data->legacy_id;
                $team->name = $response->data->name;
                $team->short_code = $response->data->short_code;
                $team->twitter = $response->data->twitter;
                $team->country_id = $response->data->country_id;
                //$team->national_team = $response->data->national_team;
                $team->founded = $response->data->founded;
                $team->logo_path = $response->data->logo_path;
                $team->venue_id = $response->data->venue_id;
                $team->current_season_id = $response->data->current_season_id;
                $team->json_data = json_encode($response);
                $team->slug = url_slug($response->data->name,['transliterate' => true]);

                $team->save();
            } else {

                $team = new ApiTeams();
                $team->team_id = $response->data->id;
                $team->legacy_id = $response->data->legacy_id;
                $team->name = $response->data->name;
                $team->name_geo = '';
                $team->short_code = $response->data->short_code;
                $team->twitter = $response->data->twitter;
                $team->country_id = $response->data->country_id;
                //$team->national_team = $response->data->national_team;
                $team->founded = $response->data->founded;
                $team->logo_path = $response->data->logo_path;
                $team->venue_id = $response->data->venue_id;
                $team->current_season_id = $response->data->current_season_id;
                $team->json_data = json_encode($response);
                $team->slug = url_slug($response->data->name,['transliterate' => true]);

                $team->save();
            }


        }

    }

    public function getPlayer($playerId) {
        $url = 'players/' . $playerId;
        $response = $this->ApiCron($url);
        $response = json_decode($response);
//            $response = SoccerAPI::teams()->byId($tid);
        if (!isset($response->data)) {
            return false;
        }

        $pl = $response->data;

        $player = ApiTeamsPlayers::where('player_id', $playerId);
        if ($player->count() > 0) {
            $player = $player->first();

            $player->team_id = $pl->team_id;
            $player->player_id = $pl->player_id;
            $player->common_name = $pl->common_name;
            $player->fullname = $pl->fullname;
            $player->firstname = $pl->firstname;
            $player->lastname = $pl->lastname;
            $player->nationality = $pl->nationality;
            $player->image_path = $pl->image_path;
            $player->data = json_encode($pl, JSON_UNESCAPED_UNICODE);
            $player->slug = url_slug($pl->fullname,['transliterate' => true]);


            $player->save();
        } else {
            $player = new ApiTeamsPlayers();
            $player->team_id = $pl->team_id;
            $player->player_id = $pl->player_id;
            $player->common_name = $pl->common_name;
            $player->fullname = $pl->fullname;
            $player->firstname = $pl->firstname;
            $player->lastname = $pl->lastname;
            $player->nationality = $pl->nationality;
            $player->image_path = $pl->image_path;
            $player->data = json_encode($pl, JSON_UNESCAPED_UNICODE);
            $player->slug = url_slug($pl->fullname,['transliterate' => true]);

            $player->save();
        }

        return $player;
    }

    public function parseTeamsByMatches()
    {

        $matches = ApiMatches::all();
        $teams = collect();

        foreach ($matches as $m) {

            $teams->push($m->localteam_id);
            $teams->push($m->visitorteam_id);

        }


        foreach ($teams->unique() as $tid) {

            $brck = false;

            $dbtm = ApiTeams::where('team_id', $tid);

            if ($dbtm->count() > 0) {
                $dbtm = $dbtm->first();

                $date1 = $dbtm->updated_at;
                $date2 = date('Y-m-d H:i:s');

                $diff = abs(strtotime($date2) - strtotime($date1));

                $H = floor($diff / (60 * 60));

                if ($H < 48) {
                    $brck = true;
                }

            }

            if ($brck) {
                echo "exist.." . PHP_EOL;
                continue;
            } else {
                echo 'parse' . PHP_EOL;
            }


            $url = 'teams/' . $tid;
            $response = $this->ApiCron($url, 'transfers,venue,goalscorers,assistscorers,cardscorers');
            $response = json_decode($response);
//            $response = SoccerAPI::teams()->byId($tid);
            echo $tid . PHP_EOL;
            if (!isset($response->data)) {
                continue;
            }

            $team = ApiTeams::where('team_id', $tid);
            if ($team->count() > 0) {
                $team = $team->get()->first();

                $team->team_id = $response->data->id;
                $team->legacy_id = $response->data->legacy_id;
                $team->name = $response->data->name;
                $team->short_code = $response->data->short_code;
                $team->twitter = $response->data->twitter;
                $team->country_id = $response->data->country_id;
                $team->national_team = $response->data->national_team;
                $team->founded = $response->data->founded;
                $team->logo_path = $response->data->logo_path;
                $team->venue_id = $response->data->venue_id;
                $team->current_season_id = $response->data->current_season_id;
                $team->json_data = json_encode($response);
                $team->slug = url_slug($response->data->name,['transliterate' => true]);

                $team->save();
            } else {

                $team = new ApiTeams();
                $team->team_id = $response->data->id;
                $team->legacy_id = $response->data->legacy_id;
                $team->name = $response->data->name;
                $team->short_code = $response->data->short_code;
                $team->twitter = $response->data->twitter;
                $team->country_id = $response->data->country_id;
                $team->national_team = $response->data->national_team;
                $team->founded = $response->data->founded;
                $team->logo_path = $response->data->logo_path;
                $team->venue_id = $response->data->venue_id;
                $team->current_season_id = $response->data->current_season_id;
                $team->json_data = json_encode($response);
                $team->slug = url_slug($response->data->name,['transliterate' => true]);

                $team->save();
            }


        }

    }


    public function ApiCron($url_s, $include = null, $other = null)
    {
        $url = 'https://soccer.sportmonks.com/api/v2.0/';
        $url .= $url_s;
        $url .= "?api_token=" . env('SPORTMONKS_API_TOKEN');
        if (!empty($include))
            $url .= "&include=" . $include;

        if (!empty($other))
            $url .= $other;

       // echo $url;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
        ));
        $resp = curl_exec($curl);

        curl_close($curl);

        return $resp;
    }

    public function getTeamsByLeague() {
        $leagues = ApiLeagues::where('live_standings', true)
            ->whereNotNull('current_season_id')
            ->whereNotNull('current_round_id')
            ->get();
        foreach ($leagues as $league) {
            echo $league->name.'-';
            $url = 'seasons/' . $league->current_season_id;
            $response = $this->ApiCron($url, 'fixtures');
            $response = json_decode($response);
            $leagueTeams = collect();
            foreach ($response->data->fixtures->data as $fixture) {
                if($leagueTeams->search($fixture->localteam_id) === false)
                    $leagueTeams->push($fixture->localteam_id);

                if($leagueTeams->search($fixture->visitorteam_id) === false)
                    $leagueTeams->push($fixture->visitorteam_id);
            }

            $alreadyAdded = ApiTeamLeagues::where('league_id', $league->league_id)
                ->where('season_id',$league->current_season_id)
                ->select('team_id')
                ->distinct()
                ->get();
            foreach ($leagueTeams as $teamId) {
                if($alreadyAdded->where('team_id', $teamId)->count() == 0) {
                    ApiTeamLeagues::create([
                        'league_id' => $league->league_id,
                        'team_id' => $teamId,
                        'season_id' => $league->current_season_id,
                        'stats' => '[]'
                    ]);
                }
            }

            echo 'OK'.PHP_EOL;
        }
    }

    public function getMatches_test()
    {
        $p = 1;
        $max_page = 1;
        do {
            // $url = 'fixtures/between/2018-10-01/2020-02-02';
            $url = 'fixtures/between/2021-07-11/2021-07-12';
            $response = $this->ApiCron($url, $this->match_incs, '&page=' . $p);
            // die;
            $response = json_decode($response);

            if (isset($response->meta->pagination->total_pages)) {
                $max_page = $response->meta->pagination->total_pages;
            }

            $this->matchUpdater($response);

            // $response = SoccerAPI::leagues()->all();

            $p++;
        } while ($p <= $max_page);


//             $url = 'fixtures/between/2018-10-01/2019-02-18';
//             $response = $this->ApiCron($url, $this->match_incs);
//             $response = json_decode($response);
//             print_r($response->meta);
//             die;
// //            echo count($response->data);
//             $this->matchUpdater($response);
        echo "done.";
    }


    public function fixtureDates($count = 31, $reverse = false)
    {
        $dates = [];
        $date = new \DateTime(date('Y-m-d'));

        $oneDay = new \DateInterval('P1D');

        if(!$reverse) {
            for ($day = 1; $day <= $count; $day++) {

                $dates[] = $date->format('Y-m-d');
                $date->add($oneDay);
            }
        } else {
            for ($day = $count; $day > 0; $day--) {

                $dates[] = $date->format('Y-m-d');
                $date->sub($oneDay);
            }
        }

        return $dates;
    }

    public function parseStandings()
    {
        $leagues = ApiLeagues::where('live_standings', 1)
            ->groupBy('current_season_id');
        $leagues = $leagues->select('current_season_id');

        $leagues = $leagues->get()->toArray();

        foreach ($leagues as $l) {
            $response = $this->ApiCron('standings/season/' . $l['current_season_id'], 'team,league,season,round,stage');
            $response = json_decode($response);

            if(isset($response->data))
                foreach ($response->data as $standing) {
                    $this->setStanding($standing);
                    $this->addTeamsStandings($standing);
                }
        }

    }

    public function addTeamsStandings($data)
    {
        if (empty($data->standings)) {
            return false;
        }
        // print_r($data->standings);
        foreach ($data->standings->data as $st) {
            if(!$st || !property_exists($st, 'team_id')) continue;
            // print_r($st);
            // die;

            $standing = ApiTeamsStandings::where('league_id', $data->league_id)
                ->where('team_id', $st->team_id)
                ->where('season_id', $data->season_id);

            if ($standing->count() > 0) {
                $standing = $standing->first();
                echo "update team standing ..." . PHP_EOL;
            } else {
                $standing = new ApiTeamsStandings();
                echo "insert team standing..." . PHP_EOL;
            }

//        $standing->standings = $data->standings
            $standing->league_id = $data->league_id;
            $standing->season_id = $data->season_id;
            $standing->standing_id = isset($data->id) ? $data->id : 0;
            $standing->name = $data->name;
            $standing->league_id = $data->league_id;
            $standing->season_id = $data->season_id;
            $standing->type = isset($data->type) ? $data->type : '';
            $standing->stage_id = $data->stage_id;
            $standing->stage_name = $data->stage_name;
            $standing->position = $st->position;
            $standing->team_id = $st->team_id;
            $standing->team_name = $st->team_name;
            $standing->round_id = $st->round_id;
            $standing->round_name = $st->round_name;
            $standing->group_id = $st->group_id;
            $standing->group_name = $st->group_name;
            $standing->result = $st->result;
            $standing->points = $st->points;
            $standing->recent_form = $st->recent_form;
            $standing->status = $st->status;
            $standing->overall = json_encode($st->overall);
            $standing->home = json_encode($st->home);
            $standing->away = json_encode($st->away);
            $standing->total = json_encode($st->total);
            $standing->save();
        }
    }

    public function setStanding($data)
    {
        $standing = ApiStandings::where('league_id', $data->league_id)
            ->where('season_id', $data->season_id)
            ->where('standing_id', $data->id);

        if ($standing->count() > 0) {
            $standing = $standing->first();
            echo "update ..." . PHP_EOL;
        } else {
            $standing = new ApiStandings();
            echo "insert..." . PHP_EOL;
        }

        $standing->standing_id = isset($data->id) ? $data->id : 0;
        $standing->name = $data->name;
        $standing->league_id = $data->league_id;
        $standing->season_id = $data->season_id;
        $standing->type = isset($data->type) ? $data->type : '';
        $standing->stage_id = $data->stage_id;
        $standing->stage_name = $data->stage_name;
        $standing->standings = json_encode($data->standings);
        $standing->save();
    }


    public function parseTodayTeamsPlayers()
    {
        echo "start" . PHP_EOL;

        $matches = ApiMatches::where('updated_at', ">", DB::raw("DATE(NOW())"))
                                ->where('updated_at', "<", DB::raw("DATE(NOW() + INTERVAL 1 DAY)"))
                                ->orderByDesc('id')->get();
        $teams = collect();

        foreach ($matches as $m) {
            if($teams->where('id', $m->localteam_id)->count() == 0)
                $teams->push(['id' => $m->localteam_id, 'season_id' => $m->season_id]);

            if($teams->where('id', $m->visitorteam_id)->count() == 0)
                $teams->push(['id' => $m->visitorteam_id, 'season_id' => $m->season_id]);
        }

        echo count($teams) . PHP_EOL;

        foreach ($teams->toArray() as $key => $tm) {
            echo $tm['id'] . ' - ' . $tm['season_id'] . PHP_EOL;

            $old_players_d = ApiTeamsPlayers::where('team_id', $tm['id'])->get()->toArray();

            $brck = false;
            if (count($old_players_d) > 0) {
                $date1 = current($old_players_d)['updated_at'];
                $date2 = date('Y-m-d H:i:s');

                $diff = abs(strtotime($date2) - strtotime($date1));

                $H = floor($diff / (60 * 60));

                if ($H < 48) {
                    $brck = true;
                }
            }

            if ($brck) {
                echo "exist.." . PHP_EOL;
                continue;
            } else {
                echo 'parse' . PHP_EOL;
            }


            $old_players = [];
            foreach ($old_players_d as $pl) {
                $old_players[$pl['player_id']] = $pl;
            }

            $response = $this->ApiCron('squad/season/' . $tm['season_id'] . '/team/' . $tm['id'],
                'player,position');
            $response = json_decode($response);

            if (!isset($response->data)) {
                print_r($response);
                continue;
            }

            if (empty($response)) {
                echo PHP_EOL . 'continue' . PHP_EOL;
                continue;
            }

            foreach ($response->data as $pl) {
                if (isset($old_players[$pl->player_id])) {
                    unset($old_players[$pl->player_id]);
                }

                if (!isset($pl->player->data)) {
                    continue;
                }

                $player_1 = ApiTeamsPlayers::where('player_id', $pl->player_id);
                if($player_1->count() > 1){
                    $player_1->delete();
                }

                $player = ApiTeamsPlayers::where('team_id', $tm['id'])->where('player_id', $pl->player_id);
                if ($player->count() > 0) {
                    $player = $player->get()->first();

                    $player->team_id = $tm['id'];
                    $player->player_id = $pl->player_id;
                    $player->common_name = $pl->player->data->common_name;
                    $player->fullname = $pl->player->data->fullname;
                    $player->firstname = $pl->player->data->firstname;
                    $player->lastname = $pl->player->data->lastname;
                    $player->nationality = $pl->player->data->nationality;
                    $player->image_path = $pl->player->data->image_path;
                    $player->data = json_encode($pl, JSON_UNESCAPED_UNICODE);
                    $player->slug = url_slug($pl->player->data->fullname,['transliterate' => true]);


                    $player->save();
                } else {
                    $player = new ApiTeamsPlayers();
                    $player->team_id = $tm['id'];
                    $player->player_id = $pl->player_id;
                    $player->common_name = $pl->player->data->common_name;
                    $player->fullname = $pl->player->data->fullname;
                    $player->firstname = $pl->player->data->firstname;
                    $player->lastname = $pl->player->data->lastname;
                    $player->nationality = $pl->player->data->nationality;
                    $player->image_path = $pl->player->data->image_path;
                    $player->data = json_encode($pl, JSON_UNESCAPED_UNICODE);
                    $player->slug = url_slug($pl->player->data->fullname,['transliterate' => true]);

                    $player->save();
                }

            }

            if (count($old_players) > 0) {
                $old = array_keys($old_players);

                ApiTeamsPlayers::whereIn('player_id', $old)->where('team_id', $tm['id'])
                    ->update(['status_id' => -1]);
            }


//            print_r($old_players);
//            die('die');

        }

    }

    public function parseTeamsPlayers()
    {

        echo "start" . PHP_EOL;

        $matches = ApiMatches::all();
        //$matches = ApiMatches::where('localteam_id', 8)->take(1)->get();
        $teams = [];

        foreach ($matches as $m) {
            $teams[] = ['id' => $m->localteam_id, 'season_id' => $m->season_id];
            $teams[] = ['id' => $m->visitorteam_id, 'season_id' => $m->season_id];
        }

        echo count($teams) . PHP_EOL;

        foreach ($teams as $tm) {
            $old_players_d = ApiTeamsPlayers::where('team_id', $tm['id'])->get()->toArray();

            $brck = false;
            if (count($old_players_d) > 0) {
                $date1 = current($old_players_d)['updated_at'];
                $date2 = date('Y-m-d H:i:s');

                $diff = abs(strtotime($date2) - strtotime($date1));

                $H = floor($diff / (60 * 60));

                if ($H < 48) {
                    $brck = true;
                }
            }

            if ($brck) {
                echo "exist.." . PHP_EOL;
                continue;
            } else {
                echo 'parse' . PHP_EOL;
            }


            $old_players = [];
            foreach ($old_players_d as $pl) {
                $old_players[$pl['player_id']] = $pl;
            }

            $response = $this->ApiCron('squad/season/' . $tm['season_id'] . '/team/' . $tm['id'],
                'player,position');
            $response = json_decode($response);

            if (!isset($response->data)) {
                print_r($response);
                continue;
            }

            if (empty($response)) {
                echo PHP_EOL . 'continue' . PHP_EOL;
                continue;
            }

            foreach ($response->data as $pl) {
                if (isset($old_players[$pl->player_id])) {
                    unset($old_players[$pl->player_id]);
                }

                if (!isset($pl->player->data)) {
                    continue;
                }

                $player_1 = ApiTeamsPlayers::where('player_id', $pl->player_id);
                if($player_1->count() > 1){
                    $player_1->delete();
                }

                $player = ApiTeamsPlayers::where('team_id', $tm['id'])->where('player_id', $pl->player_id);
                if ($player->count() > 0) {
                    $player = $player->get()->first();

                    $player->team_id = $tm['id'];
                    $player->player_id = $pl->player_id;
                    $player->common_name = $pl->player->data->common_name;
                    $player->fullname = $pl->player->data->fullname;
                    $player->firstname = $pl->player->data->firstname;
                    $player->lastname = $pl->player->data->lastname;
                    $player->nationality = $pl->player->data->nationality;
                    $player->image_path = $pl->player->data->image_path;
                    $player->data = json_encode($pl, JSON_UNESCAPED_UNICODE);
                    $player->slug = url_slug($pl->player->data->fullname,['transliterate' => true]);


                    $player->save();
                } else {
                    $player = new ApiTeamsPlayers();
                    $player->team_id = $tm['id'];
                    $player->player_id = $pl->player_id;
                    $player->common_name = $pl->player->data->common_name;
                    $player->fullname = $pl->player->data->fullname;
                    $player->firstname = $pl->player->data->firstname;
                    $player->lastname = $pl->player->data->lastname;
                    $player->nationality = $pl->player->data->nationality;
                    $player->image_path = $pl->player->data->image_path;
                    $player->data = json_encode($pl, JSON_UNESCAPED_UNICODE);
                    $player->slug = url_slug($pl->player->data->fullname,['transliterate' => true]);

                    $player->save();
                }

            }

            if (count($old_players) > 0) {
                $old = array_keys($old_players);

                ApiTeamsPlayers::whereIn('player_id', $old)->where('team_id', $tm['id'])
                    ->update(['status_id' => -1]);
            }


//            print_r($old_players);
//            die('die');

        }

    }


    public function generateCountryFlags()
    {
        $countries = ApiCountries::where('id', '>', 0)->get();

        foreach ($countries as $c) {
            Storage::disk('sftp')->put('countries/' . $c->country_id . '.svg', $c->flag);
            echo $c->name . PHP_EOL;
        }
    }

    public function parseSessionStats()
    {
        $seasons = ApiLeagues::select('current_season_id')
            ->where('id', '>', 0)
            ->where('live_standings', 1)
            ->get();

        foreach ($seasons as $season) {
            $response = $this->ApiCron('seasons/' . $season->current_season_id,
                'goalscorers,cardscorers,assistscorers,stats');
            $response = json_decode($response);

            if (isset($response->data->goalscorers) && count($response->data->goalscorers->data) > 0) {
                $this->goalscorers($response->data->goalscorers->data, $response->data->league_id);
            }
            if (isset($response->data->assistscorers) && count($response->data->assistscorers->data) > 0) {
                $this->assistscorers($response->data->assistscorers->data, $response->data->league_id);
            }
            if (isset($response->data->cardscorers) && count($response->data->cardscorers->data) > 0) {
                $this->cardscorers($response->data->cardscorers->data, $response->data->league_id);
            }

        }
    }

    public function goalscorers($data, $league_id)
    {
        echo 'goalscorers' . PHP_EOL;
        foreach ($data as $v) {
            $itm = ApiGoalscorers::where('player_id', $v->player_id)->where('season_id', $v->season_id);

            echo $v->season_id . '-' . $v->player_id . PHP_EOL;

            if ($itm->count() > 0) {
                $itm = $itm->get()->first();
                $itm->league_id = $league_id;
                $itm->position = $v->position;
                $itm->season_id = $v->season_id;
                $itm->player_id = $v->player_id;
                $itm->team_id = $v->team_id;
                $itm->stage_id = $v->stage_id;
                $itm->goals = $v->goals;
                $itm->penalty_goals = $v->penalty_goals;
                $itm->type = $v->type;
                $itm->save();
            } else {
                $itm = new ApiGoalscorers();
                $itm->league_id = $league_id;
                $itm->position = $v->position;
                $itm->season_id = $v->season_id;
                $itm->player_id = $v->player_id;
                $itm->team_id = $v->team_id;
                $itm->stage_id = $v->stage_id;
                $itm->goals = $v->goals;
                $itm->penalty_goals = $v->penalty_goals;
                $itm->type = $v->type;
                $itm->save();
            }
        }
    }

    public function assistscorers($data, $league_id)
    {
        echo 'assistscorers' . PHP_EOL;
        foreach ($data as $v) {
            $itm = ApiAssistscorers::where('player_id', $v->player_id)->where('season_id', $v->season_id);
            echo $v->season_id . '-' . $v->player_id . PHP_EOL;
            if ($itm->count() > 0) {
                $itm = $itm->get()->first();
                $itm->league_id = $league_id;
                $itm->position = $v->position;
                $itm->season_id = $v->season_id;
                $itm->player_id = $v->player_id;
                $itm->team_id = $v->team_id;
                $itm->stage_id = $v->stage_id;
                $itm->assists = $v->assists;
                $itm->type = $v->type;
                $itm->save();
            } else {
                $itm = new ApiAssistscorers();
                $itm->league_id = $league_id;
                $itm->position = $v->position;
                $itm->season_id = $v->season_id;
                $itm->player_id = $v->player_id;
                $itm->team_id = $v->team_id;
                $itm->stage_id = $v->stage_id;
                $itm->assists = $v->assists;
                $itm->type = $v->type;
                $itm->save();
            }
        }
    }

    public function cardscorers($data, $league_id)
    {
        echo 'cardscorers' . PHP_EOL;
        foreach ($data as $v) {
            $itm = ApiCardscorers::where('player_id', $v->player_id)->where('season_id', $v->season_id);
            echo $v->season_id . '-' . $v->player_id . PHP_EOL;
            if ($itm->count() > 0) {
                $itm = $itm->get()->first();
                $itm->league_id = $league_id;
                $itm->position = $v->position;
                $itm->season_id = $v->season_id;
                $itm->player_id = $v->player_id;
                $itm->team_id = $v->team_id;
                $itm->stage_id = $v->stage_id;
                $itm->yellowcards = $v->yellowcards;
                $itm->redcards = $v->redcards;
                $itm->type = $v->type;

                $itm->save();
            } else {
                $itm = new ApiCardscorers();
                $itm->league_id = $league_id;
                $itm->position = $v->position;
                $itm->season_id = $v->season_id;
                $itm->player_id = $v->player_id;
                $itm->team_id = $v->team_id;
                $itm->stage_id = $v->stage_id;
                $itm->yellowcards = $v->yellowcards;
                $itm->redcards = $v->redcards;
                $itm->type = $v->type;


                $itm->save();
            }
        }
    }

    public function parseRounds()
    {
        // $seasons = ApiLeagues::select('current_season_id')->get();
        $seasons = ApiLeagues::where('id','>',0)->where('live_standings', 1)
            ->get();

        foreach ($seasons as $ses) {

            // $sss = $ses->seasons();

            // foreach ($sss as $s) {



            $response = $this->ApiCron('rounds/season/' . $ses->current_season_id, '');
            // $response = $this->ApiCron('rounds/season/' . $s->season_id, '');
            $response = json_decode($response);
            if(!isset($response->data)){
                echo "DATA not exist, continue".PHP_EOL;
                continue;
            }

            foreach ($response->data as $v) {
                $itm = ApiRounds::where('round_id', $v->id)->where('season_id', $v->season_id);
                echo $v->season_id . '-' . $v->name . PHP_EOL;
                if ($itm->count() > 0) {
                    $itm = $itm->get()->first();
                    $itm->round_id = $v->id;
                    $itm->name = $v->name;
                    $itm->league_id = $v->league_id;
                    $itm->season_id = $v->season_id;
                    $itm->stage_id = $v->stage_id;
                    $itm->start = ($v->start != '0000-00-00')?$v->start:null;
                    $itm->end = ($v->end != '0000-00-00')?$v->end:null;

                    $itm->save();
                } else {
                    $itm = new ApiRounds();
                    $itm->round_id = $v->id;
                    $itm->name = $v->name;
                    $itm->league_id = $v->league_id;
                    $itm->season_id = $v->season_id;
                    $itm->stage_id = $v->stage_id;
                    $itm->start = ($v->start != '0000-00-00')?$v->start:null;
                    $itm->end = ($v->end != '0000-00-00')?$v->end:null;

                    $itm->save();
                }
            }

            // }
            //end foreach seasons

        }
    }


    public function parseBySeasonId()
    {
        $seasons = ApiLeagues::select('current_season_id')->get();

        foreach ($seasons as $ses) {
            $response = $this->ApiCron('seasons/' . $ses->current_season_id,
                'upcoming,stats');
            $response = json_decode($response);
            if (!isset($response->data->upcoming)) {
                echo PHP_EOL . " -- DATA NOT FOUND -- parseBySeasonId" . PHP_EOL;
                continue;
            }
            $this->matchUpdater($response->data->upcoming);
        }
    }

    public function parseSeasonStats() {
        $seasons = ApiSeasons::where('is_current_season', true)->get();

        foreach ($seasons as $season) {
            $response = $this->ApiCron('seasons/' . $season->season_id,
                'stats');
            $response = json_decode($response);
            if (!isset($response->data->stats)) {
                echo PHP_EOL . " -- DATA NOT FOUND -- parseSeasonStats" . PHP_EOL;
                continue;
            }
            $season->stats = json_encode($response->data->stats);
            $season->save();
        }
        echo 'done parseSeasonStats';
    }

    public function parseTeamStatsByLeague() {
        $teams = ApiTeams::all();
        foreach ($teams as $team) {
            $response = $this->ApiCron('teams/' . $team->team_id . '/current');
            $response = json_decode($response);
            if (isset($response->data)) {
                foreach ($response->data as $league) {
                    $league = $league->league;
                    $statResponse = $this->ApiCron('teams/' . $team->team_id,
                        'stats',
                        '&season=' . $league->data->current_season_id
                    );
                    $statResponse = json_decode($statResponse);
                    if (isset($statResponse->data) && isset($statResponse->data->stats) && isset($league->data)) {
                        //echo $statResponse->data->stats) ."\n";
                        //$leagueItem->stats = json_encode($statResponse->data->stats);
                        //$leagueItem->save();
                        foreach ($statResponse->data->stats->data as $stat) {
                            if($stat->season_id) {
                                $leagueItem = ApiTeamLeagues::where('team_id', $team->team_id)->where('league_id', $league->data->id)->where('season_id', $stat->season_id)->first();
                                if(!$leagueItem) {
                                    $leagueItem = new ApiTeamLeagues([
                                        'team_id' => $team->team_id,
                                        'league_id' => $league->data->id,
                                        'season_id' => $stat->season_id
                                    ]);
                                }
                                $leagueItem->stats = json_encode($stat);
                                //dump($leagueItem);
                                $leagueItem->save();
                            }
                        }
                    }
                }
            }
        }
        echo 'done parseTeamStatsByLeague';
    }

    public function parseTeamStatsBySeason() {
        $teams = ApiTeams::all();

        foreach ($teams as $team) {
            $response = $this->ApiCron('teams/' . $team->team_id,
                'stats','&season='.$team->current_season_id);
            $response = json_decode($response);
            if (!isset($response->data->stats)) {
                echo PHP_EOL . " -- DATA NOT FOUND -- parseTeamStatsBySeason" . PHP_EOL;
                continue;
            }

            $allStat = isset($response->data->stats) && isset($response->data->stats->data) ? collect($response->data->stats->data) : collect();
            $team->stats = json_encode($allStat->where('season_id', $team->current_season_id)->first());
            $team->save();
        }
        echo 'done parseTeamStatsBySeason';
    }

}
