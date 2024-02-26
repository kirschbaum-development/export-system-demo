<?php

use App\Http\Controllers\DownloadExportController;
use App\Livewire\Table;
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

Route::get('/', Table::class);
Route::get('/exports/{fileName}', DownloadExportController::class)
    ->middleware(['signed'])
    ->name('exports.show');
