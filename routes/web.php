<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipmentController;
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

    // Shipments: hak akses tiap aksi dicek oleh ShipmentPolicy di controller.
    Route::resource('shipments', ShipmentController::class);
    Route::post('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.status');

    // Route sementara, akan diganti controller sungguhan di tahap berikutnya.
    Route::get('/tracking', fn () => view('placeholder', ['title' => 'Tracking']))->name('tracking.index');

    // Admin dan operator.
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/exceptions', fn () => view('placeholder', ['title' => 'Exceptions']))->name('exceptions.index');
        Route::get('/reports', fn () => view('placeholder', ['title' => 'Reports']))->name('reports.index');
    });

    // Carrier dan lokasi: hanya admin (slide 5).
    Route::middleware('role:admin')->group(function () {
        Route::resource('carriers', CarrierController::class)->except(['show']);
        Route::resource('locations', LocationController::class)->except(['show']);
    });
});