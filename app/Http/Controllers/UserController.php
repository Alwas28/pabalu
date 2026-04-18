<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $user  = auth()->user();
        $query = User::with(['profile', 'roles'])->withCount('roles');

        // Owner hanya melihat staff di outlet mereka sendiri
        if ($user->isOwner()) {
            $outletIds = $user->accessibleOutlets()->pluck('id');
            $query->where(function ($q) use ($user, $outletIds) {
                $q->whereIn('outlet_id', $outletIds)
                  ->orWhere('id', $user->id);
            });
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('profile', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%"));
            });
        }

        if ($roleFilter = $request->get('role')) {
            $query->whereHas('roles', fn($r) => $r->where('name', $roleFilter));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        // Owner hanya bisa assign role kasir atau tanpa role
        $roles = $user->isOwner()
            ? Role::where('name', 'kasir')->get()
            : Role::orderBy('name')->get();

        if ($user->isOwner()) {
            $outletIds   = $user->accessibleOutlets()->pluck('id');
            $scopedUsers = User::where(function ($q) use ($user, $outletIds) {
                $q->whereIn('outlet_id', $outletIds)->orWhere('id', $user->id);
            });
            $countKasir = (clone $scopedUsers)->whereHas('roles', fn($q) => $q->where('name', 'kasir'))->count();
            $countOwner = (clone $scopedUsers)->whereHas('roles', fn($q) => $q->where('name', 'owner'))->count();
        } else {
            $countKasir = User::role('kasir')->count();
            $countOwner = User::role('owner')->count();
        }

        $stats = [
            'total'  => $query->toBase()->getCountForPagination(),
            'kasir'  => $countKasir,
            'owner'  => $countOwner,
        ];

        return view('users.index', compact('users', 'roles', 'stats'));
    }

    public function create(): View
    {
        $user    = auth()->user();
        $roles   = $user->isOwner()
            ? Role::where('name', 'kasir')->get()
            : Role::orderBy('name')->get();
        $outlets = $user->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        return view('users.create', compact('roles', 'outlets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedRoles = auth()->user()->isOwner() ? 'in:kasir' : 'exists:roles,name';

        $rules = [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', Password::defaults(), 'confirmed'],
            'role'      => ['nullable', 'string', $allowedRoles],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            // Profile
            'nama_lengkap'   => ['nullable', 'string', 'max:255'],
            'no_hp'          => ['nullable', 'string', 'max:20'],
            'jabatan'        => ['nullable', 'string', 'max:100'],
            'jenis_kelamin'  => ['nullable', 'in:L,P'],
            'tanggal_lahir'  => ['nullable', 'date'],
            'alamat'         => ['nullable', 'string', 'max:500'],
        ];

        $validated = $request->validate($rules);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'outlet_id' => $validated['outlet_id'] ?? null,
        ]);

        $user->profile()->create([
            'user_id'        => $user->id,
            'email'          => $user->email,
            'nama_lengkap'   => $validated['nama_lengkap'] ?? null,
            'no_hp'          => $validated['no_hp'] ?? null,
            'jabatan'        => $validated['jabatan'] ?? null,
            'jenis_kelamin'  => $validated['jenis_kelamin'] ?? null,
            'tanggal_lahir'  => $validated['tanggal_lahir'] ?? null,
            'alamat'         => $validated['alamat'] ?? null,
        ]);

        // Users created by admin are pre-verified — no email confirmation needed
        $user->markEmailAsVerified();

        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        ActivityLog::record('create_user',
            "User \"{$user->name}\" ({$user->email}) dibuat" . (!empty($validated['role']) ? " dengan role {$validated['role']}" : '') . ".",
            $user
        );

        return redirect()->route('users.index')
            ->with('success', "User \"{$user->name}\" berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        $user->load('profile', 'roles');
        $authUser = auth()->user();
        $roles    = $authUser->isOwner()
            ? Role::where('name', 'kasir')->get()
            : Role::orderBy('name')->get();
        $outlets  = $authUser->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        return view('users.edit', compact('user', 'roles', 'outlets'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $allowedRoles = auth()->user()->isOwner() ? 'in:kasir' : 'exists:roles,name';

        $rules = [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', Password::defaults(), 'confirmed'],
            'role'      => ['nullable', 'string', $allowedRoles],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            // Profile
            'nama_lengkap'   => ['nullable', 'string', 'max:255'],
            'no_hp'          => ['nullable', 'string', 'max:20'],
            'jabatan'        => ['nullable', 'string', 'max:100'],
            'jenis_kelamin'  => ['nullable', 'in:L,P'],
            'tanggal_lahir'  => ['nullable', 'date'],
            'alamat'         => ['nullable', 'string', 'max:500'],
        ];

        $validated = $request->validate($rules);

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'outlet_id' => $validated['outlet_id'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'email'          => $user->email,
                'nama_lengkap'   => $validated['nama_lengkap'] ?? null,
                'no_hp'          => $validated['no_hp'] ?? null,
                'jabatan'        => $validated['jabatan'] ?? null,
                'jenis_kelamin'  => $validated['jenis_kelamin'] ?? null,
                'tanggal_lahir'  => $validated['tanggal_lahir'] ?? null,
                'alamat'         => $validated['alamat'] ?? null,
            ]
        );

        if (auth()->check() && auth()->id() !== $user->id) {
            // Only users with user.assign can change roles
            if ($request->user()->can('user.assign')) {
                $user->syncRoles($validated['role'] ? [$validated['role']] : []);
            }
        }

        ActivityLog::record('update_user',
            "User \"{$user->name}\" ({$user->email}) diperbarui.",
            $user
        );

        return redirect()->route('users.index')
            ->with('success', "User \"{$user->name}\" berhasil diperbarui.");
    }

    public function ownerDetail(Request $request, User $user): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless($user->isOwner(), 404);

        $user->load(['profile', 'ownedOutlets']);

        $outlets    = $user->ownedOutlets()->withCount('products')->get();
        $outletIds  = $outlets->pluck('id');

        // Filter outlet (opsional)
        $filterOutletId = $request->get('outlet_id');
        $activeIds = $filterOutletId
            ? collect([$filterOutletId])
            : $outletIds;

        // ── Stat ringkasan ────────────────────────────────
        $totalOmzet = Transaction::whereIn('outlet_id', $activeIds)
            ->where('status', 'paid')->sum('total');
        $totalTrx   = Transaction::whereIn('outlet_id', $activeIds)
            ->where('status', 'paid')->count();

        // Hari aktif (hari yang punya minimal 1 transaksi)
        $hariAktif = Transaction::whereIn('outlet_id', $activeIds)
            ->where('status', 'paid')
            ->distinct()
            ->count(\Illuminate\Support\Facades\DB::raw('DATE(tanggal)'));
        $rataPerHari = $hariAktif > 0 ? round($totalOmzet / $hariAktif) : 0;

        // ── Grafik 7 hari terakhir ────────────────────────
        $chart7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $chart7[] = [
                'label' => $d->locale('id')->isoFormat('ddd, D MMM'),
                'omzet' => (float) Transaction::whereIn('outlet_id', $activeIds)
                    ->where('tanggal', $d->toDateString())
                    ->where('status', 'paid')->sum('total'),
                'trx'   => (int) Transaction::whereIn('outlet_id', $activeIds)
                    ->where('tanggal', $d->toDateString())
                    ->where('status', 'paid')->count(),
            ];
        }

        // ── Grafik 12 bulan terakhir ──────────────────────
        $chart12 = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::today()->startOfMonth()->subMonths($i);
            $chart12[] = [
                'label' => $m->locale('id')->isoFormat('MMM YY'),
                'omzet' => (float) Transaction::whereIn('outlet_id', $activeIds)
                    ->whereYear('tanggal', $m->year)
                    ->whereMonth('tanggal', $m->month)
                    ->where('status', 'paid')->sum('total'),
                'trx'   => (int) Transaction::whereIn('outlet_id', $activeIds)
                    ->whereYear('tanggal', $m->year)
                    ->whereMonth('tanggal', $m->month)
                    ->where('status', 'paid')->count(),
            ];
        }

        // ── Per-outlet stats ──────────────────────────────
        $outletStats = $outlets->map(function ($o) {
            $omzet = Transaction::where('outlet_id', $o->id)
                ->where('status', 'paid')->sum('total');
            $trx   = Transaction::where('outlet_id', $o->id)
                ->where('status', 'paid')->count();
            return array_merge($o->toArray(), [
                'omzet' => $omzet,
                'trx'   => $trx,
            ]);
        });

        return view('users.owner-detail', compact(
            'user', 'outlets', 'filterOutletId',
            'totalOmzet', 'totalTrx', 'rataPerHari', 'hariAktif',
            'chart7', 'chart12', 'outletStats'
        ));
    }

    public function verifyEmail(User $user): RedirectResponse
    {
        $auth = auth()->user();

        // Owner hanya boleh verifikasi user yang melekat pada outletnya
        if ($auth->isOwner()) {
            $ownerOutletIds = $auth->accessibleOutlets()->pluck('id');
            if (!$ownerOutletIds->contains($user->outlet_id)) {
                abort(403);
            }
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('info', "\"{$user->name}\" sudah terverifikasi.");
        }

        $user->markEmailAsVerified();

        ActivityLog::record('verify_email',
            "Email user \"{$user->name}\" ({$user->email}) diverifikasi.",
            $user
        );

        return back()->with('success', "Email \"{$user->name}\" berhasil diverifikasi.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('users.index')
                ->with('error', 'User dengan role Admin tidak dapat dihapus.');
        }

        $name  = $user->name;
        $email = $user->email;
        $user->delete();

        ActivityLog::record('delete_user', "User \"{$name}\" ({$email}) dihapus.");

        return redirect()->route('users.index')
            ->with('success', "User \"{$name}\" berhasil dihapus.");
    }
}
