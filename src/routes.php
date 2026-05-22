<?php

// Optional routes for the package
// You can add webhook routes here if needed

Route::prefix('api/mpesa')->group(function () {
    // These routes will be published if you want
    // Route::post('/stk-callback', [MpesaCallbackController::class, 'stkCallback'])->name('mpesa.stk-callback');
    // Route::post('/c2b-confirmation', [MpesaCallbackController::class, 'c2bConfirmation'])->name('mpesa.c2b-confirmation');
    // Route::post('/c2b-validation', [MpesaCallbackController::class, 'c2bValidation'])->name('mpesa.c2b-validation');
});