<?php

namespace App\Http\Controllers;

use App\Role;
use App\SocialAccountService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Socialite;
use JWTAuth;

class SocialAuthController extends Controller
{

    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(SocialAccountService $service, $provider)
    {


        $token = 'EAAJC9cKqHhMBANN32imWelkmjbFsPu1KG4RmdDWPXE6F9GMjWLcYZAZBptA99q47HUnK0j95XTSgUt34rf6IRXALQQAukdwpIPR4ZC2nLxU1EMRgiAovzO00AELZCVXosdZBFFZBtdzBxDluZAzYP8w1DQzyXDgq3VF7MNiKEJtMAZDZD';
        $user = Socialite::driver('facebook')->userFromToken($token);

        dd($user);


        // Important change from previous post is that I'm now passing
        // whole driver, not only the user. So no more ->user() part

        // $req_data = [
        //     'post' => $_POST,
        //     'get' => $_GET,
        //     'json' => json_decode(file_get_contents('php://input'), true)
        // ];

        // print_r($req_data);
        // die;

        // file_put_contents('/home/goal/api.goal.ge/fblog.json', json_encode($req_data));

        // dd(Socialite::driver($provider));
        $user = $service->createOrGetUser(Socialite::driver($provider));
        auth()->login($user);

        return redirect()->to('https://dev.goal.ge');
    }

    public function socialAuthByFront(SocialAccountService $service){

        if($this->request->provider == 'facebook'){
            $token = $this->request->token;
            $socialUser = Socialite::driver('facebook')->userFromToken($token);

            $user = $service->createOrGetUser($socialUser, 'FacebookProvider');

            $token = JWTAuth::fromUser( $user );

            $user = JWTAuth::setToken($token)
                ->toUser()
                ->load('categorySubscriptions', 'tagSubscriptions');

            $user->subscribed_category_ids = array_column($user->categorySubscriptions->toArray(), 'id');
            $user->subscribed_tag_ids = array_column($user->tagSubscriptions->toArray(), 'id');
            $user->load('roles.perms');

            return response()
            ->json([
                'token' => $token,
                'user'  => $user,
            ], 200);
        }

    }

//    function http($url, $params=false) {
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        if($params)
//            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [
//            'Accept: application/json',
//            'User-Agent: curl', # Apple requires a user agent header at the token endpoint
//        ]);
//        $response = curl_exec($ch);
//        return json_decode($response);
//    }

//    public function showSignInWithAppleForm() {
//        session(['state' => bin2hex(random_bytes(5))]);
//
//        $token = 'eyJraWQiOiJlWGF1bm1MIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiZ2UuZ29hbC53ZWIiLCJleHAiOjE2Mjk1NTYwNjksImlhdCI6MTYyOTQ2OTY2OSwic3ViIjoiMDAxMTU5LjY3ODMyZDI0MWQ4NTQzOGU5MTVhNmI0N2QxYjFhNDJlLjE2MTYiLCJhdF9oYXNoIjoiTFRIRlZVR1ExaUllZFlzYkpsWl9jZyIsImVtYWlsIjoic2hvdGEubm9uaWFzaHZpbGlAZ21haWwuY29tIiwiZW1haWxfdmVyaWZpZWQiOiJ0cnVlIiwiYXV0aF90aW1lIjoxNjI5NDY5NjY3LCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.wS4SJ4m2TGiUkF3VGg0Xn4Uh8FHH8U7wPAssb0Z7g3odCemy_bGQ1IvdDmuSLhj8r61Xw1jaY6WUBoS5k61p9W6I_u2YmYpl3nd3Yx6OklVdOGxZvly0ePyT9EeNFA0HdDaPAchKg2BbDioouMZEhgfb8DvKC8iVH1rOHyh1L7qYMTNvOWZCfhpQzVxPNFIKgpTkOVEWDBDz8GiddNB8fYGdug6G8tLBpNHreremZIKGlR_5AnVuqfhXTMb9UGMv59BbfwaXtz8dKvPIvQ3yCbYUyuN15MfLQtmnnFbuolsYQgjhuR8e5eD8dAavcfYs86cPOYTOAFpOGTlJ0q6BOg';
//        $claims = explode('.', $token);
//
//        $authorize_url = 'https://appleid.apple.com/auth/authorize'.'?'.http_build_query([
//                'response_type' => 'code',
//                'response_mode' => 'form_post',
//                'client_id' => env('APPLE_CLIENT_ID'),
//                'redirect_uri' => env('APPLE_REDIRECT_URI'),
//                'state' => session('state'),
//                'scope' => 'name email',
//            ]);
//
//        return '<a href="'.$authorize_url.'">Sign In with Apple</a>';
//    }

    public function getVerificationToken() {
        $token = md5(rand());

        DB::table('apple_sign_in_tokens')->insert([
            'token' => $token
        ]);

        return ['token' => $token];
    }

    public function signInWithApple(Request $request) {
        if(!$request->identity_token) abort(403);

        $token = DB::table('apple_sign_in_tokens')->where('token', $request->token)->first();
        if(!$token) abort(403);

        DB::table('apple_sign_in_tokens')->where('id', $token->id)->delete();

        try {
            $claims = explode('.', $request->identity_token)[1];
            $claims = json_decode(base64_decode($claims));

            $email = $claims->email;
            $user = User::where('email', $email)->first();
            if(!$user) {
                $user = User::create([
                    'email' => $email,
                    'username' => $email,
                    'password' => ''
                ]);
                $role = Role::where('name', 'user')->first();
                $user->roles()->sync([$role->id]);
            }

            $token = JWTAuth::fromUser($user);
            $user = JWTAuth::setToken($token)
                ->toUser()
                ->load('categorySubscriptions', 'tagSubscriptions');
            $user->subscribed_category_ids = array_column($user->categorySubscriptions->toArray(), 'id');
            $user->subscribed_tag_ids = array_column($user->tagSubscriptions->toArray(), 'id');
            $user->load('roles.perms');

            return response()
                ->json([
                    'token' => $token,
                    'user'  => $user,
                ], 200);
        } catch (\Exception $e) {
            return response()
                ->json([
                    'error' => true,
                ], 200);
        }
    }


}
