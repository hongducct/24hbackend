<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/profile', function (Request $request) {
//         return $request->user();
//     });
// });
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::apiResource('users', UserController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('banks', BankController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('order-statuses', OrderStatusController::class); // Đổi tên route cho rõ ràng

Route::get('/orders/{order}/details', [OrderController::class, 'orderDetails']); // Route tùy chỉnh cho chi tiết đơn hàng