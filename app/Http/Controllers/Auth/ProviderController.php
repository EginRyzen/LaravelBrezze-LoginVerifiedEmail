<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class ProviderController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {

        try {
            $SocialUser = Socialite::driver($provider)->user();
            // dd($SocialUser);

            $user = User::where([
                'provider' => $provider,
                'provider_id' => $SocialUser->id
            ])->first();
            // dd($user);

            if (User::where('email', $SocialUser->getEmail())->exists()) {
                return redirect('/')->withErrors(['login' => 'This email uses different method to login']);
            }
            // dd($user);
            if (!$user) {
                $user = User::create([
                    'name' => $SocialUser->getName(),
                    'username' => User::generateUserName($SocialUser->getNickname()),
                    'email_verified_at' => now(),
                    'email' => $SocialUser->getEmail(),
                    'provider_token' => $SocialUser->token,
                    'provider' =>  $provider,
                    'provider_id' => $SocialUser->getId(),
                ]);
            }

            Auth::login($user);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            return redirect('/');
        }
    }
}
