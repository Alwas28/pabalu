<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentController extends Controller
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

    /** Antrean verifikasi dokumen lintas-pelanggan — daftar semua pelanggan beserta kelengkapan dokumennya. */
    public function index(Request $request, Outlet $outlet): View
    {
        $this->authorizeOutlet($outlet);

        $requirements = $outlet->documentRequirements()->orderBy('sort_order')->orderBy('name')->get();
        $requiredCount = $requirements->where('status', 'wajib')->count();

        $status = in_array($request->query('status'), ['lengkap', 'belum'], true) ? $request->query('status') : null;

        $customers = Customer::where('outlet_id', $outlet->id)
            ->with(['documents.requirement'])
            ->orderBy('name')
            ->get();

        if ($status === 'lengkap') {
            $customers = $customers->filter(fn ($c) => $c->hasVerifiedAllRequiredDocuments())->values();
        } elseif ($status === 'belum') {
            $customers = $customers->filter(fn ($c) => !$c->hasVerifiedAllRequiredDocuments())->values();
        }

        $pendingCount = CustomerDocument::whereHas('customer', fn ($q) => $q->where('outlet_id', $outlet->id))
            ->where('status', 'menunggu')
            ->count();

        return view('sewa.documents.index', compact('outlet', 'requirements', 'requiredCount', 'customers', 'status', 'pendingCount'));
    }

    /** Unggah/ganti dokumen pelanggan untuk satu persyaratan — status kembali ke "menunggu" setiap kali diunggah ulang. */
    public function upload(Request $request, Outlet $outlet, Customer $customer): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeCustomer($outlet, $customer);

        $data = $request->validate([
            'rental_document_requirement_id' => ['required', function ($attr, $value, $fail) use ($outlet) {
                if (!$outlet->documentRequirements()->where('id', $value)->exists()) {
                    $fail('Persyaratan dokumen tidak valid untuk outlet ini.');
                }
            }],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $photoPath = $request->file('photo')->store("customer-documents/{$outlet->id}", 'public');

        $existing = CustomerDocument::where('customer_id', $customer->id)
            ->where('rental_document_requirement_id', $data['rental_document_requirement_id'])
            ->first();

        if ($existing) {
            $existing->update([
                'photo'       => $photoPath,
                'status'      => 'menunggu',
                'notes'       => null,
                'verified_at' => null,
            ]);
        } else {
            CustomerDocument::create([
                'customer_id'                     => $customer->id,
                'rental_document_requirement_id'  => $data['rental_document_requirement_id'],
                'photo'                            => $photoPath,
                'status'                           => 'menunggu',
            ]);
        }

        return back()->with('success', 'Dokumen berhasil diunggah, menunggu verifikasi.');
    }

    public function verify(Outlet $outlet, Customer $customer, CustomerDocument $document): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeCustomer($outlet, $customer);
        if ((int) $document->customer_id !== (int) $customer->id) {
            abort(404);
        }

        $document->update(['status' => 'terverifikasi', 'notes' => null, 'verified_at' => now()]);

        return back()->with('success', 'Dokumen ditandai terverifikasi.');
    }

    public function reject(Request $request, Outlet $outlet, Customer $customer, CustomerDocument $document): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeCustomer($outlet, $customer);
        if ((int) $document->customer_id !== (int) $customer->id) {
            abort(404);
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $document->update(['status' => 'ditolak', 'notes' => $data['notes'] ?? null, 'verified_at' => now()]);

        return back()->with('success', 'Dokumen ditandai ditolak.');
    }

    public function destroy(Outlet $outlet, Customer $customer, CustomerDocument $document): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeCustomer($outlet, $customer);
        if ((int) $document->customer_id !== (int) $customer->id) {
            abort(404);
        }

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
