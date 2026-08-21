<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * LIST ORDERS
     */
    public function index()
    {
        $orders = Order::with(['orderItems.product', 'customer.user'])
            ->orderByDesc('id')
            ->get();

        return response()->json($orders);
    }

    /**
     * SHOW ORDER
     */
    public function show($id)
    {
        $order = Order::with(['orderItems.product', 'customer.user'])
            ->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    /**
     * CREATE ORDER
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|integer',
            'user_id'     => 'sometimes|integer',
            'payment_method' => 'required|string',
            'subtotal' => 'required|numeric',
            'shipping_fee' => 'required|numeric',
            'tax' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'phone' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        $customer = null;

        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 422);
            }
        } elseif (!empty($validated['user_id'])) {
            $customer = Customer::firstOrCreate(
                ['user_id' => $validated['user_id']],
                [
                    'phone'   => $request->input('phone', ''),
                    'address' => $request->input('address', ''),
                    'status'  => 'Active',
                ]
            );

            if ($customer) {
                $customer->update([
                    'phone'   => $request->input('phone', $customer->phone),
                    'address' => $request->input('address', $customer->address),
                ]);
            }
        }

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'A customer_id or user_id is required to place an order'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'payment_method' => $validated['payment_method'],
                'subtotal' => $validated['subtotal'],
                'shipping_fee' => $validated['shipping_fee'],
                'tax' => $validated['tax'],
                'total_amount' => $validated['total_amount'],
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * MARK ORDER PAID (CALLED AFTER VERIFY)
     */
    public function markPaid(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string'
        ]);

        $order = Order::where('order_number', $request->order_number)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->update([
            'status' => 'paid'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as PAID',
            'order' => $order
        ]);
    }

    /**
     * CREATE PAYMENT (CALL LARAVEL 11)
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string'
        ]);

        $order = Order::where('order_number', $request->order_number)->firstOrFail();

        $response = Http::post(
            env('PAYMENT_API') . '/payment/bakong/create',
            [
                'order_number' => $order->order_number,
                'amount' => $order->total_amount
            ]
        );

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Payment service error',
                'error' => $response->body()
            ], 500);
        }

        return response()->json($response->json());
    }

    /**
     * VERIFY PAYMENT (CALL LARAVEL 11)
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'md5' => 'required|string'
        ]);

        $response = Http::post(
            env('PAYMENT_API') . '/payment/bakong/verify',
            [
                'md5' => $request->md5
            ]
        );

        if (strtoupper($response->json('status')) === 'PAID') {
            Order::where('order_number', $request->order_number)
                ->update(['status' => 'PAID']);
        }

        return response()->json($response->json());
    }
}
