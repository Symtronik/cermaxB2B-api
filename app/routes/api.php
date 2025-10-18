<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\Auth\CustomerRegistrationController;
use App\Http\Controllers\Admin\Auth\RegisterAdminController;
use App\Http\Controllers\Admin\Users\AdminsIndexController;
use App\Http\Controllers\Customer\CompanyProfileController;
use App\Http\Controllers\Customer\CompanyUserController;

Route::post('/user/register', [CustomerRegistrationController::class, 'register'])->middleware('throttle:10,1');

Route::prefix('/user')->middleware(['auth:sanctum','role:customer'])->group(function () {
    Route::get('/company',  [CompanyProfileController::class, 'show']);
    Route::post('/company', [CompanyProfileController::class, 'upsert']);
    Route::get('/users',  [CompanyUserController::class, 'index']);
    Route::post('/users', [CompanyUserController::class, 'store']);
    Route::delete('/users/{userId}', [CompanyUserController::class, 'destroy']);
});



Route::prefix('/admin')->middleware(['auth:sanctum','role:super-admin'])->group(function () {
    Route::get('/users', [AdminsIndexController::class, 'index']);
    Route::post('/users/register', [RegisterAdminController::class, 'createAdmin']);
    Route::post('/users/update/roles/{userId}', [RegisterAdminController::class, 'syncRoles']);

});

