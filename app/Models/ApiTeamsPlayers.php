<?php

namespace App\Models;

use App\Http\Controllers\Cron\ApiController;
use Illuminate\Database\Eloquent\Model;

class ApiTeamsPlayers extends Model
{
    protected $table = 'api_teams_players';
    // protected $connection =  LIVESCORE_CONNECTION;
    protected $connection =  'mysql';

    protected $primaryKey = 'player_id';

    protected $appends = ['stats', 'rating', 'goals', 'assists', 'cards', 'red_cards', 'yellow_cards'];

//    protected $hidden = ['data'];

    public function getRatingAttribute() {
        return isset($this->stats) && isset($this->stats->rating) ? (float)$this->stats->rating : null;
    }

    public function getGoalsAttribute() {
        return isset($this->stats) && isset($this->stats->goals) ? (float)$this->stats->goals : null;
    }

    public function getAssistsAttribute() {
        return isset($this->stats) && isset($this->stats->assists) ? (float)$this->stats->assists : null;
    }

    public function getRedCardsAttribute() {
        return isset($this->stats) && isset($this->stats->redcards) ? $this->stats->redcards : null;
    }

    public function getYellowCardsAttribute() {
        return isset($this->stats) && isset($this->stats->yellowcards) ? $this->stats->yellowcards : null;
    }

    public function getCardsAttribute() {
        return $this->red_cards + $this->yellow_cards;
    }

     public function team()
    {
        return $this->hasOne('App\Models\ApiTeams', 'team_id', 'team_id');
    }

    public static function createPlayerFromApi($playerId) {
         return app('App\Http\Controllers\Cron\ApiController')->getPlayer($playerId);
    }

    public function getStatsAttribute() {
         return is_object($this->data) ? $this->data : json_decode($this->data);
    }

    public function getArticles($limit = 30){
        $id = $this->player_id;
        $articles = \App\Article::with('mainGalleryItem', 'author', 'categories', 'matche')->Where('id','>',0)->whereIn('id',function ($q)use($id){
            $q->select('article_id')->from('article_player')->where('player_id',$id);
        })->orderBy('id','desc')->paginate($limit);


        return $articles;
    }

    public function getDataAsObjectAttribute() {
        return json_decode($this->data);
    }

    public function getCountryFlagAttribute() {
         $countryId = isset($this->data_as_object)
         && isset($this->data_as_object->player)
         && isset($this->data_as_object->player->data) ? $this->data_as_object->player->data->country_id : 'no-flag';

         if($countryId == 'no-flag') $countryId = isset($this->data_as_object->country_id) ? $this->data_as_object->country_id : 'no-flag';

        $png = env('STORAGE_PATH').'/images/countries/flags/'.$countryId.'.png';
        if(!file_exists($png)) {
            $link=env('STORAGE_PATH').'/images/countries/flags/'.$countryId.'.svg';
            $file = basename($link);
            $file = basename($link, ".svg");
            $im = new \Imagick();

            try {
                $svg = file_get_contents($link);
                $im->readImageBlob($svg);
                $im->resizeImage(256, 256, \Imagick::FILTER_CATROM, 1);
                $im->setImageFormat("png24");
                $im->writeImage($png);
            } catch (\Exception $e) {
                $countryId = 'no-flag';
            }
            $im->clear();
            $im->destroy();
        }

        return 'https:'.env('STORAGE_URL').'/images/countries/flags/'.$countryId.'.png';
    }

    public function getShareLink() {
         return 'https://goal.ge/player/'.$this->player_id.'/'.$this->slug.'/1';
    }

    public function getShortInfo() {
        return [
            'player_id' => $this->player_id,
            'common_name' => $this->common_name,
            'fullname' => $this->fullname,
            'display_name' => json_decode($this->data)->player->data->display_name,
            'image_path' => $this->image_path,
            'link' => $this->getShareLink(),
        ];
    }
}
