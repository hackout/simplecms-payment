<?php

use Illuminate\Support\Facades\Route;
use SimpleCMS\Payment\Http\Controllers\BackendController;

Route::get('/', [BackendController::class, 'index'])->name('backend.account.index');
Route::get('/list', [BackendController::class, 'list'])->name('backend.account.list');
Route::put('/{id}/{type}', [BackendController::class, 'update'])->name('backend.account.update')->where(['id' => uuid_regex(), 'type' => id_regex()]);
Route::delete('/{id}/{type}', [BackendController::class, 'delete'])->name('backend.account.delete')->where(['id' => uuid_regex(), 'type' => id_regex()]);
