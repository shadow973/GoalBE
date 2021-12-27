<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Models\ApiTeams;
use App\Models\ApiTeamsPlayers;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Intervention;
use JWTAuth;
use App\Category;
use App\Tag;
use App\Article;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Storage;

use App\Models\TeamUser;

class UsersController extends Controller
{
    protected $user;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        try {
            $this->user = JWTAuth::parseToken()->toUser();
        } catch (\Exception $e) {
        }
    }

    /**
     * @return mixed
     */

    public function index(Request $request){
        $users = User::orderBy('created_at', 'desc')
                        ->withCount('tagSubscriptions', 'categorySubscriptions');

        if ($request->has('q')) {
            $users->where('first_name', 'LIKE', "%{$request->get('q')}%")
                ->orWhere('last_name', 'LIKE', "%{$request->get('q')}%")
                ->orWhere('username', 'LIKE', "%{$request->get('q')}%")
                ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%{$request->get('q')}%"]);
        }

        if ($request->has('role_id')) {
            $users->whereHas('roles', function ($q) use ($request) {
                $q->where('id', $request->get('role_id'));
            });
        }

        if ($request->has('select2')) {
            return [
                'items' => $users->get()
            ];
        }

        return $users
            ->withCount('tagSubscriptions', 'categorySubscriptions')
            ->get();
    }

    public function getStats(Request $request)
    {
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->hasRole('journalist') && !$this->user->hasRole('admin') && !$this->user->hasRole('content_manager')){
            abort(403);
        }

        $this->validate($request, [
            'date_from' => 'required|date',
            'date_till' => 'required|date',
        ]);

        $dateFrom = $request->get('date_from');
        $dateTill = Carbon::parse($request->get('date_till'))->addDays(1);

        if ($dateFrom !== $dateTill) {
            $users = User::whereHas('roles', function ($query) {
                $query->where('name', 'journalist');
            })->withCount(['articles' => function($query) use ($dateFrom, $dateTill) {
                $query->whereBetween('created_at', [$dateFrom, $dateTill]);
            }])->withCount(['articleViews' => function ($query) use ($dateFrom, $dateTill) {
                $query->whereBetween('datetime', [$dateFrom, $dateTill]);
            }])->get();
        } else {
            $users = User::whereHas('roles', function ($query) {
                $query->where('name', 'journalist');
            })->withCount(['articles' => function($query) use ($dateFrom, $dateTill) {
                $query->whereRaw('DATE(created_at) = ? ', [$dateFrom]);
            }])->withCount(['articleViews' => function ($query) use ($dateFrom) {
                $query->whereRaw('DATE(datetime) = ? ', [$dateFrom]);
            }])->get();
        }

        $stats = [];

        foreach ($users as $user) {
            $stats[] = [
                'name' => trim($user->first_name . ' ' . $user->last_name . ' (' . $user->username . ')'),
                'article_count' => $user->articles_count,
                'view_count' => $user->article_views_count,
            ];
        }

        return $stats;
    }

    public function getArticles($userId)
    {
        return Article::orderBy('created_at', 'desc')
            ->with('mainGalleryItem')
            ->where('user_id', $userId)
            ->where('is_published', true)
            ->paginate(15);
    }

    public function getComments($userId)
    {
        return Comment::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->with('author', 'replies.author')
            ->get();
    }

    /**
     * @param Request $request
     * @return User
     */
    public function store(Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        $this->validate($request, [
            'first_name' => '',
            'last_name' => '',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'phone' => '',
            'birth_date' => 'date',
            'fav_league' => '',
            'fav_team' => '',
            'avatar' => '',
            'role_id' => 'required',
        ]);

        $user = new User($request->all());

        if ($request->has('avatar')) {
            $image = Intervention::make($request->get('avatar'));
            $imageName = str_random(32);

            $image = $image->fit(128, 128)
                ->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/avatars/' . $imageName . '.png',
                (string)$image,
                'public'
            );

            $user->avatar = 'images/avatars/' . $imageName . '.png';
        }

        $user->password = bcrypt($user->password);
        $user->save();

        $role = Role::findOrFail($request->get('role_id'));
        $user->roles()->sync([$role->id]);

        return User::find($user->id);
    }

    public function getAuthenticated()
    {
        if(!$this->user) {
            return abort(401, 'Token has expired.');
        }

        $this->user->subscribed_category_ids = array_column($this->user->categorySubscriptions->toArray(), 'id');
        $this->user->subscribed_tag_ids = array_column($this->user->tagSubscriptions->toArray(), 'id');
        $this->user->teamSubscriptions = $this->user->teamSubscriptions();
        $this->user->playerSubscriptions = $this->user->playerSubscriptions();

        $this->user->load(['roles.perms', 'club']);

        // if(!empty($this->user->fav_team_id)){
        //     $this->user->fav_club_img =  $this->user->club->tag->image;
        // }else{
        //     $this->user->fav_club_img =  false;
        // }

        $this->user->fav_club_img =  false;

        // dd($this->user);

        return $this->user;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);
        $user->article_count = Article::where('user_id', $userId)->count();
        $user->subscription_count = $user->categorySubscriptions()->count() + $user->tagSubscriptions()->count();
        $user->comment_count = $user->comments()->count();
        $user->role_id = count($user->roles) ? $user->roles()->first()->id : null;

        return $user;
    }

    /**
     * @param $userId
     * @param Request $request
     * @return mixed
     */
    public function update($userId, Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        $this->validate($request, [
            'first_name' => '',
            'last_name' => '',
            'email' => "email|unique:users,email,$userId",
            'username' => "unique:users,username,$userId",
            'password' => '',
            'confirm_password' => 'same:password',
            'phone' => '',
            // 'birth_date' => 'date',
            'fav_league' => '',
            'fav_team' => '',
            'avatar_new' => ''
        ]);

        $user = User::findOrFail($userId);
        $data = $request->all();

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }else{
            unset($data['password']);
        }

        if (!isset($data['avatar_new'])) {
            unset($data['avatar']);
        }

        if (isset($data['avatar_new'])) {
            $image = Intervention::make($data['avatar_new']);
            $imageName = str_random(32);

            $image = $image->fit(128, 128)
                ->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/avatars/' . $imageName . '.png',
                (string)$image,
                'public'
            );

            $data['avatar'] = 'images/avatars/' . $imageName . '.png';

            $user->deleteAvatar();
        }


        $user->update($data);

        $role = Role::findOrFail($request->get('role_id'));
        $user->roles()->sync([$role->id]);

        return User::find($userId); //cause $user object still contains old, pre-update data
    }

    public function updateAuthenticated(Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        $this->validate($request, [
            'first_name' => '',
            'last_name' => '',
            'email' => "email|unique:users,email,{$this->user->id}",
            'username' => "unique:users,username,{$this->user->id}",
            'password' => '',
            'confirm_password' => 'same:password',
            'phone' => '',
            'birth_date' => 'nullable|date',
            'fav_league' => '',
            'fav_team' => '',
            'avatar_new' => ''
        ]);

        $data = $request->all();

        // print_r($data);
        // die;

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }else{
          unset( $data['password'] );
        }

        if (!isset($data['avatar_new'])) {
            unset($data['avatar']);
        }


        if (isset($data['avatar_new'])) {
            // die('zebra');
            $image = Intervention::make($data['avatar_new']);
            $imageName = str_random(32);

            $image = $image->fit(128, 128)
                ->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/avatars/' . $imageName . '.png',
                (string)$image,
                'public'
            );

            $data['avatar'] = 'images/avatars/' . $imageName . '.png';

            $this->user->deleteAvatar();
        }

        // print_r($data);
        // die;

        // print_r($this->user->toArray());
        // die;

        if(isset($data['fav_team_id'])){
            $this->user->fav_team_id = $data['fav_team_id'];
            $this->user->save();
        }

        $this->user->update($data);


        $this->user = User::find($this->user->id);

        $this->user->subscribed_category_ids = array_column($this->user->categorySubscriptions->toArray(), 'id');
        $this->user->subscribed_tag_ids = array_column($this->user->tagSubscriptions->toArray(), 'id');

        return $this->user;
    }

    public function changePassword(Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required',
            'confirm_password' => 'same:password',
        ], [
            'current_password.required' => 'გთხოვთ მიუთითოთ მიმდინარე პაროლი',
            'password.required' => 'გთხოვთ მიუთითოთ ახალი პაროლი',
            'confirm_password.same' => 'დადასტურებული პაროლი არ ემთხვევას'
        ]);

        $data = $request->all();

        if(!Hash::check($data['current_password'], $this->user->password))
            return response()->json([
                'error' => 'მიმდინარე პაროლი არასწორია'
            ], 422);


        // print_r($data);
        // die;

        $data['password'] = bcrypt($data['password']);

        $this->user->update($data);

        return response()->json([
            'message' => 'პაროლი წარმატებით შეიცვალა'
        ], 200);
    }

    /**
     * @param $userId
     */
    public function destroy($userId)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->hasRole('admin')) {
            abort(403);
        }

        $user = User::findOrFail($userId);
        $user->delete();
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users,email',
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);


        $user = new User($request->only([
            'email', 'username'
        ]));
        $user->password = bcrypt($request->get('password'));
