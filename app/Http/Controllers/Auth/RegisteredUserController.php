<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Turnstile;
use App\Services\WhatsApp\WhatsAppVerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone'    => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms'    => ['required', 'accepted'],
            'cf-turnstile-response' => Turnstile::rules(),
        ], [
            'cf-turnstile-response.required' => 'Verifikasi keamanan (captcha) belum diselesaikan. Coba lagi.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'owner',
        ]);

        // Coba kirim kode verifikasi lewat WhatsApp dulu — kalau gateway belum
        // dikonfigurasi/nonaktif atau pengiriman gagal, event Registered tetap
        // di-fire supaya listener bawaan Laravel kirim link verifikasi ke email
        // seperti biasa. Jangan pernah kirim KEDUANYA sekaligus.
        $sentViaWhatsApp = app(WhatsAppVerificationService::class)->sendCode($user);

        if (!$sentViaWhatsApp) {
            event(new Registered($user));
        }

        Auth::login($user);

        return redirect(route('verification.notice'));
    }
}
