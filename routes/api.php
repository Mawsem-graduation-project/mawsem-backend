<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum','HasActiveShop'])->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);


    Route::middleware('IsShopOwner')->prefix('shop')->group(function () {

    });
});
