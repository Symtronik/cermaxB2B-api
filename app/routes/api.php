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
use App\Http\Controllers\Api\Admin\Users\AdminShowController;
use App\Http\Controllers\Api\Admin\Users\AdminUpdateController;
use App\Http\Controllers\Api\Admin\Settings\VatController;

use App\Http\Controllers\Api\Admin\Category\CategoryController;
use App\Http\Controllers\Api\Admin\Series\SeriesController;
use App\Http\Controllers\Api\UserProfile\UserProfileController;
use App\Http\Controllers\Api\Admin\Attribute\AttributeController;
use App\Http\Controllers\Api\Admin\Product\ProductController;
use App\Http\Controllers\Api\Admin\Color\ColorController;
use App\Http\Controllers\Api\Cart\CartController;
use App\Http\Controllers\Api\Customer\ShippingPointController;
use App\Http\Controllers\Api\Customer\OrderController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API działa',
        'time' => now(),
    ]);
});

Route::post('user/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('user/login', [AuthController::class, 'login']);

Route::get('translations/{lang}', [TranslationController::class, 'getByLang']);

Route::post('password/forgot', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('password/reset', [PasswordResetController::class, 'reset']);

/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/
Route::prefix('user')
    ->middleware(['auth:sanctum', 'role:customer'])
    ->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('change-password', [AuthController::class, 'changePassword']);

        Route::get('company', [CompanyProfileController::class, 'show']);
        Route::post('company/change-request', [CompanyProfileController::class, 'requestUpdate']);

        Route::get('users', [CompanyUserController::class, 'index']);
        Route::post('users', [CompanyUserController::class, 'store']);
        Route::delete('users/{userId}', [CompanyUserController::class, 'destroy']);

        Route::get('catalog/categories', [CategoryController::class, 'index']);
        Route::get('catalog/categories/{category}', [CategoryController::class, 'show']);

        Route::get('catalog/series', [SeriesController::class, 'index']);
        Route::get('catalog/series/{series}', [SeriesController::class, 'show']);

        Route::get('catalog/products', [ProductController::class, 'index']);
        Route::get('catalog/products/{product}', [ProductController::class, 'show']);
    });

/*
|--------------------------------------------------------------------------
| Shared authenticated
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('vats', [VatController::class, 'index']);

    Route::get('/me', [UserProfileController::class, 'me']);
    Route::put('/me', [UserProfileController::class, 'update']);
    Route::put('/me/password', [UserProfileController::class, 'changePassword']);
});

/*
|--------------------------------------------------------------------------
| Orders
|--------------------------------------------------------------------------
*/
Route::prefix('orders')
    ->middleware(['auth:sanctum', 'role:customer|admin|super_admin'])
    ->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('{order}', [OrderController::class, 'show']);

        Route::get('{order}/repeat-preview', [OrderController::class, 'repeatPreview']);
        Route::post('{order}/repeat-to-cart', [OrderController::class, 'repeatToCart']);
    });

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/
Route::prefix('cart')
    ->middleware(['auth:sanctum', 'role:customer'])
    ->group(function () {
        Route::get('/', [CartController::class, 'show']);
        Route::post('/items', [CartController::class, 'storeItem']);
        Route::patch('/items/{item}', [CartController::class, 'updateItem']);
        Route::delete('/items/{item}', [CartController::class, 'destroyItem']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::post('/validate', [CartController::class, 'validateCart']);
        Route::post('/checkout', [CartController::class, 'checkout']);
    });

/*
|--------------------------------------------------------------------------
| Shipping points
|--------------------------------------------------------------------------
*/
Route::prefix('shipping-points')
    ->middleware(['auth:sanctum', 'role:customer|admin|super_admin'])
    ->group(function () {
        Route::get('/', [ShippingPointController::class, 'index']);
        Route::post('/', [ShippingPointController::class, 'store']);
        Route::put('{shippingPoint}', [ShippingPointController::class, 'update']);
        Route::delete('{shippingPoint}', [ShippingPointController::class, 'destroy']);
    });

/*
|--------------------------------------------------------------------------
| Admin auth
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminLoginController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin|super_admin'])->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('series', SeriesController::class);

    Route::get('vats/manage', [VatController::class, 'manage']);
    Route::post('vats', [VatController::class, 'store']);
    Route::put('vats/{vat}', [VatController::class, 'update']);
    Route::delete('vats/{vat}', [VatController::class, 'destroy']);
    Route::patch('vats/{vat}/default', [VatController::class, 'setDefault']);
    Route::patch('vats/{vat}/toggle', [VatController::class, 'toggle']);
    Route::get('vats/{vat}', [VatController::class, 'show']);

    Route::get('attributes/select', [AttributeController::class, 'select']);
    Route::apiResource('attributes', AttributeController::class);

    Route::apiResource('products', ProductController::class);

    Route::get('colors', [ColorController::class, 'index']);
    Route::post('colors', [ColorController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Super-admin only
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:super_admin'])
    ->group(function () {
        Route::get('users', [AdminsIndexController::class, 'index']);
        Route::post('users', [AdminsStoreController::class, 'store']);

        Route::get('users/{user}', [AdminShowController::class, 'show']);
        Route::put('users/{user}', [AdminUpdateController::class, 'update']);

        Route::patch('users/{user}/status', [AdminStatusController::class, 'update']);

        Route::post('users/register', [RegisterAdminController::class, 'createAdmin']);
        Route::post('users/update/roles/{userId}', [RegisterAdminController::class, 'syncRoles']);
    });
