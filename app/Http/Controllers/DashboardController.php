<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary()
    {
        $startDate = Carbon::now()->subMonth();

        // ✅ Revenue = សរុបតម្លៃ Order
        $revenue = Order::whereIn('status', ['completed', 'paid'])
            ->where('created_at', '>=', $startDate)
            ->sum('total_amount');

        // ✅ Product Sold = សរុប Item លក់បាន
        $productSold = OrderItem::whereHas('order', function ($q) use ($startDate) {
            $q->whereIn('status', ['completed', 'paid'])
              ->where('created_at', '>=', $startDate);
        })->sum('quantity');

        // ✅ Customer = ចំនួន Customer
        $customers = Customer::count();

        return response()->json([
            'revenue' => number_format($revenue, 2),
            'product_sold' => $productSold,
            'customers' => $customers,
        ]);
    }
}
