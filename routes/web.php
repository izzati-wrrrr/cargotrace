<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\ExceptionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments: hak akses tiap aksi dicek oleh ShipmentPolicy di controller.
    Route::resource('shipments', ShipmentController::class);
    Route::post('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.status');
    Route::post('/shipments/{shipment}/eta', [ShipmentController::class, 'updateEta'])->name('shipments.eta');
    Route::post('/shipments/{shipment}/events', [TrackingController::class, 'store'])->name('shipments.events.store');
    Route::post('/shipments/{shipment}/exceptions', [ExceptionController::class, 'store'])->name('shipments.exceptions.store');
    Route::post('/exceptions/{exception}/status', [ExceptionController::class, 'updateStatus'])->name('exceptions.status');

    Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');

    // Admin dan operator.
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/exceptions', [ExceptionController::class, 'index'])->name('exceptions.index');
        Route::get('/reports', fn () => view('placeholder', ['title' => 'Reports']))->name('reports.index');
    });

    // Carrier, lokasi, dan pengaturan: hanya admin (slide 5).
    Route::middleware('role:admin')->group(function () {
        Route::resource('carriers', CarrierController::class)->except(['show']);
        Route::resource('locations', LocationController::class)->except(['show']);
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});