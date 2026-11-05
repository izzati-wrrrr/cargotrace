<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Route sementara. Tiap satu akan diganti controller sungguhan di tahap berikutnya.
    Route::get('/shipments', fn () => view('placeholder', ['title' => 'Shipments']))->name('shipments.index');
    Route::get('/tracking', fn () => view('placeholder', ['title' => 'Tracking']))->name('tracking.index');

    // Hanya admin dan operator.
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/exceptions', fn () => view('placeholder', ['title' => 'Exceptions']))->name('exceptions.index');
        Route::get('/carriers', fn () => view('placeholder', ['title' => 'Carriers']))->name('carriers.index');
        Route::get('/reports', fn () => view('placeholder', ['title' => 'Reports']))->name('reports.index');
    });
});