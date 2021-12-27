<?php

namespace App;

use App\Models\ApiLeagues;
use App\Models\ApiMatches;
use App\Models\ApiTeams;
use App\Models\ApiTeamsPlayers;
use App\Models\ArticleAnchor;
use App\Models\NewVideos;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Mimrahe\StripTags\Stripper;

class Article extends Model
{
     protected $guarded = [];

    protected function setPrimaryKey($key)
    {
      $this->primaryKey = $key;
    }

    // protected $hidden = ['pivot'];

    protected $appends = ['linked_match_score', 'linked_news', 'plain_content', 'main_video_url', 'comments_count', 'liked_by', 'link', 'has_embed'];

    public function getContentAttribute() {
        return isset($this->attributes['content']) ? html_entity_decode($this->attributes['content']) : '';
    }

    public function getHasEmbedAttribute() {
        $array = ['img', 'a', 'iframe'];

        $doc = $this->getDom();
        $selector = new DOMXPath($doc);
        foreach($selector->query('//div[contains(attribute::class, "linked-news")]') as $e ) {
            $e->parentNode->removeChild($e);
        }

        foreach ($array as $needle) {
//            if($_SERVER['REMOTE_ADDR'] == '178.134.149.30') dd($selector->query('//'.$needle));

            if($selector->query('//'.$needle)->length > 0) return true;
        }

        return false;
    }

    public function getDom() {
        $content = str_replace('&', '&amp;', $this->content);
        $doc = new DOMDocument();
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$content);

