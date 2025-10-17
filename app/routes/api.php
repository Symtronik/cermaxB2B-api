<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\Auth\CustomerRegistrationController;


Route::post('/auth/register', [CustomerRegistrationController::class, 'register'])
    ->name('auth.register')
    ->middleware('throttle:10,1');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


