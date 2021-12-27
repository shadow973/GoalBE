<?php

namespace App;

use App\Models\ApiTeamsPlayers;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use File;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'phone',
        'birth_date',
        'fav_league',
        'fav_team',
        'avatar',
        'registration_uid',
        'activated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'registration_uid',
        'activated_at',
    ];

    public function delete()
    {
        $this->deleteAvatar();
        parent::delete();
    }

    public function userrole(){
        return $this->hasOne('App\RoleUser', 'user_id', 'id');
    }

    public function deleteAvatar()
    {
        File::delete(public_path() . '/images/avatars/' . $this->avatar);
    }

    public function categorySubscriptions()
    {
        return $this->belongsToMany(Category::class, 'category_subscriptions', 'user_id', 'category_id');
    }

    public function playerSubscriptions()
    {
        $user_id = $this->id;

        $players = \App\Models\ApiTeamsPlayers::whereIn('player_id',function ($q) use ($user_id){
            $q->select('player_id')->from('player_subscriptions')->where('user_id',$user_id);
        });

        return $players->get();
    }

    public function tagSubscriptions()
    {
        return $this->belongsToMany(Tag::class, 'tag_subscriptions', 'user_id', 'tag_id');
    }

    public function teamSubscriptions()
    {
        $user_id = $this->id;

        $teams = \App\Models\ApiTeams::whereIn('team_id',function ($q) use ($user_id){
            $q->select('team_id')->from('team_subscriptions')->where('user_id',$user_id);
        });

        return $teams->get();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'user_id');
    }

    public function club()
    {
        return $this->hasOne('App\Models\ApiTeams', 'team_id', 'fav_team_id');
    }

    public function articlesWithOnlyDates()
    {
        return $this->hasMany(Article::class, 'user_id')
            ->select([
                'id',
                'title',
                'views',
                'is_blog_post',
                'is_published',
                'main_gallery_item_id',
                'user_id',
                'publish_date',
                'created_at',
                'updated_at',
            ]);
    }

    public function articleViews()
    {
        return $this->hasManyThrough(ArticleView::class, Article::class, 'user_id', 'article_id', 'id');
    }

    public function favorites() {
        return $this->hasOne(UserFavorite::class, 'user_id');
    }
}
