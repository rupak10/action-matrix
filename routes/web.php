<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActionMatrixController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('analytics.index');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Administrative Routes (Super Admin & Admin Only)
    Route::middleware('role:Super_Admin,Admin')->group(function () {
        // Role Management
        Route::resource('roles', \App\Http\Controllers\RoleController::class);

        // User Role Management
        Route::get('user-roles', [\App\Http\Controllers\UserRoleController::class, 'index'])->name('user-roles.index');
        Route::get('user-roles/{empId}/edit', [\App\Http\Controllers\UserRoleController::class, 'edit'])->name('user-roles.edit');
        Route::put('user-roles/{empId}', [\App\Http\Controllers\UserRoleController::class, 'update'])->name('user-roles.update');

        // User Management
        Route::resource('users', \App\Http\Controllers\UserController::class);

        // PO Info Management
        Route::get('/admin/po-info', [\App\Http\Controllers\PoInfoController::class, 'index'])->name('admin.po-info.index');
        Route::get('/admin/po-info/create', [\App\Http\Controllers\PoInfoController::class, 'create'])->name('admin.po-info.create');
        Route::post('/admin/po-info', [\App\Http\Controllers\PoInfoController::class, 'store'])->name('admin.po-info.store');
        Route::get('/admin/po-info/{id}/edit', [\App\Http\Controllers\PoInfoController::class, 'edit'])->name('admin.po-info.edit');
        Route::put('/admin/po-info/{id}', [\App\Http\Controllers\PoInfoController::class, 'update'])->name('admin.po-info.update');
        Route::delete('/admin/po-info/{id}', [\App\Http\Controllers\PoInfoController::class, 'destroy'])->name('admin.po-info.destroy');
        Route::patch('/admin/po-info/{id}/activate', [\App\Http\Controllers\PoInfoController::class, 'activate'])->name('admin.po-info.activate');
    });

    // Action Matrix Module
    Route::get('/action-matrix/data', [ActionMatrixController::class, 'getData'])->name('action-matrix.data');
    Route::post('action-matrix/forward', [ActionMatrixController::class, 'forward'])->name('action-matrix.forward');
    Route::post('action-matrix/approve', [ActionMatrixController::class, 'approve'])->name('action-matrix.approve');
    Route::post('action-matrix/reject', [ActionMatrixController::class, 'reject'])->name('action-matrix.reject');
    
    // PO Workflow Routes
    Route::post('/action-matrix/comment', [ActionMatrixController::class, 'storeComment'])->name('action-matrix.comment');
    Route::post('/action-matrix/po-forward', [ActionMatrixController::class, 'forwardToPoSupervisor'])->name('action-matrix.po-forward');
    Route::post('/action-matrix/po-forward-to-officer', [ActionMatrixController::class, 'forwardToPoOfficer'])->name('action-matrix.po-forward-to-officer');
    Route::post('/action-matrix/po-approve', [ActionMatrixController::class, 'approvePoResponse'])->name('action-matrix.po-approve');
    Route::post('/action-matrix/po-reject', [ActionMatrixController::class, 'rejectPoResponse'])->name('action-matrix.po-reject');
    Route::get('/action-matrix/{acm_id}/history', [ActionMatrixController::class, 'getHistory'])->name('action-matrix.history');
    Route::get('/action-matrix/{acmId}/pksf-comment', [ActionMatrixController::class, 'getPksfComment'])->name('action-matrix.pksf-comment');
    Route::get('/action-matrix/{acmId}/my-draft', [ActionMatrixController::class, 'getMyDraft'])->name('action-matrix.my-draft');
    
    // PKSF Workflow Routes (Closure & Revision)
    Route::post('/action-matrix/reopen', [ActionMatrixController::class, 'reopen'])->name('action-matrix.reopen');
    Route::post('/action-matrix/request-closure', [ActionMatrixController::class, 'requestClosure'])->name('action-matrix.request-closure');
    Route::post('/action-matrix/request-revision', [ActionMatrixController::class, 'requestRevision'])->name('action-matrix.request-revision');
    Route::post('/action-matrix/approve-closure', [ActionMatrixController::class, 'approveClosure'])->name('action-matrix.approve-closure');
    Route::post('/action-matrix/reject-closure', [ActionMatrixController::class, 'rejectClosure'])->name('action-matrix.reject-closure');
    Route::post('/action-matrix/approve-revision', [ActionMatrixController::class, 'approveRevision'])->name('action-matrix.approve-revision');
    Route::post('/action-matrix/reject-revision', [ActionMatrixController::class, 'rejectRevision'])->name('action-matrix.reject-revision');
    
    Route::resource('action-matrix', ActionMatrixController::class);

    // Senior Management comments
    Route::post('/action-matrix/{acm_id}/sm-comments', [\App\Http\Controllers\AcmSmCommentController::class, 'store'])
        ->name('action-matrix.sm-comments.store')
        ->middleware('role:SM_MD,SM_DMD,SM_SGM');

    // PO Assignment admin
    Route::get('/admin/po-assignments', [\App\Http\Controllers\PoAssignmentController::class, 'index'])->name('admin.po-assignments.index');
    Route::post('/admin/po-assignments', [\App\Http\Controllers\PoAssignmentController::class, 'store'])->name('admin.po-assignments.store');
    Route::delete('/admin/po-assignments/{id}', [\App\Http\Controllers\PoAssignmentController::class, 'destroy'])->name('admin.po-assignments.destroy');

    // Analytics — authorization handled inside AnalyticsController
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [AnalyticsController::class, 'getData'])->name('analytics.data');

    // Reports — authorization handled inside ReportController
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/report-1', [ReportController::class, 'report1'])->name('report1');
        Route::get('/action-matrix-report', [ReportController::class, 'actionMatrixFilter'])->name('action-matrix-report');
        Route::get('/action-matrix-report/generate', [ReportController::class, 'actionMatrixGenerate'])->name('action-matrix-report.generate');
    });
});

require __DIR__.'/auth.php';
