<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('brands', BrandController::class);


//  product routes

Route::apiResource('products', ProductController::class);
Route::patch('/products/{id}/status', [ProductController::class, 'changeStatus']);
Route::apiResource('/categories', CategoryController::class);
Route::apiResource('customers', CustomerController::class);


// order routes
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/orders/paid', [OrderController::class, 'markPaid']);

Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{order}', [OrderController::class, 'show']);



// payment routes
Route::post('/payment/create', [PaymentController::class, 'create']);
Route::post('/payment/verify', [PaymentController::class, 'verify']);



//     // order rout
    Route::get('/order-items', [OrderItemController::class, 'index']);
    Route::get('/order-items/{id}', [OrderItemController::class, 'show']);
    Route::delete('/order-items/{id}', [OrderItemController::class, 'destroy']);



Route::get('/reports/sales-by-category', [ReportController::class, 'salesByCategory']);

Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

