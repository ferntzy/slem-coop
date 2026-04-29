<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanApplicationPrintController;
use App\Http\Controllers\Notifications;
use App\Http\Controllers\OrientationController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});
Route::get('/api/auth-status', function () {
    return response()->json(['authenticated' => auth()->check()]);
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Complete Registration
Route::get('/register/complete', function () {
    return view('welcome'); // React SPA handles the UI
});

Route::post('/register/complete', [AuthController::class, 'completeRegistration']);

Route::middleware('auth')->group(function () {
    Route::post('/coop/fetch-notification', [Notifications::class, 'WebfetchNotifications']);
    Route::post('/coop/delete-notification', [Notifications::class, 'WebdeleteNotification']);
    Route::post('/coop/mark-notification-read', [Notifications::class, 'WebmarkAsRead']);
    Route::post('/coop/mark-all-notifications-read', [Notifications::class, 'WebmarkAllAsRead']);
});

Route::get('/', function () {
    return view('welcome');
});

Route::view('/scanner', 'qr-scanner');

Route::get('/loan-applications/{loanApplication}/print', [LoanApplicationPrintController::class, 'print'])
    ->name('loan-applications.print');

Route::get('/loan-applications/{loanApplication}/pdf', [LoanApplicationPrintController::class, 'download'])
    ->name('loan-applications.pdf');

Route::view('/500', 'welcome');

Route::redirect('/coop/login', '/login');

Route::get('/test-500', function () {
    throw new Exception('Test error');
});

Route::prefix('orientation')->group(function () {

    Route::get('/', [OrientationController::class, 'show']);
    Route::get('/status', [OrientationController::class, 'status']);
    Route::post('/video-watched', [OrientationController::class, 'markVideoWatched']);
    Route::post('/submit', [OrientationController::class, 'submit']);
    Route::get('/certificate', [OrientationController::class, 'downloadCertificate']);
});
Route::middleware(['auth'])->group(function () {
    Route::get('/receipts/{record}/download', [ReceiptController::class, 'download'])->name('receipt.download');
    Route::get('/receipts/{record}/print', [ReceiptController::class, 'print'])->name('receipt.print');
});

Route::get('/{path?}', function () {
    return view('welcome');
})->where('path', '.*')->name('spa');
