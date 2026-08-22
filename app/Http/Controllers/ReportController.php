<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function salesByCategory()
    {
        $rows = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category',
                DB::raw('SUM(order_items.quantity * order_items.price) as total_sales')
            )
            ->groupBy('categories.name')
            ->orderByDesc('total_sales')
            ->get();

        return response()->json([
            'labels' => $rows->pluck('category'),
            'sales' => $rows->pluck('total_sales'),
        ]);
    }

    public function orders()
    {
        $orders = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')
            ->select(
                'orders.id',
                DB::raw("COALESCE(users.name, 'Guest') as customer_name"),
                'users.email',
                'orders.total_amount as total',
                'orders.status',
                'orders.created_at'
            )
            ->orderByDesc('orders.created_at')
            ->get();

        return response()->json([
            'data' => $orders,
            'total_sales' => $orders->sum('total'),
        ]);
    }
}
