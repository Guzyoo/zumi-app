<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\SlotManagementController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/slots', [SlotManagementController::class, 'index'])->name('slots.index');
    Route::post('/slots', [SlotManagementController::class, 'store'])->name('slots.store');
    Route::post('/slots/{id}/toggle', [SlotManagementController::class, 'toggleActive'])->name('slots.toggle');
    Route::delete('/slots/{id}', [SlotManagementController::class, 'destroy'])->name('slots.destroy');
});