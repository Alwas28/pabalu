<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Auto-login jika belum login, atau sudah login sebagai user yang sama
        if (!Auth::check()) {
            Auth::login($user);
        }

        return redirect()->route('dashboard', ['verified' => 1]);
    }
}
