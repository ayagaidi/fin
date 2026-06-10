<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/reconciliation', \App\Http\Controllers\LedgerReconcileController::class);

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/files', [\App\Http\Controllers\LedgerAdminController::class, 'index'])->name('files');
    Route::match(['get', 'post'], '/upload', [\App\Http\Controllers\LedgerAdminController::class, 'upload'])->name('upload');
    Route::get('/files/{file}', [\App\Http\Controllers\LedgerAdminController::class, 'view'])->name('view');
    Route::get('/files/{file}/reconcile', [\App\Http\Controllers\LedgerAdminController::class, 'reconcile'])->name('reconcile');
    Route::get('/accounts', [\App\Http\Controllers\LedgerAdminController::class, 'accounts'])->name('accounts');
});
