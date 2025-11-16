<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Payment routes
Route::middleware(['auth'])->group(function () {
    Route::post('/payment/process/{package}', [PaymentController::class, 'process'])
        ->name('payment.process');
    
    Route::get('/payment/success/{package}', [PaymentController::class, 'success'])
        ->name('payment.success');
    
    Route::get('/payment/failure/{package}', [PaymentController::class, 'failure'])
        ->name('payment.failure');
    
    Route::get('/payment/pending/{package}', [PaymentController::class, 'pending'])
        ->name('payment.pending');
});

// Payment callback (public - webhook from Mercado Pago)
Route::post('/payment/callback', [PaymentController::class, 'callback'])
    ->name('payment.callback');
