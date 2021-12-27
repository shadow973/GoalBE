<?php

namespace App\Http\Controllers;

use App\Models\ApiTeams;
use Illuminate\Http\Request;
use JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Models\ApiLeagues;
Use App\League;

class LeagueController extends Controller
{
    public $request;
    protected $user;

    public function __construct(Request $request)
    {
        $this->request = $request;

        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function index(){
        if(isset($this->request->bytag) && !empty($this->request->bytag)){
            $this->request->id = $this->request->bytag;

            return $this->getItem($this->request->id, true);
        }
        $data = League::where('league_l.status_id','>', -1);
        $data->leftjoin('api_leagues as t', 't.league_id','=','league_l.league_id');

        $data->addSelect(DB::raw('
            league_l.* ,
            t.name as league_name            
         '));

        if(isset($this->request->menu)){
            $data->where('league_l.menu', 1);
        }

        return $data->get()->toArray();
    }

    public function getItem(){

        $data = League::where('id', $this->request->id);

        if(empty($data)){
            abort(404);
        }

        $data = $data->where('status_id','>', -1);
        if($data->count() == 0){
            abort(404);
        }

        $data = $data->first();

        if(!empty($data->league)){
            $data->league = $data->league;
        }

        return response()->json($data);
    }

    public function setItem(){

        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }


        $item = isset($this->request->id)? League::find($this->request->id) : new League();

        $data = $this->request->all();

        if(isset($data['name'])) $item->name = $data['name'];
        if(isset($data['league_id'])) $item->league_id = $data['league_id'];
        if(isset($data['description'])) $item->description = $data['description'];
        if(isset($data['priority'])) $item->priority = $data['priority'];
        if(isset($data['menu'])) $item->menu = $data['menu']; else $item->menu = 0;


        $item->save();
        $this->request->id = $item->id;

        return $this->getItem();
    }

    public function unsetItem(){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        if(!isset($this->request->id)){
            return abort(404);
        }

        $item = League::find($this->request->id);

        $item->status_id = -1;
        $item->save();

        return "ok";
    }
    
    public function GetCountryLeagues() {
        $leagues = [
            [
                'league_id' => 319,
                'name' => 'ქართული',
            ],
            [
                'league_id' => 8,
                'name' => 'ინგლისი',
            ],
            [
                'league_id' => 564,
                'name' => 'ესპანეთი',
            ],
            [
                'league_id' => 384,
                'name' => 'იტალია',
            ],
            [
                'league_id' => 82,
                'name' => 'გერმანია',
            ]
        ];

        return ['data' => $leagues];
    }

    public function GetLeagueIcon($leagueId) {
        $league = ApiLeagues::where('league_id', $leagueId)->firstOrFail();

        $png = env('STORAGE_PATH').'/images/countries/flags/'.$league->country_id.'.png';
        if(file_exists($png)) {
            header("Content-type: image/png");
            readfile($png);
            exit;
        } else {
            $link=env('STORAGE_PATH').'/images/countries/flags/'.$league->country_id.'.svg';
            $file = basename($link);
            $file = basename($link, ".svg");
            $im = new \Imagick();

            try {
                $svg = file_get_contents($link);
                $im->readImageBlob($svg);
                $im->resizeImage(256, 256, \Imagick::FILTER_CATROM, 1);
                $im->setImageFormat("png24");
                $im->writeImage($png);

                header("Content-Type: image/png");
                $thumbnail = $im->getImageBlob();
            } catch (\Exception $e) {
                header("Content-Type: image/png");
                $thumbnail = file_get_contents(env('STORAGE_PATH').'/images/countries/flags/no-flag.png');
            }
            echo $thumbnail;

            $im->clear();
            $im->destroy();
        }
    }
}
