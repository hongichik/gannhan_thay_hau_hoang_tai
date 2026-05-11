<?php

use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserLabelController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('user.home')
        : redirect()->route('user.login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [UserAuthController::class, 'showLogin'])->name('user.login');
    Route::post('/login', [UserAuthController::class, 'login'])->name('user.login.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [UserLabelController::class, 'home'])->name('user.home');

    Route::get('/label/intrusions', [UserLabelController::class, 'showIntrusionLabelPage'])->name('user.label.intrusions');
    Route::post('/label/intrusions', [UserLabelController::class, 'storeIntrusionLabel'])->name('user.label.intrusions.store');
    Route::get('/label/intrusions/history', [UserLabelController::class, 'intrusionHistory'])->name('user.label.intrusions.history');

    Route::get('/label/subdomains', [UserLabelController::class, 'showSubdomainLabelPage'])->name('user.label.subdomains');
    Route::post('/label/subdomains', [UserLabelController::class, 'storeSubdomainLabel'])->name('user.label.subdomains.store');
    Route::get('/label/subdomains/history', [UserLabelController::class, 'subdomainHistory'])->name('user.label.subdomains.history');

    Route::post('/logout', [UserAuthController::class, 'logout'])->name('user.logout');
});
