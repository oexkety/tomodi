<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\LaporanController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

        Route::get('/laporan', [LaporanController::class, 'index'])
    ->name('laporan.index');

Route::get('/laporan/csv', [LaporanController::class, 'csv'])
    ->name('laporan.csv');

        Route::get('/transaksi/{timbangan}', [TransaksiController::class, 'show'])
    ->whereNumber('timbangan')
    ->name('transaksi.show');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::middleware('role:admin')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])
            ->name('users.index');

        Route::post('/pengguna', [UserController::class, 'store'])
            ->name('users.store');
    });
});
