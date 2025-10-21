<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
     public function index()
    {
        $customers = User::where('role', 'user')
            ->select('id', 'name', 'email', 'created_at')
            ->with('profile') // if you have a profile table for phone/address
            ->get();

        return response()->json($customers);
    }
}
