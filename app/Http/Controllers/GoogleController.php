<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate([
            'email' => $googleUser->getEmail(),
        ], [
            'password'=> bcrypt(Str::random(16)),
            'name' => $googleUser->getName(),
            // 'google_id' => $googleUser->getId(),
            'email'=> $googleUser->getEmail(),
            // 'avatar' => $googleUser->getAvatar(),
            'role' => 'user',
        ]);

        $token = JWTAuth::fromUser($user);

        return redirect("http://localhost:5173/auth/callback?token=$token");

        // $token = $user->createToken('auth')->plainTextToken;

        

        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'Logged in with Google successfully',
        //     'user' => $user,
        // ]);
        // return redirect()->to('http://localhost:5173/auth/callback?token=' . $token);
    }
}
