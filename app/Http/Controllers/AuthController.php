<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'sometimes|required_with:password|same:password',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            // Friendly message for duplicate email
            if ($errors->has('email') && str_contains($errors->first('email'), 'already been taken')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already exists',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => $errors->first(),
                'errors'  => $errors->messages(),
            ], 422);
        }

        // Belt & suspenders: explicit duplicate check
        if (User::where('email', strtolower($request->email))->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists',
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role'     => 'user', // public registration is always a normal user
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Register successfully',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()->messages(),
            ], 422);
        }

        // Find user by email
        $user = User::where('email', strtolower($request->email))->first();

        // Check credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
                'error'   => 'Invalid credentials',
            ], 401);
        }

        // Generate token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successfully',
            'token'   => $token,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    // LOGOUT
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout'
            ], 500);
        }
    }
}
