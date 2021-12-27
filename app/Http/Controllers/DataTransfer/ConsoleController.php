<?php

namespace App\Http\Controllers\DataTransfer;

use App\Category;
use App\RoleUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Tag;
use App\User;
use App\Models\ApiLeagues;
use App\Models\ApiTeams;
use App\Models\ApiTeamsPlayers;
use App\Models\Redis;


class ConsoleController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function test(){
        $old = DB::connection('mysql_old');

        $post = $old->table('dle_post')->limit(1)->get()->toArray();

        print_r($post);
        echo PHP_EOL;
    }

    public function transferCategories(){
        $old = DB::connection('mysql_old');

        $res = $old->table('dle_category')->get()->toArray();
//        ->limit(4)

        $exch_ids = [];
        foreach ($res as $v){
            $new = new \App\Category();

            $new->title = $v->name;
            $new->is_visible = 1;
            $new->save();

            $exch_ids[$v->id] = $new->id;
        }


        file_put_contents('cat_ids.json',json_encode( $exch_ids ));
    }


    public function transferUsers(){

        $old = DB::connection('mysql_old');

        $res = $old->table('dle_users')->get()->toArray();
//        ->limit(10)
        $exch_ids = [];
        foreach ($res as $v){
            $data = [];

            $check = \App\User::where('email',$v->email);
            if($check->count() > 0){
                echo $v->email." -- already exists".PHP_EOL;
                continue;
            }
            $fullname = explode(' ', $v->fullname);

            $data['email'] = $v->email;
            $data['username'] = $v->email;
            $data['avatar'] = str_replace('http://goal.ge','old',$v->foto);
            $data['first_name'] = isset($fullname[0])?$fullname[0]:'';
            $data['last_name'] = isset($fullname[1])?$fullname[1]:'';
//            $new->userrole()->create(['user_id' => $new->id,'role_id' => 1]);

            $user = new User($data);
            $user->password = $v->password;
            $user->save();
            $user->roles()->sync([1]);
            $exch_ids[$v->user_id] = $user->id;

            echo $v->email.' - '.$v->user_id.' - '.$user->id.PHP_EOL;

//            \App\RoleUser::create(['user_id' => $new->id,'role_id' => 1]);
        }

        file_put_contents('user_ids.json',json_encode( $exch_ids ));

    }

    public function xparse($f){
        $rs = [];
        $xf = explode('||',$f);

        if(count($xf) > 0){
            foreach ($xf as $v){
                $xff = explode('|',$v);
                $rs[$xff[0]] = isset($xff[1])?$xff[1]:'';
            }
        }
        return $rs;
    }

    public function transferPost(){

        $max_id = \App\Article::select(DB::raw('max(old_id) as max'))->where('id','>',0)->get();
        $max_id = empty($max_id[0]->max)?0:$max_id[0]->max;

        $old = DB::connection('mysql_old');
        $post = $old->table('dle_post')
            ->where('id','>', $max_id)
//            ->orderBy('id','desc')
            ->limit(100000)
            ->get();
//            ->toArray();

        foreach ($post as $v){
            $cat_ids = array_filter(explode(',',$v->category));
            $xfield = $this->xparse($v->xfields);
            $meta = [];

            $img = null;
            if(isset($xfield['image'])){
                $img = $xfield['image'];
            }elseif(isset($xfield['imagenew'])){
                $img = $xfield['imagenew'];
            }elseif(isset($xfield['imagenew'])){
                $img = $xfield['imagenew'];
            }

            $img_id = null;
            // print_r($img);
            if(!empty($img)){
                $img = str_replace('http://goal.ge/', '', $img);

                $gi = new \App\GalleryItem();
                $gi->type = 'image';
                $gi->filename = substr($img, 0, 230);
                $gi->filename_preview = substr($img, 0, 230) ;
                $gi->show_in_video_gallery = 0;
                $gi->save();
                $img_id = $gi->id;
            }

            


            $meta['xfields'] = $xfield;
            $art = new \App\Article();
            $art -> title = $v->title;
            $art -> slug = url_slug($v->title,['transliterate' => true]);
            $art -> content = $v->full_story;
            $art -> meta = json_encode($meta);
            $art -> created_at = $v->date;
            $art -> updated_at = $v->date;
            $art -> publish_date = $v->date;
            $art -> main_gallery_item_id = $img_id;
            $art -> old_id = $v->id;
            $art -> user_id = 4;
            $art ->save();
            // print_r($cat_ids);

            $ctags = \App\Category::select('id','title')->whereIn('id',$cat_ids)->get();

            $checked_ids = [];
            $tag_titles = [];
            if(!empty($ctags)){
                foreach ($ctags as $t){
                    $checked_ids[] = $t->id;
                    $tag_titles[] = $this->catExch($t->title);
                }
            }

            $art->categories()->sync($checked_ids);

            $league_ids = ApiLeagues::select('league_id')->whereIn('name',$tag_titles);

            if($league_ids->count() > 0){
                $league_ids = $league_ids->get();
                foreach ($league_ids as $lids){
                    \App\Models\ArticleLeague::create(['article_id'=>$art ->id, 'league_id'=>$lids->league_id]);
                }
            }

            $teams_ids = ApiTeams::select('team_id')->whereIn('name',$tag_titles);

            if($teams_ids->count() > 0){
                $teams_ids = $teams_ids->get();
                foreach ($teams_ids as $lids){
                    \App\Models\ArticleTeam::create(['article_id'=>$art ->id, 'team_id'=>$lids->team_id]);
                }
            }

            $player_ids = ApiTeamsPlayers::select('player_id')->whereIn('fullname',$tag_titles)->orWhereIn('common_name',$tag_titles);

            if($player_ids->count() > 0){
                $player_ids = $player_ids->get();
                foreach ($player_ids as $lids){
                    \App\Models\ArticlePlayer::create(['article_id'=>$art ->id, 'player_id'=>$lids->player_id]);
                }
            }

            echo $v->id.PHP_EOL;
        }

        Redis::ClearArticles();

//        print_r($post);
    }

    public function catExch($title){

        $cats = $this->getOldCategories();

        if(isset($cats[$title])){
            return $cats[$title];
        }else{
            return $title;
        }

    }

    public function getOldCategories(){
        $arr = [
            'ლესტერი'	=> 	'Leicester City',
            'მანჩესტერ იუნაიტედი'	=> 	'Manchester United',
            'ჩელსი'	=> 	'Chelsea',
            'ლივერპული'	=> 	'Liverpool',
            'არსენალი'	=> 	'Arsenal',
            'მანჩესტერ სიტი'	=> 	'Manchester City',
            'ტოტენჰემი'	=> 	'Tottenham Hotspur',
            'საპოვნელა'	=> '',
            'საბურთალო'	=> 	'Saburtalo',
            'ლოკომოტივი'	=> '',
            'დინამო თბილისი'	=> 	'Dinamo Tbilisi',
            'ზესტაფონი'	=> '',
            'ტორპედო ქუთაისი'	=> 	'Torpedo Kutaisi',
            'რუსთავის მეტალურგი'	=> '',
            'დილა გორი'	=> '',
            'საქართველოს ნაკრები'	=> '',
            'ჩიხურა'	=> 	'Chikhura',
            'ვიტ-ჯორჯია'	=> '',
            'შუქურა'	=> '',
            'სიონი ბოლნისი'	=> '',
            'დინამო ბათუმი'	=> 	'Dinamo Batumi',
            'ზუგდიდი'	=> '',
            'ცხინვალი'	=> '',
            'სამტრედია'	=> '',
            'მერანი მარტვილი'	=> '',
            'კოლხეთი ფოთი'	=> '',
            'გურია ლანჩხუთი'	=> '',
            'სევილია'	=> 	'Sevilla',
            'ბარსელონა'	=> 	'Barcelona',
            'რეალი'	=> 	'Real Madrid',
            'ვალენსია'	=> 	'Valencia',
            'ატლეტიკო მადრიდი'	=> 	'Atlético Madrid',
            'მალაგა'	=> 	'Málaga',
            'ატლეტიკ ბილბაო'	=> 	'Athletic Club',
            'მილანი'	=> 	'Milan',
            'იუვენტუსი'	=> 	'Juventus',
            'ინტერი'	=> 	'Inter',
            'რომა'	=> 	'Roma',
            'ნაპოლი'	=> 	'Napoli',
            'ლაციო'	=> 	'Lazio',
            'ვოლფსბურგი'	=> 	'Wolfsburg',
            'ბაიერნი'	=> 	'Bayern München',
            'ბორუსია დორტმუნდი'	=> 	'Borussia Dortmund',
            'შალკე 04'	=> 	'Schalke 04',
            'ბაიერი ლევერკუზენი'	=> 	'Bayer Leverkusen',
            'ვერდერი'	=> 	'Werder Bremen',
            'შტუტგარტი'	=> 	'Stuttgart',
            'პსჟ'	=> 	'PSG',
            'ესპანეთის ნაკრები'	=> 	'Spain',
            'ჰოლანდიის ნაკრები'	=> 	'Netherlands',
            'ბრაზილიის ნაკრები'	=> 	'Brazil',
            'იტალიის ნაკრები'	=> 	'Italy',
            'ინგლისის ნაკრები'	=> 	'England',
            'პორტუგალიის ნაკრები'	=> 	'Portugal',
            'საფრანგეთის ნაკრები'	=> 	'France',
            'არგენტინის ნაკრები'	=> 	'Argentina',

        ];

        return $arr;
    }
}