//        $user->registration_uid = str_random(32);
        $user->save();

        $role = Role::where('name', 'user')->first();
        $user->attachRole($role);

//        Mail::to($user)->send(new Registration($user));

        return response()->json([
            'message' => 'You have successfully registered'
        ]);
    }

    public function activateUser(Request $request) {
        $this->validate($request, [
            'uid' => 'required'
        ]);

        $user = User::where('registration_uid', $request->get('uid'))
            ->firstOrFail();

        if ($user->activated_at) {
            return response()
                ->json(['error' => 'Профиль активирован.'], 400);
        }

        $user->activated_at = new \DateTime();
        $user->save();

        return response()
            ->json(['activated' => true]);
    }

    /*
     * Also handles subscription removal
     */
    public function subscribeToCategory($categoryId)
    {
        if (!$this->user) {
            abort(401);
        }

        $category = Category::findOrFail($categoryId);

        $userCategorySubscriptions = array_column($this->user->categorySubscriptions->toArray(), 'id');

        if (in_array($categoryId, $userCategorySubscriptions)) {
            $this->user->categorySubscriptions()->detach($categoryId);
            return response()
                ->json([
                    'subscribed' => false,
                ]);
        } else {
            $this->user->categorySubscriptions()->attach($categoryId);
            return response()
                ->json([
                    'subscribed' => true,
                ]);
        }
    }

    public function subscribeToPlayer($id) {
        if (!$this->user) {
            abort(401);
        }

        $player_id = (int)$id;

        if($player_id == 0){
            return ['subscribed' => false];
        }

        $team = \App\Models\ApiTeamsPlayers::where('player_id',$player_id);
        if($team->count() == 0){
            return ['subscribed' => false];
        }

        $tu = DB::table('player_subscriptions')->where('user_id', $this->user->id)->where('player_id', $player_id);
        if($tu->count() > 0){
            $tu->delete();
            return ['subscribed' => false];
        }

        DB::table('player_subscriptions')->insert([
            'user_id' => $this->user->id,
            'player_id' => $player_id
        ]);
        return ['subscribed' => true];
    }

    public function subscribeToTeam($id){
        if (!$this->user) {
            abort(401);
        }

        $team_id = (int)$id;

        if($team_id == 0){
            return ['subscribed' => false];
        }

        $team = \App\Models\ApiTeams::where('team_id',$team_id);
        if($team->count() == 0){
            return ['subscribed' => false];
        }

        $tu = TeamUser::where('user_id',$this->user->id)->where('team_id',$team_id);
        if($tu->count() > 0){
            $tu->delete();
            return ['subscribed' => false];
        }

        $tu = new TeamUser;
        $tu->team_id = $team_id;
        $tu->user_id = $this->user->id;
        $tu->save();

        return ['subscribed' => true];

    }

    public function subscribeToTag($tagId)
    {
        if (!$this->user) {
            abort(401);
        }

        $tag = Tag::findOrFail($tagId);

        $userTagSubscriptions = array_column($this->user->tagSubscriptions->toArray(), 'id');

        if (in_array($tagId, $userTagSubscriptions)) {
            $this->user->tagSubscriptions()->detach($tagId);
            return response()
                ->json([
                    'subscribed' => false,
                ]); 
        } else {
            $this->user->tagSubscriptions()->attach($tagId);
            return response()
                ->json([
                    'subscribed' => true,
                ]);
        }
    }

    public function mySubscriptions()
    {
        if(!$this->user){
            abort(401);
        }

        if($this->request->search) {
            $search = $this->request->search;
            $userId = $this->user->id;

            $players = ApiTeamsPlayers::leftJoin('player_subscriptions', function($j) use ($userId) {
                $j->on('player_subscriptions.player_id', '=', 'api_teams_players.player_id');
                    $j->where('player_subscriptions.user_id', '=', $userId);
                })
                ->where('common_name', 'like', '%'.$search.'%')
                ->orWhere('fullname', 'like', '%'.$search.'%')
                ->selectRaw('api_teams_players.player_id as id, api_teams_players.common_name as name, api_teams_players.image_path as image, \'player\' as type, 
                if(player_subscriptions.user_id is null, 0, 1) as is_subscribed');

            $teams = ApiTeams::leftJoin('team_subscriptions', function($j) use ($userId) {
                    $j->on('team_subscriptions.team_id', '=', 'api_teams.team_id');
                    $j->where('team_subscriptions.user_id', '=', $userId);
                })
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('name_geo', 'like', '%'.$search.'%')
                ->selectRaw('api_teams.team_id as id, if(length(name_geo) > 0, name_geo, api_teams.name) as name, api_teams.logo_path as image, \'team\' as type, 
                if(team_subscriptions.user_id is null, 0, 1) as is_subscribed');;


            return $teams->union($players)->get();
        }

        $data = [];
        foreach ($this->user->playerSubscriptions() as $playerSubscription) {
            $data[] = [
                'id' => $playerSubscription->player_id,
                'name' => $playerSubscription->common_name,
                'image' => $playerSubscription->image_path,
                'type' => 'player',
                'is_subscribed' => true,
            ];
        }

        foreach ($this->user->teamSubscriptions() as $teamSubscription) {
            $data[] = [
                'id' => $teamSubscription->team_id,
                'name' => $teamSubscription->name,
                'image' => $teamSubscription->logo_path,
                'type' => 'team',
                'is_subscribed' => true,
            ];
        }

        return $data;
    }

    public function getSubscriptions($userId)
    {
        $user = User::findOrFail($userId);
        $subscriptions['categories'] = $user->categorySubscriptions;
        $subscriptions['tags'] = $user->tagSubscriptions;

        return $subscriptions;
    }

    public function getStatistics(){

        if(!$this->user){
            abort(401);
        }

        if(!$this->user->hasRole('journalist') && !$this->user->hasRole('admin') && !$this->user->hasRole('content_manager')){
            abort(403);
        }

        $countTotal = User::count();
        $countToday = User::whereDate('created_at', '=', Carbon::today()->format('Y-m-d'))->count();
        $start = Carbon::parse('2017-09-15 00:00:00');
        $diff = $start->diffInDays(Carbon::now());
        $countAverage = round($countTotal / $diff, 0);

        return [
            'total' => $countTotal,
            'today' => $countToday,
            'average' => $countAverage
        ];
    }

    public function export(Request $request)
    {
        dd($this->user);
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->hasRole('journalist') && !$this->user->hasRole('admin') && !$this->user->hasRole('content_manager')){
            abort(403);
        }

        $users = User::select('username', 'email')
            ->withCount('tagSubscriptions', 'categorySubscriptions')
            ->get();

        Excel::create('users', function ($excel) use ($users) {
            $excel->sheet('Sheet 1', function ($sheet) use ($users) {
                $sheet->fromArray($users);
            });
        })->export('xls', [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
