<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterAdminController;
use App\Http\Controllers\Api\Admin\Users\AdminsIndexController;
use App\Http\Controllers\Api\Admin\Users\AdminStatusController;
use App\Http\Controllers\Api\Admin\Users\AdminsStoreController;
use App\Http\Controllers\Api\Customer\CompanyProfileController;
use App\Http\Controllers\Api\Customer\CompanyUserController;
use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\AdminLoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Admin\Category\CategoryController;


Route::post('/user/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/user/login',  [AuthController::class, 'login']);
Route::get('/translations/{lang}', [TranslationController::class, 'getByLang']);
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);
// Route::apiResource('/categories', CategoryController::class);


Route::prefix('/user')->middleware(['auth:sanctum','role:customer'])->group(function () {
    Route::get('/company',  [CompanyProfileController::class, 'show']);
    Route::post('/company', [CompanyProfileController::class, 'upsert']);
    Route::get('/users',  [CompanyUserController::class, 'index']);
    Route::post('/users', [CompanyUserController::class, 'store']);
    Route::delete('/users/{userId}', [CompanyUserController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:admin|super-admin', ])->group(function () {
    Route::apiResource('categories', CategoryController::class);
});


Route::prefix('/admin')->group(function () {

    // publiczny endpoint logowania adminów
    Route::post('/login', [AdminLoginController::class, 'login']);

    // endpointy tylko dla zalogowanego super-admina
    Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
        Route::get('/users', [AdminsIndexController::class, 'index']);
        Route::post('/users/register', [RegisterAdminController::class, 'createAdmin']);
        Route::post('/users/update/roles/{userId}', [RegisterAdminController::class, 'syncRoles']);
        Route::patch('/users/{user}/status', [AdminStatusController::class, 'update']);
        Route::post('/users', [AdminsStoreController::class, 'store']);
    });



});

