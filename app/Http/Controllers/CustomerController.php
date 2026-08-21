<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    // ✅ Get all customers with related user info
    public function index()
    {
        $customers = Customer::with('user')->get();
        return response()->json($customers);
    }

    // ✅ Show single customer
    public function show($id)
    {
        $customer = Customer::with('user')->find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
        return response()->json($customer);
    }

    // ✅ Store new customer
    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'phone' => 'required|string',
    //         'address' => 'required|string',
    //         'status' => 'required|boolean',
    //     ]);

    //     $customer = Customer::create($validatedData);
    //     return response()->json($customer, 201);
    // }
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|string',
            'address'    => 'required|string', // ✅ ADD
        ]);

        // ✅ create user
        $user = User::create([
            'name'     => $data['first_name'] . ' ' . $data['last_name'],
            'email'    => $data['email'],
            'password' => Hash::make('12345678'),
        ]);

        // ✅ create customer
        $customer = Customer::create([
            'user_id' => $user->id,
            'phone'   => $data['phone'],
            'address' => $data['address'], // ✅ SAVE ADDRESS
            'status'  => true,
        ]);

        return response()->json([
            'message'  => 'Customer created successfully',
            'customer' => $customer,
            'user'     => $user
        ], 201);
    }

    // ✅ Update customer
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'status' => 'sometimes|boolean',
        ]);

        $customer->update($validatedData);
        return response()->json($customer);
    }

    // ✅ Delete customer
    public function destroy($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
