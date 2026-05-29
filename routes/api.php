<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExcelController;
//use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum','HasActiveShop'])->group(function () {

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // CSV, Excel & Sales
    Route::get('export-sales',[ExcelController::class, 'exportSalesToCSV']);
    Route::post('import-sales',[ExcelController::class, 'importSales']);

    Route::delete('sales/delete-all', [SaleController::class, 'destroyAll']);
    Route::resource('sales', SaleController::class);


    // Products
    Route::resource('products', ProductController::class);
    Route::get('forecast/{product}',[ProductController::class, 'forecast']);

    // Dashboard
    Route::get('dashboard',[DashboardController::class, 'summary']);

    // Inventory
//    Route::get('inventory',[InventoryController::class, 'index']);
//    Route::get('inventory/{inventory}',[InventoryController::class, 'show']);
//    Route::middleware('IsShopOwner')->group(function () {
//        Route::post('inventory',[InventoryController::class, 'store']);
//        Route::patch('inventory/{inventory}', [InventoryController::class, 'update']);
//        Route::delete('inventory/{inventory}', [InventoryController::class, 'destroy']);
//    });
});
