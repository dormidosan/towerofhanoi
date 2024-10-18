<?php

use App\Http\Controllers\TowerController;
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

Route::get('/tower', function () {
    return view('tower');
});

Route::get('/clean', [TowerController::class, 'cleanSession'])->name('clean');
Route::get('/state', [TowerController::class, 'state'])->name('state');
Route::post('/move/{from}/{to}', [TowerController::class, 'move'])->name('move');

