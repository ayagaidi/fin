<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/reconciliation', \App\Http\Controllers\LedgerReconcileController::class);
