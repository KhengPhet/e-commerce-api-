<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class FacebookController extends Controller
{
    // Redirect to Facebook
    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();
    }
    // Handle Facebook callback
    public function callback(){
        $fbUser = Socialite::diriver("facebook")->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $fbUser->getEmail()],
            [
                'name' => $fbUser->getName(),
                'facebook_id' => $fbUser->getId(),
                'avatar' => $fbUser->getAvatar(),
                'password' => Hash::make('fb_' . $fbUser->getId()),
                'role' => 'user'
            ]
        );
        $token = JWTAuth::forUser($user);
         // Redirect to React with token
        return redirect("http://localhost:5173/auth/callback?token=$token");


    }
}