        return $doc;
    }

    public function getPlainContentAttribute() {
        $doc = $this->getDom();
        $selector = new DOMXPath($doc);
        foreach($selector->query('//div[contains(attribute::class, "linked-news")]') as $e ) {
            $e->parentNode->removeChild($e);
        }

        $content = str_replace("\n\n\n\n", "\n", $doc->textContent);
        return $content;
    }

    public function getLikedByAttribute() {
        return $this->likes()->select(['user_id'])->get()->pluck('user_id')->toArray();
    }

    public function likes() {
        return $this->hasMany(ArticleLike::class, 'article_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag', 'article_id', 'tag_id');
    }

    public function comments() {
        return $this->hasMany(Comment::class, 'article_id');
    }

    public function teams()
    {
        return $this->belongsToMany(ApiTeams::class, 'article_team', 'article_id', 'team_id');
    }

    public function matche()
    {
        return $this->hasOne( '\App\Models\ApiMatches', 'match_id', 'match_id')
        ->addSelect(['match_id', 'time_status', 'starting_at', 'league_id',
        'localteam_score', 'visitorteam_score', 'localteam_id','visitorteam_id'])
        ->with(['localteam_mini', 'league', 'visitorteam_mini']);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'article_category', 'article_id', 'category_id')
            ->defaultOrder();
    }

    public function getCommentsCountAttribute() {
        return $this->comments()->count();
    }

    public function categories_first()
    {
        $rs = $this->belongsToMany(Category::class, 'article_category', 'article_id', 'category_id')
            ->defaultOrder();
        return $rs;
    }

    public function players_sync($players){
        // die('123');
        if(empty($players)){
            \App\Models\ArticlePlayer::where('article_id',$this->id)->delete();
            return false;
        }
        $players = explode(',', $players);

        // print_r($players);

        $o_players = $this->getPlayers(true);
        $old_players = [];

        foreach ($o_players as $plr){
            $old_players[$plr->player_id] = $plr;
        }

        foreach ($players as $pl){
            if(isset($old_players[$pl])){
                unset($old_players[$pl]);

                continue;
            }

            \App\Models\ArticlePlayer::create([
                'article_id'=>$this->id,
                'player_id'=>$pl,
            ]);

        }

        $old_players = array_keys($old_players);

        \App\Models\ArticlePlayer::whereIn('player_id', $old_players)->where('article_id',$this->id)->delete();
    }

    public function players() {
        return $this->belongsToMany(ApiTeamsPlayers::class, 'article_player', 'article_id', 'player_id');
    }

    public function getPlayers( $onlyrelation= false, $onlyids = false){
        $players = \App\Models\ArticlePlayer::where('article_id',$this->id);

        if($players->count() == 0){ return []; }

        $players = $players->get();

        if($onlyrelation) {
            return $players;
        }

        $players_ids = [];

        foreach ($players as $pl) {
            $players_ids[] = $pl->player_id;
        }

        if ($onlyids) {
            return $players_ids;
        }

        $players_data = \App\Models\ApiTeamsPlayers::whereIn('player_id', $players_ids)
            ->select(['player_id', 'common_name', 'nationality', 'image_path', 'slug'])
            ->get();

        return $players_data;
    }

    public function leagues() {
        return $this->belongsToMany(ApiLeagues::class, 'article_league', 'article_id', 'league_id');
    }

    public function leagues_sync($leagues){
        // die('123');
        if(empty($leagues)){
            \App\Models\ArticleLeague::where('article_id',$this->id)->delete();
            return false;
        }
        $leagues = explode(',', $leagues);

        // print_r($players);

        $o_leagues = $this->getLeagues(true);
        $old_leagues = [];

        foreach ($o_leagues as $plr){
            $old_leagues[$plr->league_id] = $plr;
        }

        foreach ($leagues as $pl){
            if(isset($old_leagues[$pl])){
                unset($old_leagues[$pl]);

                continue;
            }

            \App\Models\ArticleLeague::create([
                'article_id'=>$this->id,
                'league_id'=>$pl,
            ]);

        }

        $old_leagues = array_keys($old_leagues);

        \App\Models\ArticleLeague::whereIn('league_id', $old_leagues)->where('article_id',$this->id)->delete();

    }

    public function getLeagues( $onlyrelation= false, $onlyids = false){
        $leagues = \App\Models\ArticleLeague::where('article_id',$this->id);

        if($leagues->count() == 0){ return []; }

        $leagues = $leagues->get();

        if($onlyrelation) {
            return $leagues;
        }

        $leagues_ids = [];

        foreach ($leagues as $pl) {
            $leagues_ids[] = $pl->league_id;
        }

        if ($onlyids) {
            return $leagues_ids;
        }

        $leagues_data = \App\Models\ApiLeagues::whereIn('league_id', $leagues_ids)->get();

        return $leagues_data;


    }


    public function teams_sync($teams){
        // die('123');
        if(empty($teams)){
            \App\Models\ArticleTeam::where('article_id',$this->id)->delete();
            return false;
        }
        $teams = explode(',', $teams);

        // print_r($players);

        $o_teams = $this->getTeams(true);
        $old_teams = [];

        foreach ($o_teams as $plr){
            $old_teams[$plr->team_id] = $plr;
        }

        foreach ($teams as $pl){
            if(isset($old_teams[$pl])){
                unset($old_teams[$pl]);

                continue;
            }

            \App\Models\ArticleTeam::create([
                'article_id'=>$this->id,
                'team_id'=>$pl,
            ]);

        }

        $old_teams = array_keys($old_teams);

        \App\Models\ArticleTeam::whereIn('team_id', $old_teams)->where('article_id',$this->id)->delete();

    }

 public function getTeams( $onlyrelation= false, $onlyids = false){
        $teams = \App\Models\ArticleTeam::where('article_id',$this->id)->whereNotIn('team_id', [30830, 234948, 6199, 239693, 472, 236848, 976]);

        if($teams->count() == 0){ return []; }

        $teams = $teams->get();

        if($onlyrelation) {
            return $teams;
        }

        $team_ids = [];

        // print_r($teams);die;

        foreach ($teams as $pl) {
            $team_ids[] = $pl->team_id;
        }

        if ($onlyids) {
            return $team_ids;
        }

        $teams_data = \App\Models\ApiTeams::whereIn('team_id', $team_ids)->get();

        return $teams_data;


    }

    public function getTagged() {
        $data = [];

        foreach ($this->getTeams() as $team) {
            $teamData = [
                'id' => $team->team_id,
                'link' => $team->getShareLink(),
                'name' => $team->name,
                'type' => 'team',
                'image' => $team->logo_path
            ];

            $data[] = $teamData;
        }

        foreach ($this->getPlayers() as $player) {
            $playerData = [
                'id' => $player->player_id,
                'link' => $player->getShareLink(),
                'name' => $player->common_name,
                'type' => 'player',
                'image' => $player->image_path
            ];

            $data[] = $playerData;
        }

        foreach ($this->getLeagues() as $league) {
            $playerData = [
                'id' => $league->league_id,
                'link' => $league->getShareLink(),
                'name' => $league->name,
                'type' => 'league',
                'image' => $league->icon
            ];

            $data[] = $playerData;
        }

        return $data;
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mainGalleryItem()
    {
        return $this->hasOne(GalleryItem::class, 'id', 'main_gallery_item_id')->select(['id','filename','filename_webp','image_x','image_y','filename_preview']);
    }


    public function views()
    {
        return $this->hasMany(ArticleView::class, 'id', 'article_id');
    }

    public static function addWatermark($image, $position) {
        if($position == 'hidden') return $image;

        $waterMarkUrl = public_path('images/watermark.png');

        if($image && ($position == 'top' || $position == 'bottom')) {
            $image->insert($waterMarkUrl, $position.'-center', 5, 5);
        }

        return $image;
    }

    public static function addWatermarkToPath($path, $storage, $position) {
        $image = Storage::disk($storage)->get($path);
        $image = self::addWatermark(Image::make($image), $position);

        return $image;
    }

    public function hashtags() {
        return $this->belongsToMany(Hashtag::class, 'article_hashtags');
    }

    public function getHashtags() {
        return $this->hashtags()->get()->pluck('hashtag')->toArray();
    }

    public function saveHashtags($hashtags) {
        if(!$hashtags) return;

        $this->hashtags()->sync([]);
        foreach ($hashtags as $hashtagString) {
            $hashtag = Hashtag::firstOrCreate(['hashtag' => $hashtagString]);
            $this->hashtags()->attach($hashtag);
        }
    }

    /*public function getContentForMobileAttribute() {
        $stripTags = new Stripper($this->content);

        $stripedText = $stripTags->except([
            'a', 'pre', 'p', 'br', 'h1', 'h2', 'h3', 'h4', 'h5',
            'li', 'ol', 'ul', 'iframe'
        ])->strip();
    }*/

    public function getShareLink() {
        return 'https://goal.ge/news/'.$this->id.'/'.$this->slug;
    }

    public function getLinkAttribute() {
        return $this->getShareLink();
    }

    public function getLinkedNewsAttribute() {
        try {
            $doc = new \DOMDocument('1.0', 'UTF-8');
            $internalErrors = libxml_use_internal_errors(true);
            $doc->loadHTML('<?xml encoding="UTF-8">'. $this->content);
            libxml_use_internal_errors($internalErrors);
            $xpath = new DOMXPath($doc);
            $nodeList = $xpath->query('//a');
            $links = [];
            for ($i = 0; $i < $nodeList->length; $i++) {
                $img = $nodeList->item($i)->firstChild;
                if(get_class($img) != 'DOMText') {
                    $imgSrc = $img->getAttribute('src');
                } else {
                    $imgSrc = null;
                }
                $text = $nodeList->item($i)->nodeValue;
                $link = $nodeList->item($i)->attributes[0]->nodeValue;
                $idExp = explode('/news/', $link);

                if(count($idExp) > 1) {
                    $idExp2 = explode('/', $idExp[1]);
                    $id = $idExp2[0];
                    $links[] = [
                        'id' => $id,
                        'link' => $link,
                        'text' => $text,
                        'img' => $imgSrc ?? null,
                    ];
                }
            }
            return $links;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getLinkedMatchScoreAttribute() {
        $match = ApiMatches::where('match_id', $this->match_id)->first();

        if(!$match) return null;

        return $match->getShortData();
    }

    public function getMainVideoUrlAttribute() {
        try {
            if(strpos($this->main_video, 'iframe') !== false) {
                $doc = new DOMDocument();
                $doc->loadHTML($this->main_video);
                $iframes = $doc->getElementsByTagName('iframe');

                foreach($iframes as $tag) {
                    return $tag->getAttribute('src');
                }
            } elseif(strpos($this->main_video, 'type="video"') !== false) {
                preg_match('/".*?"/', str_replace('type="video"', '', $this->main_video), $matches);

                if(count($matches) == 0) return null;

                $videoId = str_replace('"', '', $matches[0]);
                $video = NewVideos::find($videoId);

                if(strpos($video->video_url, 'http://') !== false || strpos($video->video_url, 'https://') !== false)
                    return $video->video_url;

                return 'https:'.env('STORAGE_URL').$video->video_url;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function clearCategoryCache() {
        foreach ($this->categories()->get()->pluck('id') as $cat) {
            Cache::tags(['category-'.$cat])->flush();
        }
    }

    public function clearTeamCache() {
        foreach ($this->teams()->get()->pluck('team_id') as $teamId) {
            Cache::tags(['team-'.$teamId])->flush();
        }
    }

    public function clearLeagueCache() {
        foreach ($this->leagues()->get()->pluck('league_id') as $leagueId) {
            Cache::tags(['league-'.$leagueId])->flush();
        }
    }
}
