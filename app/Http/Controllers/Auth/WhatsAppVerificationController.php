<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\WhatsAppVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WhatsAppVerificationController extends Controller
{
    public function verify(Request $request, WhatsAppVerificationService $service): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.size' => 'Kode harus 6 digit.',
        ]);

        $result = $service->verifyCode($request->user(), $data['code']);

        if (!$result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        return redirect()->route('dashboard', ['verified' => 1]);
    }

    // Kirim ulang kode WA — dipisah dari EmailVerificationNotificationController
    // (yang tetap ada apa adanya buat fallback "verifikasi lewat email saja").
    public function resend(Request $request, WhatsAppVerificationService $service): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $sent = $service->sendCode($user);

        return back()->with(
            $sent ? 'status' : 'wa_resend_error',
            $sent ? 'wa-verification-sent' : 'Gagal mengirim kode ke WhatsApp. Coba lagi, atau pakai verifikasi email di bawah.'
        );
    }
}
