<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\EmployeeSalaryPayment;
use App\Models\Expense;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    private function ownerOutlets(): \Illuminate\Database\Eloquent\Collection
    {
        /** @var User $owner */
        $owner = Auth::user();

        if ($owner->role === 'admin') {
            return Outlet::orderBy('name')->get();
        }

        return $owner->outlets()->orderBy('name')->get();
    }

    public function mySalary(Request $request): View
    {
        /** @var User $user */
        $user     = Auth::user();
        $employee = Employee::where('user_id', $user->id)
            ->with(['salaries' => fn($q) => $q->orderByDesc('effective_date')->orderByDesc('id')])
            ->first();

        $payments = collect();
        $payYear  = $request->input('pay_year', now()->year);
        $payMonth = $request->input('pay_month', '');

        if ($employee) {
            $payments = $employee->salaryPayments()
                ->with('creator')
                ->whereYear('paid_at', $payYear)
                ->when($payMonth, fn($q) => $q->whereMonth('paid_at', $payMonth))
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->get();
        }

        $activeTab = $request->input('tab', 'master');

        return view('my-salary', compact('user', 'employee', 'payments', 'payYear', 'payMonth', 'activeTab'));
    }

    private function authorizeOwner(): User
    {
        /** @var User $owner */
        $owner = Auth::user();
        abort_unless(in_array($owner->role, ['admin', 'owner']), 403);
        return $owner;
    }

    private function resolveEmployee(int|string $id): Employee
    {
        $owner = Auth::user();
        $employee = Employee::findOrFail($id);

        if ($owner->role !== 'admin' && (int) $employee->owner_id !== (int) $owner->id) {
            abort(403);
        }

        return $employee;
    }

    public function index(): View
    {
        $owner = $this->authorizeOwner();

        $employees = Employee::with(['user', 'outlet'])
            ->where(function ($q) use ($owner) {
                if ($owner->role !== 'admin') {
                    $q->where('owner_id', $owner->id);
                }
            })
            ->orderBy('name')
            ->get();

        $outlets = $this->ownerOutlets();

        return view('employees.index', compact('owner', 'employees', 'outlets'));
    }

    public function show(Request $request, int $employee): View
    {
        $this->authorizeOwner();
        $employee = $this->resolveEmployee($employee);
        $employee->load(['user', 'salaries' => fn($q) => $q->orderByDesc('effective_date')->orderByDesc('id')]);

        $payYear  = $request->input('pay_year', now()->year);
        $payMonth = $request->input('pay_month', '');

        $payments = $employee->salaryPayments()
            ->with('creator')
            ->whereYear('paid_at', $payYear)
            ->when($payMonth, fn($q) => $q->whereMonth('paid_at', $payMonth))
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        $activeTab = $request->input('tab', 'master');

        return view('employees.show', compact('employee', 'payments', 'payYear', 'payMonth', 'activeTab'));
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = $this->authorizeOwner();

        $outletIds = $this->ownerOutlets()->pluck('id')->toArray();

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'outlet_id'       => ['nullable', 'integer', 'in:' . implode(',', $outletIds ?: [0])],
            'position'        => ['nullable', 'string', 'max:100'],
            'whatsapp'        => ['nullable', 'string', 'max:25'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'education_level' => ['nullable', 'in:SD,SMP,SMA,D3,S1,S2,S3'],
            'join_date'       => ['nullable', 'date'],
            'notes'           => ['nullable', 'string', 'max:2000'],
            'photo'           => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $data['owner_id'] = $owner->id;

        Employee::create($data);

        return back()->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $owner    = $this->authorizeOwner();
        $employee = $this->resolveEmployee($id);

        $outletIds = $this->ownerOutlets()->pluck('id')->toArray();

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'outlet_id'       => ['nullable', 'integer', 'in:' . implode(',', $outletIds ?: [0])],
            'position'        => ['nullable', 'string', 'max:100'],
            'whatsapp'        => ['nullable', 'string', 'max:25'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'education_level' => ['nullable', 'in:SD,SMP,SMA,D3,S1,S2,S3'],
            'join_date'       => ['nullable', 'date'],
            'notes'           => ['nullable', 'string', 'max:2000'],
            'photo'           => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee->update($data);

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeOwner();
        $employee = $this->resolveEmployee($id);

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $this->authorizeOwner();
        $employee = $this->resolveEmployee($id);

        $employee->update(['is_active' => !$employee->is_active]);
        $status = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Karyawan berhasil {$status}.");
    }

    public function storeSalary(Request $request, int $id): RedirectResponse
    {
        $owner = $this->authorizeOwner();
        $employee = $this->resolveEmployee($id);

        $data = $request->validate([
            'amount'         => ['required', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string', 'max:255'],
            'effective_date' => ['required', 'date'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $isActive = !empty($data['is_active']);

        DB::transaction(function () use ($data, $employee, $owner, $isActive) {
            if ($isActive) {
                $employee->salaries()->update(['is_active' => false]);
            }
            EmployeeSalary::create([
                'employee_id'    => $employee->id,
                'amount'         => $data['amount'],
                'notes'          => $data['notes'] ?? null,
                'effective_date' => $data['effective_date'],
                'is_active'      => $isActive,
                'created_by'     => $owner->id,
            ]);
        });

        return back()->with('success', 'Master gaji berhasil ditambahkan.');
    }

    public function activateSalary(int $employeeId, int $salaryId): RedirectResponse
    {
        $this->authorizeOwner();
        $employee = $this->resolveEmployee($employeeId);

        DB::transaction(function () use ($employee, $salaryId) {
            $employee->salaries()->update(['is_active' => false]);
            $employee->salaries()->where('id', $salaryId)->update(['is_active' => true]);
        });

        return back()->with('success', 'Gaji aktif diperbarui.');
    }

    public function destroySalary(int $employeeId, int $salaryId): RedirectResponse
    {
        $this->authorizeOwner();
        $employee = $this->resolveEmployee($employeeId);

        $salary = EmployeeSalary::where('employee_id', $employee->id)->findOrFail($salaryId);
        $salary->delete();

        return back()->with('success', 'Entri gaji dihapus.');
    }

    public function createSalaryPayment(int $id): View
    {
        $owner    = $this->authorizeOwner();
        $employee = $this->resolveEmployee($id);
        $employee->load(['outlet', 'salaries' => fn($q) => $q->where('is_active', true)]);

        $outlets = $owner->role === 'admin'
            ? Outlet::where('is_active', true)->orderBy('name')->get()
            : $owner->outlets()->where('is_active', true)->orderBy('name')->get();

        $activeSalary    = $employee->salaries->first();
        $defaultOutletId = $employee->outlet_id;

        return view('employees.salary-payment-create', compact(
            'employee', 'outlets', 'activeSalary', 'owner', 'defaultOutletId'
        ));
    }

    public function storeSalaryPayment(Request $request, int $id): RedirectResponse
    {
        $owner    = $this->authorizeOwner();
        $employee = $this->resolveEmployee($id);

        // Daftar outlet yang valid untuk owner ini
        $validOutletIds = $owner->role === 'admin'
            ? Outlet::pluck('id')->toArray()
            : $owner->outlets()->pluck('outlets.id')->toArray();

        $data = $request->validate([
            'outlet_id'    => ['required', 'integer', 'in:' . implode(',', $validOutletIds)],
            'amount'       => ['required', 'integer', 'min:1'],
            'period_month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'paid_at'      => ['required', 'date'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data, $employee, $owner) {
            // Buat expense di outlet terkait
            $periodLabel = (new EmployeeSalaryPayment(['period_month' => $data['period_month']]))->period_label;
            $expense = Expense::create([
                'outlet_id'   => $data['outlet_id'],
                'user_id'     => $owner->id,
                'date'        => $data['paid_at'],
                'category'    => 'gaji',
                'description' => 'Gaji ' . $employee->name . ' - ' . $periodLabel,
                'amount'      => $data['amount'],
                'notes'       => $data['notes'] ?? null,
            ]);

            // Catat pembayaran gaji
            EmployeeSalaryPayment::create([
                'employee_id'  => $employee->id,
                'outlet_id'    => $data['outlet_id'],
                'amount'       => $data['amount'],
                'period_month' => $data['period_month'],
                'paid_at'      => $data['paid_at'],
                'notes'        => $data['notes'] ?? null,
                'created_by'   => $owner->id,
                'expense_id'   => $expense->id,
            ]);
        });

        return redirect()->route('employees.show', [$employee, 'tab' => 'payments'])
            ->with('success', 'Gaji berhasil dicatat dan pengeluaran outlet telah dibuat.');
    }

    public function destroySalaryPayment(int $employeeId, int $paymentId): RedirectResponse
    {
        $this->authorizeOwner();
        $employee = $this->resolveEmployee($employeeId);

        $payment = EmployeeSalaryPayment::where('employee_id', $employee->id)->findOrFail($paymentId);

        DB::transaction(function () use ($payment) {
            // Hapus expense terkait jika ada
            if ($payment->expense_id) {
                Expense::find($payment->expense_id)?->delete();
            }
            $payment->delete();
        });

        return back()->with('success', 'Catatan pembayaran gaji dan pengeluaran outlet terkait dihapus.');
    }

    public function updateAccount(Request $request, int $id): RedirectResponse
    {
        $this->authorizeOwner();
        $employee = $this->resolveEmployee($id);

        if (!$employee->user_id) {
            return back()->withErrors(['account_email' => 'Karyawan belum memiliki akun.']);
        }

        $user = $employee->user;
        $outlets = $this->ownerOutlets();
        $outletIds = $outlets->pluck('id')->toArray();

        $data = $request->validate([
            'email'        => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'password'     => ['nullable', 'string', 'min:6'],
            'role'         => ['required', 'in:kasir,admin_outlet'],
            'outlet_ids'   => ['required', 'array', 'min:1'],
            'outlet_ids.*' => ['integer', 'in:' . implode(',', $outletIds)],
        ], [
            'outlet_ids.required' => 'Pilih minimal satu outlet.',
            'outlet_ids.*.in'     => 'Outlet tidak valid.',
        ]);

        DB::transaction(function () use ($data, $user, $outletIds) {
            $update = [
                'email' => $data['email'],
                'role'  => $data['role'],
            ];
            if (!empty($data['password'])) {
                $update['password'] = Hash::make($data['password']);
            }
            $user->update($update);

            $current = $user->assignedOutlets()->pluck('outlets.id')->toArray();
            $keep    = array_diff($current, $outletIds);
            $user->assignedOutlets()->sync(array_merge($keep, $data['outlet_ids']));
        });

        return back()->with('success', 'Akun karyawan berhasil diperbarui.');
    }

    public function createAccount(Request $request, int $id): RedirectResponse
    {
        $this->authorizeOwner();
        $employee = $this->resolveEmployee($id);

        if ($employee->user_id) {
            return back()->withErrors(['account' => 'Karyawan sudah memiliki akun.']);
        }

        $outlets = $this->ownerOutlets();
        $outletIds = $outlets->pluck('id')->toArray();

        $data = $request->validate([
            'email'        => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:6'],
            'role'         => ['required', 'in:kasir,admin_outlet'],
            'outlet_ids'   => ['required', 'array', 'min:1'],
            'outlet_ids.*' => ['integer', 'in:' . implode(',', $outletIds)],
        ], [
            'outlet_ids.required' => 'Pilih minimal satu outlet.',
            'outlet_ids.*.in'     => 'Outlet tidak valid.',
        ]);

        DB::transaction(function () use ($data, $employee) {
            $user = User::create([
                'name'              => $employee->name,
                'email'             => $data['email'],
                'password'          => Hash::make($data['password']),
                'role'              => $data['role'],
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);

            $user->assignedOutlets()->sync($data['outlet_ids']);
            $employee->update(['user_id' => $user->id]);
        });

        return back()->with('success', 'Akun karyawan berhasil dibuat.');
    }
}
