<?php

namespace App\Http\Controllers;

use App\Mail\PasswordReset;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use JWTAuth;
use App;

class AuthController extends Controller
{
    public function logIn(Request $request){
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        $loginField = $request->input('email');

        $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $request->merge([ $loginType => $loginField ]);

        $credentials = $request->only([ $loginType, 'password']);


        // print_r( $credentials );
        // die;

        if(!$token = JWTAuth::attempt($credentials)){
            return response()
                ->json([
                    'error' => (App::getLocale() == 'ka' ? 'ელ. ფოსტა ან/და პაროლი არასწორია.' : 'Неправильный email или пароль.')
                ], 401);
        }

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

    public function refreshToken(Request $request){
        $token = JWTAuth::getToken();
        $newToken = JWTAuth::refresh($token);

        return response()
            ->json([
                'token' => $newToken
            ]);
    }

    public function resetPassword(Request $request) {
        $this->validate($request, [
            'email' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user)
            return response()->json([
                    'error' => 'ასეთი მომხმარებელი არ არის რეგისტრირებული'
                ], 401);

        $password = Str::random(8);

        try {
            Mail::to($user)->send(new PasswordReset($password));
            $user->update(['password' => Hash::make($password)]);

            return response()->json([
                'message' => 'ახალი პაროლი გამოგზავნილია თქვენს ელ-ფოსტაზე'
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
            return response()->json([
                'error' => 'დაფიქსირდა შეცდომა, გთხოვთ სცადოთ მოგვიანებით'
            ], 401);
        }
    }
}
