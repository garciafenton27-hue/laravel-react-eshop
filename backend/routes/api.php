<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AdminController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'store']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Payments
    Route::post('/payment/create-order', [PaymentController::class, 'createRazorpayOrder']);
    Route::post('/payment/verify', [PaymentController::class, 'verifyPayment']);

    // Admin Routes
    Route::middleware(['role:admin|super_admin'])->group(function () {
        Route::apiResource('admin/products', ProductController::class)->except(['index', 'show']);
        Route::apiResource('admin/categories', CategoryController::class)->except(['index']);
        Route::get('admin/orders', [OrderController::class, 'adminIndex']);
        Route::patch('admin/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::get('admin/sellers', [AdminController::class, 'getSellers']);
        Route::patch('admin/sellers/{id}/approve', [AdminController::class, 'approveSeller']);
        Route::patch('admin/sellers/{id}/reject', [AdminController::class, 'rejectSeller']);
    });

    // Seller Routes
    Route::post('/seller/register', [SellerController::class, 'store']);
    Route::middleware(['role:seller'])->group(function () {
        Route::get('/seller/products', [ProductController::class, 'sellerProducts']);
        Route::post('/seller/products', [ProductController::class, 'store']);
        Route::put('/seller/products/{product}', [ProductController::class, 'update']);
        Route::delete('/seller/products/{product}', [ProductController::class, 'destroy']);
    });
});
