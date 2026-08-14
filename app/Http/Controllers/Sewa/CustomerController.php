<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }
        return $user;
    }

    private function authorizeCustomer(Outlet $outlet, Customer $customer): void
    {
        if ((int) $customer->outlet_id !== (int) $outlet->id) {
            abort(404);
        }
    }

    private function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:120'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'email'      => ['nullable', 'email', 'max:150'],
            'address'    => ['nullable', 'string', 'max:255'],
            'city'       => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender'     => ['nullable', 'in:L,P'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $this->authorizeOutlet($outlet);

        $q = trim((string) $request->input('q', ''));

        $customers = Customer::where('outlet_id', $outlet->id)
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('sewa.customers.index', compact('outlet', 'customers', 'q'));
    }

    public function create(Outlet $outlet): View
    {
        $this->authorizeOutlet($outlet);

        return view('sewa.customers.create', compact('outlet'));
    }

    public function show(Outlet $outlet, Customer $customer): View
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeCustomer($outlet, $customer);

        $rentals = $customer->rentalTransactions()
            ->with('rentalUnit.rentalItem')
            ->orderByDesc('start_at')
            ->get();

        $stats = [
            'total_rentals'  => $rentals->count(),
            'total_spent'    => $rentals->sum('total_amount'),
            'late_count'     => $rentals->filter(fn ($r) => $r->status === 'selesai' && $r->returned_at && $r->returned_at->gt($r->end_at))->count(),
            'damage_count'   => $rentals->where('fine_amount', '>', 0)->count(),
            'active_rental'  => $rentals->firstWhere('status', 'aktif'),
        ];

        $requirements   = $outlet->documentRequirements()->orderBy('sort_order')->orderBy('name')->get();
        $customerDocs   = $customer->documents()->get()->keyBy('rental_document_requirement_id');
        $isFullyVerified = $customer->hasVerifiedAllRequiredDocuments();

        return view('sewa.customers.show', compact('outlet', 'customer', 'rentals', 'stats', 'requirements', 'customerDocs', 'isFullyVerified'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        $data = $request->validate($this->rules());
        $data['outlet_id'] = $outlet->id;

        Customer::create($data);

        return redirect($outlet->route('customers.index'))->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    /** Tambah pelanggan cepat (AJAX) — hanya nama wajib, dipakai dari halaman Sewa Baru agar kasir tidak perlu pindah halaman. */
    public function quickStore(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorizeOutlet($outlet);

        if ($outlet->documentRequirements()->where('status', 'wajib')->exists()) {
            return response()->json([
                'message' => 'Outlet ini punya persyaratan dokumen wajib — pelanggan harus didaftarkan lewat form lengkap dan diverifikasi dokumennya terlebih dahulu.',
            ], 422);
        }

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
        $data['outlet_id'] = $outlet->id;

        $customer = Customer::create($data);

        return response()->json([
            'id'    => $customer->id,
            'name'  => $customer->name,
            'phone' => $customer->phone,
        ]);
    }

    public function update(Request $request, Outlet $outlet, Customer $customer): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeCustomer($outlet, $customer);

        $data = $request->validate($this->rules());
        $customer->update($data);

        return back()->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet, Customer $customer): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeCustomer($outlet, $customer);

        $customer->delete();

        return back()->with('success', 'Pelanggan berhasil dihapus.');
    }
}
