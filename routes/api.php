<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('/customers/filter', [CustomerController::class, 'filter']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('product/quantity', [ProductController::class, 'getQuantity']);

    Route::resource('product-categories', ProductCategoryController::class);
    Route::resource('product-brands', ProductBrandController::class);
    Route::resource('orders', OrderController::class);
    Route::get('/get/order', [OrderController::class, 'index']);
    Route::get('/supplier/order/{id}', [PurchaseController::class, 'index']);

    // payments
    Route::post('payments/update', [PaymentController::class, 'updatePayment']);
    Route::get('users/{user}/orders', [PaymentController::class, 'userOrders']);
    Route::resource('customers', CustomerController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::get('/supplier/search', [SupplierController::class, 'supplierSearch']);
    Route::resource('cashiers', CashierController::class);
    Route::get('/all/users', [CashierController::class, 'allUser']);
    Route::get('cashier-orders/{cashier_id}', [CashierController::class, 'cashierOrders']);
    Route::resource('expenses', ExpenseController::class);
    Route::get('cashier/today-orders', [CashierController::class, 'todayOrders']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('purchases', [PurchaseController::class, 'store']);

});
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::resource('roles', RoleController::class);
    Route::post('roles/assign-to-user', [RoleController::class, 'assignToUser']);
    Route::post('roles/assign-permission', [RoleController::class, 'assignPermission']);

    Route::resource('permissions', PermissionController::class);
    Route::post('permissions/assign-to-user', [PermissionController::class, 'assignToUser']);
    Route::post('permissions/assign-to-role', [PermissionController::class, 'assignToRole']);

});
