<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserApiController extends Controller
{
    // ── Daftar User ───────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $query = User::with(['profile', 'roles']);

        // Owner hanya melihat staff di outletnya sendiri
        if ($authUser->isOwner()) {
            $outletIds = $authUser->accessibleOutlets()->pluck('id');
            $query->where(fn($q) =>
                $q->whereIn('outlet_id', $outletIds)->orWhere('id', $authUser->id)
            );
        }

        if ($search = $request->q) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            );
        }

        if ($role = $request->role) {
            $query->whereHas('roles', fn($r) => $r->where('name', $role));
        }

        $users = $query->latest()->get()->map(fn($u) => [
            'id'        => $u->id,
            'name'      => $u->name,
            'email'     => $u->email,
            'outlet_id' => $u->outlet_id,
            'roles'     => $u->roles->pluck('name'),
            'is_active' => $u->is_active ?? true,
            'jabatan'   => $u->profile?->jabatan,
            'no_hp'     => $u->profile?->no_hp,
        ]);

        // Roles yang boleh diassign oleh caller
        $availableRoles = $authUser->isOwner()
            ? Role::where('name', 'kasir')->pluck('name')
            : Role::orderBy('name')->pluck('name');

        return response()->json(['users' => $users, 'available_roles' => $availableRoles]);
    }

    // ── Tambah User ───────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $authUser     = $request->user();
        $allowedRoles = $authUser->isOwner() ? 'in:kasir' : 'exists:roles,name';

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', Password::defaults()],
            'role'      => ['nullable', 'string', $allowedRoles],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'jabatan'   => ['nullable', 'string', 'max:100'],
            'no_hp'     => ['nullable', 'string', 'max:20'],
        ]);

        // Owner hanya boleh assign ke outletnya sendiri
        if ($authUser->isOwner() && $request->outlet_id) {
            $ok = $authUser->accessibleOutlets()->where('id', $request->outlet_id)->exists();
            if (! $ok) {
                return response()->json(['message' => 'Tidak punya akses ke outlet ini.'], 403);
            }
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'outlet_id' => $request->outlet_id,
        ]);

        $user->profile()->create([
            'user_id'  => $user->id,
            'email'    => $user->email,
            'jabatan'  => $request->jabatan,
            'no_hp'    => $request->no_hp,
        ]);

        $user->markEmailAsVerified();

        if ($request->role) {
            $user->assignRole($request->role);
        }

        ActivityLog::record('create_user',
            "User \"{$user->name}\" ({$user->email}) dibuat via API.",
            $user
        );

        return response()->json(['message' => "User \"{$user->name}\" berhasil ditambahkan.", 'id' => $user->id], 201);
    }

    // ── Update User ───────────────────────────────────────
    public function update(Request $request, User $user): JsonResponse
    {
        $authUser     = $request->user();
        $allowedRoles = $authUser->isOwner() ? 'in:kasir' : 'exists:roles,name';

        // Owner hanya boleh edit user di outletnya
        if ($authUser->isOwner() && $authUser->id !== $user->id) {
            $outletIds = $authUser->accessibleOutlets()->pluck('id');
            if (! $outletIds->contains($user->outlet_id)) {
                return response()->json(['message' => 'Tidak punya akses ke user ini.'], 403);
            }
        }

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', Password::defaults()],
            'role'      => ['nullable', 'string', $allowedRoles],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'jabatan'   => ['nullable', 'string', 'max:100'],
            'no_hp'     => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'outlet_id' => $request->outlet_id,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['email' => $user->email, 'jabatan' => $request->jabatan, 'no_hp' => $request->no_hp]
        );

        if ($authUser->can('user.assign') && $authUser->id !== $user->id) {
            $user->syncRoles($request->role ? [$request->role] : []);
        }

        ActivityLog::record('update_user', "User \"{$user->name}\" diperbarui via API.", $user);

        return response()->json(['message' => "User \"{$user->name}\" berhasil diperbarui."]);
    }

    // ── Hapus User ────────────────────────────────────────
    public function destroy(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if ($user->id === $authUser->id) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 422);
        }

        if ($user->hasRole('admin')) {
            return response()->json(['message' => 'User dengan role Admin tidak dapat dihapus.'], 422);
        }

        // Owner hanya boleh hapus user di outletnya
        if ($authUser->isOwner()) {
            $outletIds = $authUser->accessibleOutlets()->pluck('id');
            if (! $outletIds->contains($user->outlet_id)) {
                return response()->json(['message' => 'Tidak punya akses ke user ini.'], 403);
            }
        }

        $name = $user->name;
        ActivityLog::record('delete_user', "User \"{$name}\" ({$user->email}) dihapus via API.");
        $user->delete();

        return response()->json(['message' => "User \"{$name}\" berhasil dihapus."]);
    }
}
