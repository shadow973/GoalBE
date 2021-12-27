<?php

namespace App\Http\Controllers;

use App\Models\ApiTeamsPlayers;
use Illuminate\Http\Request;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\ArticlesController;
use \App\Models\ApiTeams;
use App\Models\ApiLeagues;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    private $request;

    public function __construct( Request $request ){
        $this->request = $request;
    }

    public function searchMobile(){
//        if(Auth::guard('api')->check()) {
//            if($_SERVER['REMOTE_ADDR'] == '217.147.224.85') {
//                dd(1);
//            }
//        }

        $page = $this->request->page ? $this->request->page : 1;
        $perPage = $this->request->per_page ? $this->request->per_page : 30;

        $teams = ApiTeams::selectRaw('team_id, name_geo as name, logo_path')
            ->limit($perPage)->offset($page - 1);

        $leagues =  ApiLeagues::selectRaw('league_id, name_geo as name, country_id, current_season_id, slug')
            ->limit($perPage)->offset($page - 1);

        $players = ApiTeamsPlayers::selectRaw('player_id, fullname, image_path')
            ->limit($perPage)->offset($page - 1);

        if(isset($this->request->search)){
            $s = $this->request->search;
            $teams->where(function( $query) use ($s) {
                $query->where('name','like',"%".$s."%");
                $query->orWhere('name_geo','like',"%".$s."%");
            });
            $leagues->where(function( $query) use ($s) {
                $query->where('name', 'LIKE', "%{$s}%");
                $query->orWhere('name_geo', 'LIKE', "%{$s}%");
            });
            $players->where(function( $query) use ($s) {
                $query->where('fullname', 'like', '%'.$s.'%');
                $query->orWhere('common_name', 'like', '%'.$s.'%');
            });
        }

        return [
            'teams' => $teams->get(),
            'leagues' => $leagues->get(),
            'players' => $players->get()
        ];
    }

    public function search(){

        $s = null;

        if(isset($this->request->s) && !empty($this->request->s)){
            $s = $this->request->s;
        }elseif(isset($this->request->q) && !empty($this->request->q)){
            $s = $this->request->q;
        }
        // else{

        // }

        $limit = (isset($this->request->per_page) && (int)$this->request->per_page < 30)? (int)$this->request->per_page : 30;



        if(empty($s)){
            return ['tags' => [], 'articles' => [], 'leagues' => [], 'players' => []];
        }

        $teams =  ApiTeams::selectRaw('team_id, name_geo as name, logo_path, slug')
            ->where('name', 'LIKE', "%{$s}%")
            ->orWhere('name_geo', 'LIKE', "%{$s}%")
            ->limit($limit)
            ->get();
        $leagues =  ApiLeagues::selectRaw('league_id, name_geo as name, country_id, current_season_id, slug')
            ->where('name', 'LIKE', "%{$s}%")
            ->orWhere('name_geo', 'LIKE', "%{$s}%")
            ->limit($limit)->get();
        $players = ApiTeamsPlayers::where('status_id', '>', 0)
            ->select(['player_id', 'fullname', 'image_path', 'slug'])
            ->where('fullname', 'like', '%'.$s.'%')
            ->orWhere('common_name', 'like', '%'.$s.'%')
            ->limit($limit)->get();

        $articles = new ArticlesController( $this->request );

        $articles = $articles->search($s, $this->request);

        $return['teams'] = $teams;
        // $return['tags'] = [];
        $return['articles'] = $articles;
        $return['leagues'] = $leagues;
        $return['players'] = $players;

        return $return;
    }
}
