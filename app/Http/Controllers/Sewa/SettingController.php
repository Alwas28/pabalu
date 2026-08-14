<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\RentalDocumentRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** Nama dokumen umum yang ditawarkan sebagai chip cepat saat menambah persyaratan baru. */
    public const SUGGESTED_NAMES = [
        'KTP', 'SIM', 'Paspor', 'Kartu Pelajar', 'Kartu Mahasiswa', 'NPWP', 'Kartu Keluarga',
    ];

    private function authorizeOwner(Outlet $outlet): void
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && (int) $outlet->owner_id !== (int) $user->id) {
            abort(403, 'Hanya pemilik atau admin yang dapat mengubah pengaturan outlet.');
        }
    }

    private function authorizeRequirement(Outlet $outlet, RentalDocumentRequirement $requirement): void
    {
        if ((int) $requirement->outlet_id !== (int) $outlet->id) {
            abort(404);
        }
    }

    public function edit(Request $request, Outlet $outlet): View
    {
        $this->authorizeOwner($outlet);
        $outlet->load('outletType');

        $tab = in_array($request->query('tab'), ['requirements', 'payment', 'receipt'], true) ? $request->query('tab') : 'booking';
        $requirements = $outlet->documentRequirements()->orderBy('sort_order')->orderBy('name')->get();

        return view('sewa.settings.index', [
            'outlet'         => $outlet,
            'tab'            => $tab,
            'requirements'   => $requirements,
            'suggestedNames' => self::SUGGESTED_NAMES,
        ]);
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($outlet);

        $outlet->update([
            'enable_booking' => $request->boolean('enable_booking'),
        ]);

        return back()->with('success', 'Pengaturan booking berhasil disimpan.');
    }

    public function updatePayment(Request $request, Outlet $outlet): RedirectResponse|JsonResponse
    {
        $this->authorizeOwner($outlet);

        $isSystemAdmin = Auth::user()->role === 'admin';

        $data = $request->validate([
            'enable_qris_transfer'   => ['nullable', 'boolean'],
            'enable_transfer'        => ['nullable', 'boolean'],
            'enable_card'            => ['nullable', 'boolean'],
            'enable_qris_pay'        => ['nullable', 'boolean'],
            'midtrans_server_key'    => ['nullable', 'string', 'max:255'],
            'midtrans_client_key'    => ['nullable', 'string', 'max:255'],
            'midtrans_is_production' => ['nullable', 'boolean'],
        ]);

        // QRIS Pay hanya bisa diubah oleh admin sistem; non-admin tidak bisa mengubah nilai yang sudah ada,
        // berapa pun nilai yang dikirim (proteksi ganda selain atribut disabled di frontend).
        $enableQrisPay = $isSystemAdmin
            ? $request->boolean('enable_qris_pay')
            : $outlet->enable_qris_pay;

        if ($enableQrisPay && !$request->filled('midtrans_server_key') && !$outlet->midtrans_server_key) {
            $message = 'Server Key Midtrans wajib diisi untuk mengaktifkan QRIS Pay.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $message, 'errors' => ['midtrans_server_key' => [$message]]], 422);
            }
            return back()->withErrors(['midtrans_server_key' => $message])->withInput();
        }

        $outlet->update([
            'enable_qris_transfer'   => $request->boolean('enable_qris_transfer'),
            'enable_qris_pay'        => $enableQrisPay,
            'enable_transfer'        => $request->boolean('enable_transfer'),
            'enable_card'            => $request->boolean('enable_card'),
            'midtrans_server_key'    => $request->filled('midtrans_server_key') ? $data['midtrans_server_key'] : $outlet->midtrans_server_key,
            'midtrans_client_key'    => $request->filled('midtrans_client_key') ? $data['midtrans_client_key'] : $outlet->midtrans_client_key,
            'midtrans_is_production' => $request->boolean('midtrans_is_production'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message'         => 'Metode pembayaran berhasil disimpan.',
                'enable_qris_pay' => $outlet->enable_qris_pay,
            ]);
        }

        return redirect($outlet->route('settings.edit', ['tab' => 'payment']))
            ->with('success', 'Metode pembayaran berhasil disimpan.');
    }

    public function updateReceipt(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($outlet);

        $data = $request->validate([
            'rental_receipt_type' => ['required', 'in:invoice,thermal'],
        ]);

        $outlet->update($data);

        return redirect($outlet->route('settings.edit', ['tab' => 'receipt']))
            ->with('success', 'Jenis struk berhasil disimpan.');
    }

    public function storeRequirement(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($outlet);

        $data = $request->validate([
            'name'   => ['required', 'string', 'max:100', 'unique:rental_document_requirements,name,NULL,id,outlet_id,' . $outlet->id],
            'status' => ['required', 'in:opsional,wajib'],
        ], [
            'name.unique' => 'Persyaratan dengan nama ini sudah ada.',
        ]);

        $maxSort = $outlet->documentRequirements()->max('sort_order');

        $outlet->documentRequirements()->create([
            'name'       => $data['name'],
            'status'     => $data['status'],
            'sort_order' => $maxSort !== null ? $maxSort + 1 : 0,
        ]);

        return redirect($outlet->route('settings.edit', ['tab' => 'requirements']))
            ->with('success', 'Persyaratan dokumen berhasil ditambahkan.');
    }

    public function updateRequirement(Request $request, Outlet $outlet, RentalDocumentRequirement $requirement): RedirectResponse
    {
        $this->authorizeOwner($outlet);
        $this->authorizeRequirement($outlet, $requirement);

        $data = $request->validate([
            'name'   => ['required', 'string', 'max:100', 'unique:rental_document_requirements,name,' . $requirement->id . ',id,outlet_id,' . $outlet->id],
            'status' => ['required', 'in:opsional,wajib'],
        ], [
            'name.unique' => 'Persyaratan dengan nama ini sudah ada.',
        ]);

        $requirement->update($data);

        return redirect($outlet->route('settings.edit', ['tab' => 'requirements']))
            ->with('success', 'Persyaratan dokumen berhasil diperbarui.');
    }

    public function destroyRequirement(Outlet $outlet, RentalDocumentRequirement $requirement): RedirectResponse
    {
        $this->authorizeOwner($outlet);
        $this->authorizeRequirement($outlet, $requirement);

        $requirement->delete();

        return redirect($outlet->route('settings.edit', ['tab' => 'requirements']))
            ->with('success', 'Persyaratan dokumen berhasil dihapus.');
    }
}
