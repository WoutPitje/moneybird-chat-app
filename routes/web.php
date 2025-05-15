<?php

use App\Http\Controllers\SelectAdministrationController;
use App\Http\Controllers\MoneybirdAuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('/moneybird/login', [MoneybirdAuthController::class, 'login'])->name('moneybird.login');
Route::get('/moneybird/callback', [MoneybirdAuthController::class, 'callback'])->name('moneybird.callback');
Route::get('/moneybird/logout', [MoneybirdAuthController::class, 'logout'])->name('moneybird.logout');

Route::get('/chat', [ChatController::class, 'index'])->name('chat');
Route::post('/chat', [ChatController::class, 'sendMessage'])->name('chat.sendMessage');
require __DIR__.'/auth.php';
