<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Administrative Routes (Super Admin & Admin Only)
    Route::middleware('role:Super_Admin, Admin')->group(function () {
        // Role Management
        Route::resource('roles', \App\Http\Controllers\RoleController::class);

        // User Role Management
        Route::get('user-roles', [\App\Http\Controllers\UserRoleController::class, 'index'])->name('user-roles.index');
        Route::get('user-roles/{empId}/edit', [\App\Http\Controllers\UserRoleController::class, 'edit'])->name('user-roles.edit');
        Route::put('user-roles/{empId}', [\App\Http\Controllers\UserRoleController::class, 'update'])->name('user-roles.update');

        // User Management
        Route::resource('users', \App\Http\Controllers\UserController::class);
    });

    // Action Matrix Module
    Route::post('action-matrix/forward', [\App\Http\Controllers\ActionMatrixController::class, 'forward'])->name('action-matrix.forward');
    Route::post('action-matrix/approve', [\App\Http\Controllers\ActionMatrixController::class, 'approve'])->name('action-matrix.approve');
    Route::post('action-matrix/reject', [\App\Http\Controllers\ActionMatrixController::class, 'reject'])->name('action-matrix.reject');
    Route::resource('action-matrix', \App\Http\Controllers\ActionMatrixController::class);
});

require __DIR__.'/auth.php';
