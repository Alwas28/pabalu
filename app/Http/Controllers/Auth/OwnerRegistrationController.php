<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class OwnerRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-owner');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'account_type'  => 'trial',
            'trial_ends_at' => now()->addDays(30),
        ]);

        $user->assignRole('owner');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
