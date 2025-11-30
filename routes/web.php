<?php

use App\Http\Controllers\EventLogoController;
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

    // Event logo upload routes (admin only)
    Route::post('/admin/events/{event}/logo/upload', [EventLogoController::class, 'upload'])
        ->name('admin.events.logo.upload');

    Route::delete('/admin/events/{event}/logo/delete', [EventLogoController::class, 'delete'])
        ->name('admin.events.logo.delete');
});

// Payment callback (public - webhook from Mercado Pago)
Route::post('/payment/callback', [PaymentController::class, 'callback'])
    ->name('payment.callback');
