<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->isAccountActive()) {
            return response()->json([
                'message' => 'Akun tidak aktif atau sudah kedaluwarsa.',
            ], 403);
        }

        if (! $user->hasAnyRole(['kasir', 'admin_outlet', 'owner', 'admin'])) {
            return response()->json([
                'message' => 'Akun tidak memiliki akses ke aplikasi kasir.',
            ], 403);
        }

        $deviceName = $request->device_name ?? 'mobile';

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName, ['kasir'])->plainTextToken;

        ActivityLog::record('api_login', "Login via API mobile ({$deviceName})");

        return response()->json([
            'token' => $token,
            'user'  => $this->buildUserPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->buildUserPayload($request->user()));
    }

    private function buildUserPayload(User $user): array
    {
        $role        = $user->getRoleNames()->first() ?? 'kasir';
        $isOwner     = $user->hasRole('owner');
        $isAdmin     = $user->hasRole('admin');
        $isKasir     = $user->hasRole('kasir');
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        // ── Feature flags — dibaca langsung oleh mobile untuk show/hide menu ──
        $features = [
            // Kasir & Owner
            'pos'              => true,                                      // Layar POS kasir
            'opening_stok'     => true,                                      // Input opening stok
            'tambah_stok'      => true,                                      // Tambah stok masuk
            'waste'            => true,                                      // Catat barang rusak
            'antrian_order'    => true,                                      // Lihat & kelola antrian
            'pengeluaran'      => true,                                      // Catat pengeluaran
            'closing'          => true,                                      // Ringkasan closing
            'riwayat_transaksi'=> true,                                      // Riwayat transaksi

            // Owner & Admin only
            'laporan'          => $isOwner || $isAdmin,                      // Laporan penjualan & L/R
            'kelola_produk'    => $isOwner || $isAdmin,                      // CRUD produk
            'kelola_user'      => $isOwner || $isAdmin,                      // CRUD user
            'kelola_outlet'    => $isAdmin,                                  // CRUD outlet (admin saja)

            // Batasan kasir
            'lihat_semua_transaksi' => $isOwner || $isAdmin,                 // Kasir hanya lihat miliknya
            'lihat_semua_expense'   => $isOwner || $isAdmin,                 // Kasir hanya lihat miliknya
            'lihat_expense_laporan' => $isOwner || $isAdmin,                 // Expense di L/R tersembunyi untuk kasir
            'multi_outlet'          => $isOwner || $isAdmin,                 // Pilih outlet (kasir: fixed)
        ];

        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'role'         => $role,
            'outlet_id'    => $user->outlet_id,
            'account_type' => $user->account_type,
            'permissions'  => $permissions,
            'features'     => $features,
        ];
    }
}
