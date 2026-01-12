<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/verify',[VerificationController::class, 'index']);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'check_status']);

Route::post('/logout', [AuthController::class, 'logout']) ->middleware('auth');