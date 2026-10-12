<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimbanganController;

Route::get('/status', function () {
    return response()->json([
        'status' => 'ok',
        'aplikasi' => 'Tomodi',
    ]);
});

Route::get('/timbangan', [TimbanganController::class, 'index']);

Route::post('/timbangan', [TimbanganController::class, 'store']);
