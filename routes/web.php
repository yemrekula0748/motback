<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\QuestAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Admin login (no auth required)
Route::get('/admin/login', [AdminLoginController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Admin panel (requires auth + is_admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/users/{id}', [AdminController::class, 'show'])->name('admin.users.show');
    Route::patch('/users/{id}/ban', [AdminController::class, 'ban'])->name('admin.users.ban');
    Route::patch('/users/{id}/unban', [AdminController::class, 'unban'])->name('admin.users.unban');

    Route::get('/quests', [QuestAdminController::class, 'index'])->name('admin.quests.index');
    Route::get('/quests/create', [QuestAdminController::class, 'create'])->name('admin.quests.create');
    Route::post('/quests', [QuestAdminController::class, 'store'])->name('admin.quests.store');
    Route::get('/quests/{id}/edit', [QuestAdminController::class, 'edit'])->name('admin.quests.edit');
    Route::put('/quests/{id}', [QuestAdminController::class, 'update'])->name('admin.quests.update');
    Route::delete('/quests/{id}', [QuestAdminController::class, 'destroy'])->name('admin.quests.destroy');
});
