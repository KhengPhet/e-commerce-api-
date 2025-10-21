<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Summary Report
    public function summary(){
        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalSales = Order::sum('total_price');

        return response()->json([
            'message' => 'Report summary generated successfully',
            'data' => [
                'total_users' => $totalUsers,
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'total_sales' => number_format($totalSales, 2),
            ],
        ], 200);
    }

    // Detailed Orders Report
    public function ordersReport(){
        $orders = Order::with(['user', 'product'])->orderBy('created_at', 'desc')->get();
        return response()->json([
            'message' => 'Orders report fetched successfully',
            'orders' => $orders
        ], 200);
    }

    // Product Stock Report
    public function productStock(){
        $products = Product::select('id', 'name', 'stock', 'price')->orderBy('stock', 'asc')->get();
        return response()->json([
            'message' => 'Product stock report fetched successfully',
            'products' => $products
        ], 200);
    }
}
