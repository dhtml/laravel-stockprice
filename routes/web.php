<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::match(['post'], '/add/stock', [StockController::class, 'addItem'])->name('add-stock-item');
Route::match(['put'], '/edit/stock', [StockController::class, 'editItem'])->name('edit-stock-item');

Route::match(['get'], '/all/stocks', [StockController::class, 'fetchAll'])->name('fetch-stock-items');
Route::match(['get'], '/stock/{path}', [StockController::class, 'getStock'])->name('get-stock-item');
