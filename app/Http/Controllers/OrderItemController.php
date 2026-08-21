<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    // GET all order items
    public function index()
    {
        // return OrderItem::with(['order.customer.user', 'product'])->get();
        return response()->json(OrderItem::with('product','order')->get());
    }

    // GET single order item
    public function show($id)
    {
        $item = OrderItem::with('order.customer', 'product')->find($id);
        if (!$item) {
            return response()->json(['message' => 'Order item not found'], 404);
        }
        return $item;
    }

    // DELETE an order item
    public function destroy($id)
    {
        // $item = OrderItem::find($id);
        // if (!$item) {
        //     return response()->json(['message' => 'Order item not found'], 404);
        // }
        // $item->delete();
        // return response()->json(['message' => 'Order item deleted successfully']);
        OrderItem::findOrFail($id)->delete();
        return response()->json(['message'=>'Item deleted']);
    }
}
