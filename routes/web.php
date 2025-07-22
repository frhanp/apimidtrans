<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
Route::get('/', function () {
    return view('welcome');
    
});
Route::post('/midtrans/webhook', [MidtransController::class, 'handleWebhook']);

// TAMBAHKAN ROUTE INI UNTUK TESTING
Route::get('/test', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Halo! Endpoint Anda berhasil diakses!'
    ]);
});

// Route untuk menampilkan halaman pembayaran
Route::get('/pay', [MidtransController::class, 'showPaymentPage']);

// Route untuk membuat transaksi (dipanggil oleh Javascript)
Route::post('/create-transaction', [MidtransController::class, 'createTransaction']);

Route::get('/transactions', [MidtransController::class, 'showTransactions']);