<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\WhatsAppVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('dynamic.throttle:register');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // Percobaan login gagal per email+IP juga dibatasi di dalam LoginRequest
    // (App\Http\Requests\Auth\LoginRequest::ensureIsNotRateLimited(), pakai profil
    // "login" yang sama) — middleware ini lapisan tambahan per-IP.
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('dynamic.throttle:login');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('dynamic.throttle:otp')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('dynamic.throttle:otp')
        ->name('verification.send');

    Route::post('verify-whatsapp', [WhatsAppVerificationController::class, 'verify'])
        ->middleware('dynamic.throttle:otp')
        ->name('verification.whatsapp.verify');

    Route::post('verify-whatsapp/resend', [WhatsAppVerificationController::class, 'resend'])
        ->middleware('dynamic.throttle:otp')
        ->name('verification.whatsapp.resend');
});

// Verifikasi email bisa diakses tanpa login — auto-login setelah verifikasi
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'dynamic.throttle:otp'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
