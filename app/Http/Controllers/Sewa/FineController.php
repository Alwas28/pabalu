<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\RentalPayment;
use App\Models\RentalTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FineController extends Controller
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

    /** Metode pembayaran yang tersedia untuk outlet ini: Tunai selalu ada + metode non-tunai yang diaktifkan. */
    private function paymentMethodOptions(Outlet $outlet): array
    {
        $options = ['cash' => 'Tunai'];
        foreach ($outlet->activePaymentMethods() as $code => [$label, $icon]) {
            $options[$code] = $label;
        }
        return $options;
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);
        $from = $data['from'] ?? null;
        $to   = $data['to'] ?? null;

        $query = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('fine_amount', '>', 0)
            ->with(['customer', 'rentalUnit.rentalItem', 'payments']);

        if ($from) $query->whereDate('updated_at', '>=', $from);
        if ($to)   $query->whereDate('updated_at', '<=', $to);

        $rentals = (clone $query)->orderByDesc('updated_at')->paginate(25)->withQueryString();

        $totalFines = (clone $query)->sum('fine_amount');
        $rentalsForPaidCalc = (clone $query)->get();
        $totalPaid      = $rentalsForPaidCalc->sum(fn ($r) => $r->finePaid());
        $totalRemaining = $rentalsForPaidCalc->sum(fn ($r) => $r->fineRemaining());

        return view('sewa.fines.index', compact('outlet', 'user', 'rentals', 'totalFines', 'totalPaid', 'totalRemaining', 'from', 'to'));
    }

    /**
     * Catat pembayaran atas denda yang belum lunas — bisa dipakai kapan pun, baik sewa masih aktif maupun
     * sudah selesai. Bisa dipotong dari deposit yang tersedia (use_deposit=1); jika deposit tidak cukup
     * menutup seluruh jumlah yang dipilih, sisanya wajib dibayar lewat metode pembayaran biasa.
     */
    public function pay(Request $request, Outlet $outlet, RentalTransaction $rental): JsonResponse
    {
        $this->authorizeOutlet($outlet);
        if ((int) $rental->outlet_id !== (int) $outlet->id) {
            abort(404);
        }

        $remaining = $rental->fineRemaining();
        if ($remaining <= 0) {
            return response()->json(['message' => 'Denda untuk sewa ini sudah lunas.'], 422);
        }

        $useDeposit  = $request->boolean('use_deposit');
        $amountInput = max(0, (int) $request->input('amount', 0));

        // Hitung ulang di server — jangan percaya nilai potongan deposit dari klien.
        $depositCut   = $useDeposit ? min($rental->depositAvailable(), $amountInput) : 0;
        $methodAmount = $amountInput - $depositCut;

        $methodKeys = array_keys($this->paymentMethodOptions($outlet));

        $data = $request->validate([
            'amount'           => ['required', 'integer', 'min:1'],
            'payment_method'   => [$methodAmount > 0 ? 'required' : 'nullable', 'in:' . implode(',', $methodKeys)],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'photo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($data['amount'] > $remaining) {
            return response()->json(['message' => 'Jumlah tidak boleh melebihi sisa denda.'], 422);
        }

        $photoPath = null;
        if ($methodAmount > 0 && $request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store("rental-payments/{$outlet->id}", 'public');
        }

        DB::transaction(function () use ($rental, $depositCut, $methodAmount, $data, $photoPath) {
            if ($depositCut > 0) {
                RentalPayment::create([
                    'rental_transaction_id' => $rental->id,
                    'amount'                => $depositCut,
                    'method'                => 'deposit',
                    'is_fine'               => true,
                    'note'                  => 'Denda dipotong dari deposit.',
                    'paid_at'               => now(),
                ]);
            }

            if ($methodAmount > 0) {
                RentalPayment::create([
                    'rental_transaction_id' => $rental->id,
                    'amount'                => $methodAmount,
                    'method'                => $data['payment_method'],
                    'is_fine'               => true,
                    'reference_number'      => $data['reference_number'] ?? null,
                    'photo'                 => $photoPath,
                    'note'                  => 'Pembayaran denda.',
                    'paid_at'               => now(),
                ]);
            }
        });

        // Jika deposit baru saja habis terpakai, tidak ada lagi yang perlu dikembalikan ke pelanggan —
        // tandai otomatis sebagai sudah di-refund (sama seperti pola di Proses Bayar).
        if ($depositCut > 0) {
            $rental->refresh();
            if ($rental->depositAvailable() <= 0 && $rental->refunded_at === null) {
                $rental->update(['refunded_at' => now(), 'refund_amount' => 0]);
            }
        }

        return response()->json([
            'message'       => "Pembayaran denda untuk {$rental->order_number} berhasil dicatat.",
            'deposit_cut'   => $depositCut,
            'method_amount' => $methodAmount,
        ]);
    }
}
