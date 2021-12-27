<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use App\Services\SocialFacebookAccountService;

class SocialAuthFacebookController extends Controller
{

    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();
    }
    /**
     * Return a callback method from facebook api.
     *
     * @return callback URL from facebook
     */
    public function callback(SocialFacebookAccountService $service)
    {
        // dd(dirname(__FILE__));
        $data = $this->request->all();

        file_put_contents('/home/goal/api.goal.ge/FBlogs.json', json_encode($data));

        $user = $service->createOrGetUser(Socialite::driver('facebook')->user());
        auth()->login($user);
        return redirect()->to('/');
    }
}
