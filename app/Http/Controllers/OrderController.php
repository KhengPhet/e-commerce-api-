<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())->get();
        return response()->json($orders);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);



        $product = Product::find($validated['product_id']);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }

        $user = Auth::user(); // from token
        $product = Product::findOrFail($request->product_id);
        $total = $product->price * $request->quantity;
        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'total' => $total,
            'status' => 'pending',
        ]);

        $product->stock -= $validated['quantity'];
        $product->save();

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order
        ], 201);
    }

    // Get all orders for current user
    public function userOrders()
    {
        $orders = Order::with('product')->where('user_id', Auth::id())->latest()->get(['id', 'product_id', 'stock', 'total', 'status', 'created_at']); // Include created_at

        return response()->json([
            'status' => 'success',
            'orders' => $orders
        ], 200);
    }
}
