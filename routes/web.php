<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActionMatrixController;
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
    Route::post('action-matrix/forward', [ActionMatrixController::class, 'forward'])->name('action-matrix.forward');
    Route::post('action-matrix/approve', [ActionMatrixController::class, 'approve'])->name('action-matrix.approve');
    Route::post('action-matrix/reject', [ActionMatrixController::class, 'reject'])->name('action-matrix.reject');
    
    // PO Workflow Routes
    Route::post('/action-matrix/comment', [ActionMatrixController::class, 'storeComment'])->name('action-matrix.comment');
    Route::post('/action-matrix/po-forward', [ActionMatrixController::class, 'forwardToPoSupervisor'])->name('action-matrix.po-forward');
    Route::post('/action-matrix/po-approve', [ActionMatrixController::class, 'approvePoResponse'])->name('action-matrix.po-approve');
    Route::post('/action-matrix/po-reject', [ActionMatrixController::class, 'rejectPoResponse'])->name('action-matrix.po-reject');
    Route::get('/action-matrix/{acm_id}/history', [ActionMatrixController::class, 'getHistory'])->name('action-matrix.history');
    Route::get('/action-matrix/{acmId}/my-draft', [ActionMatrixController::class, 'getMyDraft'])->name('action-matrix.my-draft');
    
    // PKSF Workflow Routes (Closure & Revision)
    Route::post('/action-matrix/request-closure', [ActionMatrixController::class, 'requestClosure'])->name('action-matrix.request-closure');
    Route::post('/action-matrix/request-revision', [ActionMatrixController::class, 'requestRevision'])->name('action-matrix.request-revision');
    Route::post('/action-matrix/approve-closure', [ActionMatrixController::class, 'approveClosure'])->name('action-matrix.approve-closure');
    Route::post('/action-matrix/reject-closure', [ActionMatrixController::class, 'rejectClosure'])->name('action-matrix.reject-closure');
    Route::post('/action-matrix/approve-revision', [ActionMatrixController::class, 'approveRevision'])->name('action-matrix.approve-revision');
    Route::post('/action-matrix/reject-revision', [ActionMatrixController::class, 'rejectRevision'])->name('action-matrix.reject-revision');
    
    Route::resource('action-matrix', ActionMatrixController::class);
});

require __DIR__.'/auth.php';
