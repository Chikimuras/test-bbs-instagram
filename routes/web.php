<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/auth/instagram/callback', [\App\Http\Controllers\InstagramController::class, 'getInstagramToken'])->name('getInstagramToken');
Route::get('/feed', [\App\Http\Controllers\InstagramController::class, 'getInstagramFeed'])->name('getInstagramFeed');
Route::get('/media', [\App\Http\Controllers\InstagramController::class, 'displayMedia'])->name('displayMedia');
