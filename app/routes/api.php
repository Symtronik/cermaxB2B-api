<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\PasswordResetController;

use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\CompanyProfileController;
use App\Http\Controllers\Api\Customer\CompanyUserController;

use App\Http\Controllers\Api\Auth\AdminLoginController;
use App\Http\Controllers\Api\Auth\RegisterAdminController;
use App\Http\Controllers\Api\Admin\Users\AdminsIndexController;
use App\Http\Controllers\Api\Admin\Users\AdminsStoreController;
use App\Http\Controllers\Api\Admin\Users\AdminStatusController;
use App\Http\Controllers\Api\Admin\Settings\VatController;

use App\Http\Controllers\Api\Admin\Category\CategoryController;
use App\Http\Controllers\Api\Admin\Series\SeriesController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::post('user/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('user/login', [AuthController::class, 'login']);

Route::get('translations/{lang}', [TranslationController::class, 'getByLang']);

Route::post('password/forgot', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('password/reset', [PasswordResetController::class, 'reset']);

/*
|--------------------------------------------------------------------------
| Customer (authenticated)
|--------------------------------------------------------------------------
*/
Route::prefix('user')
    ->middleware(['auth:sanctum', 'role:customer'])
    ->group(function () {
        Route::get('company', [CompanyProfileController::class, 'show']);
        Route::post('company', [CompanyProfileController::class, 'upsert']);

        Route::get('users', [CompanyUserController::class, 'index']);
        Route::post('users', [CompanyUserController::class, 'store']);
        Route::delete('users/{userId}', [CompanyUserController::class, 'destroy']);
    });

Route::middleware(['auth:sanctum'])->group(function () {
    // VAT do selecta (tylko aktywne)
    Route::get('vats', [VatController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Admin auth (public)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminLoginController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Admin API (authenticated admin|super-admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin|super-admin'])->group(function () {
    // Kategorie (prawdziwe kategorie, bez obrazka)
    Route::apiResource('categories', CategoryController::class);

    // Serie (z obrazkiem) — update przez POST + _method=PUT
    Route::apiResource('series', SeriesController::class)->except(['update']);
    Route::post('series/{series}', [SeriesController::class, 'update']);

    Route::get('vats/manage', [VatController::class, 'manage']); // paginacja + search do tabeli
    Route::post('vats', [VatController::class, 'store']);
    Route::put('vats/{vat}', [VatController::class, 'update']);
    Route::delete('vats/{vat}', [VatController::class, 'destroy']);
    Route::patch('vats/{vat}/default', [VatController::class, 'setDefault']);
    Route::patch('vats/{vat}/toggle', [VatController::class, 'toggle']);
    Route::get('vats/{vat}', [VatController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Super-admin only
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:super-admin'])
    ->group(function () {
        Route::get('users', [AdminsIndexController::class, 'index']);

        Route::post('users', [AdminsStoreController::class, 'store']);
        Route::post('users/register', [RegisterAdminController::class, 'createAdmin']);
        Route::post('users/update/roles/{userId}', [RegisterAdminController::class, 'syncRoles']);

        Route::patch('users/{user}/status', [AdminStatusController::class, 'update']);
    });
