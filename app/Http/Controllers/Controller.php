<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
     public function index()
    {
        // Only admin can access
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Fetch all users with their orders
        $customers = User::with('orders')->where('role', 'user')->get();

        return response()->json([
            'status' => 'success',
            'customers' => $customers
        ], 200);
    }
}
