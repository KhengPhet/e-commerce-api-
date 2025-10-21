<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//  product routes 
Route::apiResource('products', ProductController::class);

Route::middleware('auth:api')->group(function(){

    // order rout
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Reports rout
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/orders', [ReportController::class, 'ordersReport']);
    Route::get('/reports/products', [ReportController::class, 'productStock']);
    

    Route::get('/customers', [CustomerController::class, 'index']);
});
 



