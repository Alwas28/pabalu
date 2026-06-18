<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    // Roles yang boleh mengelola user
    private const MANAGER_ROLES = ['admin', 'owner'];

    private function authorizeManager(): void
    {
        if (!in_array(Auth::user()->role, self::MANAGER_ROLES)) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat melakukan aksi ini.');
        }
    }

    // Role yang boleh dibuat oleh owner (tidak boleh buat admin/owner lain)
    private function allowedRoles(): array
    {
        return Auth::user()->role === 'admin'
            ? ['admin', 'owner', 'admin_outlet', 'kasir']
            : ['admin_outlet', 'kasir'];
    }

    public function index(): View
    {
        $this->authorizeManager();

        $users = User::when(Auth::user()->role !== 'admin', function ($q) {
                // Owner hanya lihat user non-admin dan non-owner
                $q->whereNotIn('role', ['admin', 'owner']);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorizeManager();

        return view('users.create', [
            'allowedRoles' => $this->allowedRoles(),
        ]);
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('users.edit', $user);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'role'     => ['required', 'in:' . implode(',', $this->allowedRoles())],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        // Semua user yang dibuat admin → email otomatis terverifikasi (bukan self-register)
        $user->markEmailAsVerified();

        return redirect()->route('users.index')
            ->with('success', "User \"{$user->name}\" berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        $this->authorizeManager();

        // Owner tidak boleh edit admin/owner lain
        if (Auth::user()->role !== 'admin' && in_array($user->role, ['admin', 'owner'])) {
            abort(403, 'Akses ditolak.');
        }

        return view('users.edit', [
            'user'         => $user,
            'allowedRoles' => $this->allowedRoles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManager();

        if (Auth::user()->role !== 'admin' && in_array($user->role, ['admin', 'owner'])) {
            abort(403, 'Akses ditolak.');
        }

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'role'  => ['required', 'in:' . implode(',', $this->allowedRoles())],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        // Jika role diubah ke admin dan belum terverifikasi → auto-verify
        if ($validated['role'] === 'admin' && !$user->fresh()->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('users.index')
            ->with('success', "User \"{$user->name}\" berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeManager();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        if (Auth::user()->role !== 'admin' && in_array($user->role, ['admin', 'owner'])) {
            abort(403, 'Akses ditolak.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User \"{$name}\" berhasil dihapus.");
    }

    // Admin verifikasi email user secara manual
    public function verify(User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($user->hasVerifiedEmail()) {
            return back()->with('info', "{$user->name} sudah terverifikasi.");
        }

        $user->markEmailAsVerified();

        return back()->with('success', "Email \"{$user->name}\" berhasil diverifikasi.");
    }

    // Admin toggle aktif/nonaktif user
    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User \"{$user->name}\" berhasil {$status}.");
    }
}
