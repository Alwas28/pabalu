<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcesKasirLimit;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    use EnforcesKasirLimit;

    // Roles yang boleh mengelola user
    private const MANAGER_ROLES = ['admin', 'owner'];

    // Karyawan yang boleh dihubungkan ke user baru dari sini — punya karyawan tapi
    // belum ada akun login (user_id null), dibatasi ke karyawan milik owner yang sama
    // (admin bebas semua), sama seperti scoping di EmployeeController::index().
    private function unlinkedEmployees(): \Illuminate\Database\Eloquent\Collection
    {
        $owner = Auth::user();

        return Employee::whereNull('user_id')
            ->when($owner->role !== 'admin', fn ($q) => $q->where('owner_id', $owner->id))
            ->orderBy('name')
            ->get();
    }

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

    // Owner hanya boleh mengelola user (kasir/admin_outlet) yang dia buat sendiri,
    // atau yang sudah ditugaskan di salah satu outlet miliknya. Admin bebas akses semua user.
    private function ownsUser(User $actor, User $user): bool
    {
        if ($user->created_by === $actor->id) {
            return true;
        }

        return $user->assignedOutlets()->where('owner_id', $actor->id)->exists();
    }

    private function resolveManagedUser(User $user): User
    {
        $actor = Auth::user();

        if ($actor->role !== 'admin' && !$this->ownsUser($actor, $user)) {
            abort(403, 'Akses ditolak.');
        }

        return $user;
    }

    // Role yang boleh dibuat oleh owner (tidak boleh buat admin/owner lain)
    private function allowedRoles(): array
    {
        return Auth::user()->role === 'admin'
            ? ['admin', 'owner', 'admin_outlet', 'kasir']
            : ['admin_outlet', 'kasir'];
    }

    // max_kasir sekarang berarti batas PER OUTLET (lihat EmployeeController::
    // outletKasirLimitMessage()), bukan total akun di seluruh akun owner — akun yang
    // dibuat lewat halaman ini belum terikat outlet manapun (outlet_ids diisi belakangan
    // lewat menu Karyawan), jadi tidak ada konteks outlet untuk ditegakkan di sini.
    public function index(): View
    {
        $this->authorizeManager();

        $owner = Auth::user();

        $users = User::when($owner->role !== 'admin', function ($q) use ($owner) {
                // Owner hanya lihat user non-admin/non-owner yang dia buat sendiri
                // atau yang sudah ditugaskan ke salah satu outlet miliknya.
                $q->whereHas('roleRelation', fn($r) => $r->whereNotIn('slug', ['admin', 'owner']))
                  ->where(function ($q2) use ($owner) {
                      $q2->where('created_by', $owner->id)
                         ->orWhereHas('assignedOutlets', fn($r) => $r->where('owner_id', $owner->id));
                  });
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
            'employees'    => $this->unlinkedEmployees(),
        ]);
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('users.edit', $user);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager();

        $unlinkedIds = $this->unlinkedEmployees()->pluck('id')->toArray();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'role'        => ['required', 'in:' . implode(',', $this->allowedRoles())],
            'password'    => ['required', 'confirmed', Password::defaults()],
            'employee_id' => ['nullable', 'integer', 'in:' . implode(',', $unlinkedIds ?: [0])],
        ], [
            'employee_id.in' => 'Karyawan tidak valid atau sudah punya akun.',
        ]);

        // Menghubungkan ke karyawan cuma masuk akal untuk role operasional (kasir/admin_outlet)
        // — sama seperti batasan role di EmployeeController::createAccount().
        $employee = null;
        if (!empty($validated['employee_id']) && in_array($validated['role'], ['kasir', 'admin_outlet'], true)) {
            $employee = Employee::find($validated['employee_id']);

            if ($employee->outlet_id) {
                $outlet = $employee->outlet;
                if ($outlet && ($message = $this->outletKasirLimitMessage($outlet))) {
                    return back()->withErrors(['employee_id' => $message])->withInput();
                }
            }
        }

        $user = DB::transaction(function () use ($validated, $employee) {
            $user = User::create([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'role'       => $validated['role'],
                'password'   => Hash::make($validated['password']),
                'created_by' => Auth::id(),
            ]);

            if ($employee) {
                $employee->update(['user_id' => $user->id]);
                if ($employee->outlet_id) {
                    $user->assignedOutlets()->syncWithoutDetaching([$employee->outlet_id]);
                }
            }

            return $user;
        });

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

        $this->resolveManagedUser($user);

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

        $this->resolveManagedUser($user);

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'role'  => ['required', 'in:' . implode(',', $this->allowedRoles())],
        ];

        $validated = $request->validate($rules);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ]);

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

        $this->resolveManagedUser($user);

        $name = $user->name;

        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', "User \"{$name}\" tidak dapat dihapus karena masih memiliki data terkait (transaksi, shift, atau laporan).");
        }

        return redirect()->route('users.index')
            ->with('success', "User \"{$name}\" berhasil dihapus.");
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password \"{$user->name}\" berhasil diperbarui.");
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
