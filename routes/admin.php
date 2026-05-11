<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DataController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');

        Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
        Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::middleware(['admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('data')->name('data.')->group(function () {
            Route::get('intrusions', [DataController::class, 'intrusions'])->name('intrusions.index');
            Route::post('intrusions/import', [DataController::class, 'importIntrusions'])->name('intrusions.import');
            Route::post('intrusions/clear', [DataController::class, 'clearIntrusions'])->name('intrusions.clear');
            Route::get('intrusions/export/{user}', [DataController::class, 'exportIntrusionLabels'])->name('intrusions.export');
            Route::get('intrusions/export-all', [DataController::class, 'exportAllIntrusionLabels'])->name('intrusions.export-all');

            Route::get('subdomains', [DataController::class, 'subdomains'])->name('subdomains.index');
            Route::post('subdomains/import', [DataController::class, 'importSubdomains'])->name('subdomains.import');
            Route::post('subdomains/clear', [DataController::class, 'clearSubdomains'])->name('subdomains.clear');
            Route::get('subdomains/export/{user}', [DataController::class, 'exportSubdomainLabels'])->name('subdomains.export');
            Route::get('subdomains/export-all', [DataController::class, 'exportAllSubdomainLabels'])->name('subdomains.export-all');
        });

        Route::prefix('role')->name('role.')->group(function () {
            Route::resource('permission', App\Http\Controllers\Admin\Role\PermissionController::class);
            Route::resource('role', App\Http\Controllers\Admin\Role\RoleController::class);
            Route::resource('admin', App\Http\Controllers\Admin\Role\AdminController::class);
        });
    });
});

Route::any('{any}', function () {
    return redirect()->route('admin.dashboard');
})->where('any', '.*');
