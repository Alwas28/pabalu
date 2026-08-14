<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\RentalExtension;
use App\Models\RentalPayment;
use App\Models\RentalTransaction;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RentalController extends Controller
{
    private const DT_FORMAT = 'Y-m-d H:i';

    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }
        return $user;
    }

    private function authorizeTransaction(Outlet $outlet, RentalTransaction $rental): void
    {
        if ((int) $rental->outlet_id !== (int) $outlet->id) {
            abort(404);
        }
    }

    /** Metode pembayaran yang tersedia untuk outlet ini: Tunai selalu ada + metode non-tunai yang diaktifkan. */
    private function paymentMethodOptions(Outlet $outlet): array
    {
        $options = ['cash' => 'Tunai'];
        foreach ($outlet->activePaymentMethods() as $code => [$label, $icon]) {
            $options[$code] = $label;
        }
        return $options;
    }

    private function generateOrderNumber(Outlet $outlet): string
    {
        $prefix = 'REN-' . ($outlet->code ?: $outlet->id) . '-' . now()->format('ymd') . '-';
        $last   = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('order_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max('order_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    /** ═══════════════════ SEWA BARU ═══════════════════ */

    public function create(Outlet $outlet): View
    {
        $this->authorizeOutlet($outlet);

        $requiredDocs    = $outlet->documentRequirements()->where('status', 'wajib')->orderBy('name')->pluck('name');
        $hasRequiredDocs = $requiredDocs->isNotEmpty();

        $allCustomers = Customer::where('outlet_id', $outlet->id)->orderBy('name')->get();
        $customers    = $hasRequiredDocs
            ? $allCustomers->filter(fn ($c) => $c->hasVerifiedAllRequiredDocuments())->values()
            : $allCustomers;

        $availableUnits = RentalUnit::where('status', 'tersedia')
            ->whereHas('rentalItem', fn ($q) => $q->where('outlet_id', $outlet->id))
            ->with('rentalItem')
            ->orderBy('code')
            ->get();
        $paymentMethods = $this->paymentMethodOptions($outlet);
        $allCustomersCount = $allCustomers->count();

        return view('sewa.rentals.create', compact(
            'outlet', 'customers', 'availableUnits', 'requiredDocs', 'paymentMethods', 'hasRequiredDocs', 'allCustomersCount'
        ));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        $methodKeys = array_keys($this->paymentMethodOptions($outlet));

        $data = $request->validate([
            'customer_id'    => ['required', 'exists:customers,id', function ($attr, $value, $fail) use ($outlet) {
                $customer = Customer::where('id', $value)->where('outlet_id', $outlet->id)->first();
                if (!$customer) {
                    $fail('Pelanggan tidak valid untuk outlet ini.');
                } elseif (!$customer->hasVerifiedAllRequiredDocuments()) {
                    $fail('Pelanggan ini belum melengkapi seluruh dokumen wajib.');
                }
            }],
            'rental_unit_id' => ['required', function ($attr, $value, $fail) use ($outlet) {
                $unit = RentalUnit::whereHas('rentalItem', fn ($q) => $q->where('outlet_id', $outlet->id))->find($value);
                if (!$unit) {
                    $fail('Unit tidak valid untuk outlet ini.');
                } elseif ($unit->status !== 'tersedia') {
                    $fail('Unit yang dipilih sudah tidak tersedia.');
                }
            }],
            'rental_type'    => ['required', 'in:' . implode(',', array_keys(RentalTransaction::TYPES))],
            'start_at'       => ['required', 'date_format:' . self::DT_FORMAT],
            'end_at'         => ['required', 'date_format:' . self::DT_FORMAT, 'after:start_at'],
            'total_amount'   => ['required', 'integer', 'min:0'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'paid_amount'    => ['nullable', 'integer', 'min:0', function ($attr, $value, $fail) use ($request) {
                $total = (int) $request->input('total_amount');
                if ($value && $value > $total) {
                    $fail('Jumlah dibayar tidak boleh melebihi total biaya sewa.');
                }
            }],
            'payment_method' => [
                ($request->filled('deposit_amount') && $request->input('deposit_amount') > 0)
                || ($request->filled('paid_amount') && $request->input('paid_amount') > 0)
                    ? 'required' : 'nullable',
                'in:' . implode(',', $methodKeys),
            ],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'payment_photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $paidAmount = (int) ($data['paid_amount'] ?? 0);

        $photoPath = null;
        if ($paidAmount > 0 && $request->hasFile('payment_photo')) {
            $photoPath = $request->file('payment_photo')->store("rental-payments/{$outlet->id}", 'public');
        }

        $rental = DB::transaction(function () use ($data, $outlet, $paidAmount, $photoPath) {
            Outlet::whereKey($outlet->id)->lockForUpdate()->first();

            $rental = RentalTransaction::create([
                'outlet_id'      => $outlet->id,
                'customer_id'    => $data['customer_id'],
                'rental_unit_id' => $data['rental_unit_id'],
                'rental_type'    => $data['rental_type'],
                'order_number'   => $this->generateOrderNumber($outlet),
                'start_at'       => $data['start_at'],
                'end_at'         => $data['end_at'],
                'total_amount'   => $data['total_amount'],
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'notes'          => $data['notes'] ?? null,
                'status'         => 'aktif',
            ]);

            RentalUnit::whereKey($data['rental_unit_id'])->update(['status' => 'disewa']);

            if ($paidAmount > 0) {
                RentalPayment::create([
                    'rental_transaction_id' => $rental->id,
                    'amount'                => $paidAmount,
                    'method'                => $data['payment_method'],
                    'reference_number'      => $data['payment_reference'] ?? null,
                    'photo'                 => $photoPath,
                    'paid_at'               => now(),
                ]);
            }

            return $rental;
        });

        return redirect($outlet->route('rentals.active'))->with('success', "Sewa {$rental->order_number} berhasil dibuat.");
    }

    /** ═══════════════════ EDIT SEWA ═══════════════════ */

    public function edit(Outlet $outlet, RentalTransaction $rental): View
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeTransaction($outlet, $rental);

        if ($rental->status !== 'aktif') {
            abort(404);
        }

        $rental->load('customer', 'rentalUnit.rentalItem');

        $hasRequiredDocs = $outlet->documentRequirements()->where('status', 'wajib')->exists();
        $allCustomers    = Customer::where('outlet_id', $outlet->id)->orderBy('name')->get();
        $customers       = $hasRequiredDocs
            ? $allCustomers->filter(fn ($c) => $c->hasVerifiedAllRequiredDocuments() || (int) $c->id === (int) $rental->customer_id)->values()
            : $allCustomers;

        $availableUnits = RentalUnit::whereHas('rentalItem', fn ($q) => $q->where('outlet_id', $outlet->id))
            ->where(function ($q) use ($rental) {
                $q->where('status', 'tersedia')->orWhere('id', $rental->rental_unit_id);
            })
            ->with('rentalItem')
            ->orderBy('code')
            ->get();

        return view('sewa.rentals.edit', compact('outlet', 'rental', 'customers', 'availableUnits'));
    }

    public function updateRental(Request $request, Outlet $outlet, RentalTransaction $rental): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeTransaction($outlet, $rental);

        if ($rental->status !== 'aktif') {
            abort(422, 'Sewa ini sudah selesai.');
        }

        $data = $request->validate([
            'customer_id'    => ['required', 'exists:customers,id', function ($attr, $value, $fail) use ($outlet, $rental) {
                $customer = Customer::where('id', $value)->where('outlet_id', $outlet->id)->first();
                if (!$customer) {
                    $fail('Pelanggan tidak valid untuk outlet ini.');
                } elseif (!$customer->hasVerifiedAllRequiredDocuments() && (int) $value !== (int) $rental->customer_id) {
                    $fail('Pelanggan ini belum melengkapi seluruh dokumen wajib.');
                }
            }],
            'rental_unit_id' => ['required', function ($attr, $value, $fail) use ($outlet, $rental) {
                $unit = RentalUnit::whereHas('rentalItem', fn ($q) => $q->where('outlet_id', $outlet->id))->find($value);
                if (!$unit) {
                    $fail('Unit tidak valid untuk outlet ini.');
                } elseif ($unit->status !== 'tersedia' && (int) $value !== (int) $rental->rental_unit_id) {
                    $fail('Unit yang dipilih sudah tidak tersedia.');
                }
            }],
            'rental_type'    => ['required', 'in:' . implode(',', array_keys(RentalTransaction::TYPES))],
            'start_at'       => ['required', 'date_format:' . self::DT_FORMAT],
            'end_at'         => ['required', 'date_format:' . self::DT_FORMAT, 'after:start_at'],
            'total_amount'   => ['required', 'integer', 'min:0'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($rental, $data) {
            $oldUnitId = (int) $rental->rental_unit_id;
            $newUnitId = (int) $data['rental_unit_id'];

            $rental->update([
                'customer_id'    => $data['customer_id'],
                'rental_unit_id' => $newUnitId,
                'rental_type'    => $data['rental_type'],
                'start_at'       => $data['start_at'],
                'end_at'         => $data['end_at'],
                'total_amount'   => $data['total_amount'],
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'notes'          => $data['notes'] ?? null,
            ]);

            if ($oldUnitId !== $newUnitId) {
                RentalUnit::whereKey($oldUnitId)->update(['status' => 'tersedia']);
                RentalUnit::whereKey($newUnitId)->update(['status' => 'disewa']);
            }
        });

        return redirect($outlet->route('rentals.active'))->with('success', "Sewa {$rental->order_number} berhasil diperbarui.");
    }

    /**
     * Proses Bayar — melunasi sisa tagihan sewa (dipakai dari halaman Sewa Aktif).
     * Bisa dipotong dari deposit yang tersedia (use_deposit=1); jika deposit tidak cukup menutup
     * seluruh sisa tagihan, sisanya wajib dibayar lewat metode pembayaran biasa.
     */
    public function storePayment(Request $request, Outlet $outlet, RentalTransaction $rental): JsonResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeTransaction($outlet, $rental);

        if ($rental->status !== 'aktif') {
            return response()->json(['message' => 'Sewa ini sudah selesai.'], 422);
        }

        $methodKeys = array_keys($this->paymentMethodOptions($outlet));
        $useDeposit = $request->boolean('use_deposit');
        $remaining  = $rental->remainingAmount();
        $fineInput  = max(0, (int) $request->input('fine_amount', 0));

        // Hitung ulang di server — jangan percaya nilai potongan deposit/denda dari klien.
        // Deposit hanya menutup biaya sewa; denda baru selalu harus dibayar lewat metode pembayaran.
        $depositCut     = $useDeposit ? min($rental->depositAvailable(), $remaining) : 0;
        $rentalLeftover = $remaining - $depositCut;
        $totalDue       = $rentalLeftover + $fineInput;

        $data = $request->validate([
            'unit_status'      => ['nullable', 'in:tersedia,disewa,maintenance,rusak'],
            'fine_amount'      => ['nullable', 'integer', 'min:0'],
            'payment_method'   => [$totalDue > 0 ? 'required' : 'nullable', 'in:' . implode(',', $methodKeys)],
            'cash_received'    => ['nullable', 'integer', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'photo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($totalDue > 0 && $data['payment_method'] === 'cash'
            && !empty($data['cash_received']) && $data['cash_received'] < $totalDue) {
            return response()->json(['message' => 'Uang diterima kurang dari total yang harus dibayar.'], 422);
        }

        $photoPath = null;
        if ($totalDue > 0 && $request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store("rental-payments/{$outlet->id}", 'public');
        }

        $unitStatus = $data['unit_status'] ?? null;

        DB::transaction(function () use ($rental, $depositCut, $rentalLeftover, $fineInput, $data, $photoPath, $unitStatus) {
            if ($depositCut > 0) {
                RentalPayment::create([
                    'rental_transaction_id' => $rental->id,
                    'amount'                => $depositCut,
                    'method'                => 'deposit',
                    'note'                  => 'Dipotong otomatis dari deposit.',
                    'paid_at'               => now(),
                ]);
            }

            if ($rentalLeftover > 0 || $fineInput > 0) {
                $note = null;
                if ($data['payment_method'] === 'cash' && !empty($data['cash_received']) && $data['cash_received'] > ($rentalLeftover + $fineInput)) {
                    $change = $data['cash_received'] - ($rentalLeftover + $fineInput);
                    $note   = 'Uang diterima Rp' . number_format($data['cash_received'], 0, ',', '.') . ', kembalian Rp' . number_format($change, 0, ',', '.') . '.';
                }

                if ($rentalLeftover > 0) {
                    RentalPayment::create([
                        'rental_transaction_id' => $rental->id,
                        'amount'                => $rentalLeftover,
                        'method'                => $data['payment_method'],
                        'reference_number'      => $data['reference_number'] ?? null,
                        'photo'                 => $photoPath,
                        'note'                  => $note,
                        'paid_at'               => now(),
                    ]);
                }

                if ($fineInput > 0) {
                    RentalPayment::create([
                        'rental_transaction_id' => $rental->id,
                        'amount'                => $fineInput,
                        'method'                => $data['payment_method'],
                        'is_fine'               => true,
                        'reference_number'      => $rentalLeftover > 0 ? null : ($data['reference_number'] ?? null),
                        'photo'                 => $rentalLeftover > 0 ? null : $photoPath,
                        'note'                  => $rentalLeftover > 0 ? 'Pembayaran denda.' : ($note ?? 'Pembayaran denda.'),
                        'paid_at'               => now(),
                    ]);
                }
            }

            if ($fineInput > 0) {
                $rental->increment('fine_amount', $fineInput);
            }

            if ($unitStatus !== null && $unitStatus !== $rental->rentalUnit->status) {
                $rental->rentalUnit->update(['status' => $unitStatus]);
            }
        });

        // Jika deposit baru saja habis terpakai untuk membayar (potong dari deposit), tidak ada lagi yang
        // perlu dikembalikan ke pelanggan — tandai otomatis sebagai sudah di-refund (refund_amount 0).
        if ($depositCut > 0) {
            $rental->refresh();
            if ($rental->depositAvailable() <= 0 && $rental->refunded_at === null) {
                $rental->update(['refunded_at' => now(), 'refund_amount' => 0]);
            }
        }

        return response()->json([
            'message'     => 'Pembayaran berhasil dicatat.',
            'deposit_cut' => $depositCut,
            'leftover'    => $rentalLeftover,
            'fine_added'  => $fineInput,
        ]);
    }

    /** ═══════════════════ DETAIL SEWA ═══════════════════ */

    public function show(Outlet $outlet, RentalTransaction $rental): View
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeTransaction($outlet, $rental);

        $rental->load(['customer', 'rentalUnit.rentalItem', 'payments', 'extensions']);

        return view('sewa.rentals.show', compact('outlet', 'rental'));
    }

    /** Cetak bukti transaksi sewa — format mengikuti pengaturan Jenis Struk outlet (Invoice A4 / Struk Thermal). */
    public function receipt(Outlet $outlet, RentalTransaction $rental): View
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeTransaction($outlet, $rental);

        $rental->load(['customer', 'rentalUnit.rentalItem', 'payments', 'extensions']);
        $outlet->load('outletType');

        $view = $outlet->rental_receipt_type === 'invoice' ? 'sewa.rentals.receipt-invoice' : 'sewa.rentals.receipt-thermal';

        return view($view, compact('outlet', 'rental'));
    }

    /** ═══════════════════ SEWA AKTIF ═══════════════════ */

    public function active(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        $rentals = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('status', 'aktif')
            ->with(['customer', 'rentalUnit.rentalItem', 'payments'])
            ->orderBy('end_at')
            ->get();

        return view('sewa.rentals.active', compact('outlet', 'user', 'rentals'));
    }

    /** ═══════════════════ PERPANJANGAN ═══════════════════ */

    public function extend(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        $rentals = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('status', 'aktif')
            ->with(['customer', 'rentalUnit.rentalItem'])
            ->orderBy('end_at')
            ->get();

        $history = RentalExtension::whereHas('rentalTransaction', fn ($q) => $q->where('outlet_id', $outlet->id))
            ->with('rentalTransaction.customer', 'rentalTransaction.rentalUnit.rentalItem')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('sewa.rentals.extend', compact('outlet', 'user', 'rentals', 'history'));
    }

    public function storeExtension(Request $request, Outlet $outlet, RentalTransaction $rental): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeTransaction($outlet, $rental);

        if ($rental->status !== 'aktif') {
            abort(422, 'Sewa ini sudah selesai.');
        }

        $data = $request->validate([
            'new_end_at'        => ['required', 'date_format:' . self::DT_FORMAT, 'after:' . $rental->end_at->format(self::DT_FORMAT)],
            'additional_amount' => ['nullable', 'integer', 'min:0'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $rental) {
            RentalExtension::create([
                'rental_transaction_id' => $rental->id,
                'previous_end_at'       => $rental->end_at,
                'new_end_at'            => $data['new_end_at'],
                'additional_amount'     => $data['additional_amount'] ?? 0,
                'notes'                 => $data['notes'] ?? null,
            ]);

            $rental->update([
                'end_at'       => $data['new_end_at'],
                'total_amount' => $rental->total_amount + ($data['additional_amount'] ?? 0),
            ]);
        });

        return back()->with('success', "Sewa {$rental->order_number} berhasil diperpanjang.");
    }

    /** ═══════════════════ PENGEMBALIAN ═══════════════════ */

    public function returns(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        $rentals = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('status', 'aktif')
            ->with(['customer', 'rentalUnit.rentalItem', 'payments'])
            ->orderBy('end_at')
            ->get();

        return view('sewa.rentals.returns', compact('outlet', 'user', 'rentals'));
    }

    public function processReturn(Request $request, Outlet $outlet, RentalTransaction $rental): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeTransaction($outlet, $rental);

        if ($rental->status !== 'aktif') {
            abort(422, 'Sewa ini sudah selesai.');
        }

        $data = $request->validate([
            'condition_note' => ['nullable', 'string', 'max:1000'],
            'fine_amount'    => ['nullable', 'integer', 'min:0'],
            'is_damaged'     => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $rental) {
            $rental->update([
                'status'         => 'selesai',
                'returned_at'    => now(),
                'fine_amount'    => $data['fine_amount'] ?? 0,
                'condition_note' => $data['condition_note'] ?? null,
            ]);

            $rental->rentalUnit->update([
                'status' => !empty($data['is_damaged']) ? 'rusak' : 'tersedia',
            ]);
        });

        return back()->with('success', "Pengembalian sewa {$rental->order_number} berhasil diproses.");
    }
}