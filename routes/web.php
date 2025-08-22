<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeleteScanProduksiController;

Route::get('/', function () {
    return redirect('/admin');
});

// Route untuk Terminal Log Interface
Route::get('/terminal-log', function () {
    return view('terminal-log');
})->name('terminal-log');

// Route untuk Delete Scan Produksi
Route::get('/del-scan-prod', [DeleteScanProduksiController::class, 'index'])->name('delete-scan-produksi.index');
Route::post('/del-scan-prod', [DeleteScanProduksiController::class, 'execute'])->name('delete-scan-produksi.execute');
