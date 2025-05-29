<?php

use Illuminate\Http\Request;
use App\Services\AccurateService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HargaController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccurateController;
use App\Http\Controllers\CredentialController;

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

Route::get('/', [CredentialController::class, 'edit'])->middleware('auth');

// Tampilkan halaman dashboard (GET)
Route::get('/dashboard', [CredentialController::class, 'edit'])
    ->middleware(['auth'])
    ->name('dashboard');

// Simpan data credential (POST)
Route::post('/dashboard', [CredentialController::class, 'store'])
    ->middleware(['auth'])
    ->name('dashboard.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/accurate/login', [AccurateController::class, 'redirectToAccurate'])->name('accurate.login');
    Route::get('/accurate/callback', [AccurateController::class, 'handleCallback']);
    Route::get('/accurate/customers', [AccurateController::class, 'getCustomers']);
    Route::get('/accurate/database', [AccurateController::class, 'getDb']);
    Route::get('/accurate/items', [AccurateController::class, 'getItems'])->name('get.item');

    Route::post('/produk/print-pdf', [AccurateController::class, 'printPDF'])->name('produk.print-pdf');
    Route::get('/accurate/analisa-harga-terakhir', [AccurateController::class, 'analisaHargaTerakhir'])
        ->name('accurate.analisaHargaTerakhir');

    Route::post('/update/harga', [HargaController::class, 'store'])->name('sales.adjustment');

    Route::get('/export-produk', [ExportController::class, 'exportProduk']);

    Route::get('/penyesuaian/import', [HargaController::class, 'showImportForm'])->name('penyesuaian.import.form');
    Route::post('/penyesuaian/import', [HargaController::class, 'import'])->name('penyesuaian.import');
});


require __DIR__ . '/auth.php';
