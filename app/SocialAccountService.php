<?php

namespace App;

use Laravel\Socialite\Contracts\Provider;

class SocialAccountService
{
    public function createOrGetUser($providerUser, $providerName)
    {


        // file_put_contents('/home/goal/api.goal.ge/fblog.json', json_encode($_POST));
        // $providerUser = $provider->stateless()->user();
        // $providerName = class_basename($provider);

        $account = \App\SocialAccount::whereProvider($providerName)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        // dd($providerUser);
        // $user = Socialite::driver('twitter')->userFromTokenAndSecret($token['oauth_token'], $token['oauth_token_secret']);

        if ($account) {
            return $account->user;
        } else {

            $account = new \App\SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $providerName
            ]);

            $user = \App\User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {

                $fname = $providerUser->getName();
                $lname = '';

                $name = explode(' ',$providerUser->getName());
                if(is_array($name)){
                    $fname = $name[0];
                    $lname = $name[1];
                }

                $avatar = @$providerUser->getAvatar();
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'username' => $providerUser->getEmail(),
                    'first_name' => $fname,
                    'last_name' => $lname,
                    'password' => md5(time()),
                    'avatar' => $avatar
                ]);
            }

            $account->user()->associate($user);
            $account->save();

            return $user;

        }

    }


    public function createOrGetUserFORBACKEND(Provider $provider)
    {


        // file_put_contents('/home/goal/api.goal.ge/fblog.json', json_encode($_POST));
        $providerUser = $provider->stateless()->user();
        $providerName = class_basename($provider);

        $account = \App\SocialAccount::whereProvider($providerName)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        dd($providerUser);
        // $user = Socialite::driver('twitter')->userFromTokenAndSecret($token['oauth_token'], $token['oauth_token_secret']);

        if ($account) {
            return $account->user;
        } else {

            $account = new \App\SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $providerName
            ]);

            $user = \App\User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {

                $fname = $providerUser->getName();
                $lname = '';

                $name = explode(' ',$providerUser->getName());
                if(is_array($name)){
                    $fname = $name[0];
                    $lname = $name[1];
                }

                $avatar = @$providerUser->getAvatar();
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'username' => $providerUser->getEmail(),
                    'first_name' => $fname,
                    'last_name' => $lname,
                    'password' => md5(time()),
                    'avatar' => $avatar
                ]);
            }

            $account->user()->associate($user);
            $account->save();

            return $user;

        }

    }
}
