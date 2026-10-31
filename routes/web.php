<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SidebarPageController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/dashboard');
Route::get('/system-media/{media}', [SidebarPageController::class, 'systemMedia'])->name('system-media');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifications', [SidebarPageController::class, 'notifications'])->name('notifications');
    Route::get('/profile', [SidebarPageController::class, 'profile'])->name('profile');
    Route::put('/profile', [SidebarPageController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [SidebarPageController::class, 'updatePassword'])->name('profile.password.update');
    Route::middleware('permission:projects.view')->group(function (): void {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->middleware('permission:projects.create')->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->middleware('permission:projects.create')->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->middleware('permission:projects.update')->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->middleware('permission:projects.update')->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->middleware('permission:projects.delete')->name('projects.destroy');
    });
    Route::get('/tasks', [TaskController::class, 'index'])->middleware('permission:tasks.view')->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('permission:tasks.create')->name('tasks.store');
    Route::get('/risk-prediction', [SidebarPageController::class, 'riskPrediction'])->middleware('permission:risk-predictions.view')->name('risk-prediction.index');
    Route::get('/risk-mitigation', [SidebarPageController::class, 'riskMitigation'])->middleware('permission:risk-mitigations.manage')->name('risk-mitigation.index');
    Route::get('/reports', [SidebarPageController::class, 'reports'])->middleware('permission:reports.view')->name('reports.index');

    Route::prefix('admin')->middleware('permission:users.view')->group(function (): void {
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->middleware('permission:users.create')->name('admin.users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->middleware('permission:users.update')->name('admin.users.update');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->middleware('permission:users.update')->name('admin.users.reset-password');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->middleware('permission:users.delete')->name('admin.users.destroy');
        Route::get('/access-control', [SidebarPageController::class, 'accessControl'])->middleware('permission:roles.manage')->name('admin.access-control');
        Route::put('/access-control/roles/{role}', [SidebarPageController::class, 'updateAccessControl'])->middleware('permission:roles.manage')->name('admin.access-control.update');
        Route::get('/activity-logs', [SidebarPageController::class, 'activityLogs'])->middleware('permission:activity-logs.view')->name('admin.activity-logs');
        Route::get('/database-backups', [SidebarPageController::class, 'databaseBackups'])->middleware('permission:backups.view')->name('admin.database-backups');
        Route::post('/database-backups', [SidebarPageController::class, 'createDatabaseBackup'])->middleware('permission:backups.create')->name('admin.database-backups.create');
        Route::get('/database-backups/{filename}/download', [SidebarPageController::class, 'downloadDatabaseBackup'])->middleware('permission:backups.view')->name('admin.database-backups.download');
        Route::get('/system-settings', [SidebarPageController::class, 'systemSettings'])->middleware('permission:system-settings.view')->name('admin.system-settings');
        Route::patch('/system-settings/general', [SidebarPageController::class, 'updateGeneralSettings'])->middleware('permission:system-settings.update')->name('admin.system-settings.general');
        Route::patch('/system-settings', [SidebarPageController::class, 'updateAllSettings'])->middleware('permission:system-settings.update')->name('admin.system-settings.update');
        Route::post('/system-settings', [SidebarPageController::class, 'updateAllSettings'])->middleware('permission:system-settings.update')->name('admin.system-settings.store');
        Route::patch('/system-settings/branding', [SidebarPageController::class, 'updateBrandingSettings'])->middleware('permission:system-settings.update')->name('admin.system-settings.branding');
        Route::patch('/system-settings/contact', [SidebarPageController::class, 'updateContactSettings'])->middleware('permission:system-settings.update')->name('admin.system-settings.contact');
        Route::patch('/system-settings/address', [SidebarPageController::class, 'updateAddressSettings'])->middleware('permission:system-settings.update')->name('admin.system-settings.address');
        Route::delete('/system-settings/{type}', [SidebarPageController::class, 'removeBranding'])->middleware('permission:system-settings.update')->whereIn('type', ['logo', 'favicon'])->name('admin.system-settings.remove');
    });
});

if (app()->environment('local')) {
    Route::get('/preview/errors/{status}', function (int $status) {
        abort_unless(in_array($status, [400, 401, 403, 404, 405, 408, 419, 422, 429, 500, 501, 502, 503, 504], true), 404);

        return Inertia::render('Errors/ErrorPage', ['status' => $status]);
    })->whereNumber('status')->name('errors.preview');
}
