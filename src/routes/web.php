<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');

Route::middleware('auth')->group(function () {
    Route::view('/top', 'top')->name('top');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
