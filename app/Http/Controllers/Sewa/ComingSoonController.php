<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ComingSoonController extends Controller
{
    /** Label, ikon, dan deskripsi tiap menu Rental yang belum dibangun — dikunci dari nama route. */
    private const SECTIONS = [
        'bookings.create' => ['Booking Baru', 'fa-calendar-plus', 'Buat booking barang/unit untuk pelanggan sebelum tanggal sewa.'],
        'bookings.active' => ['Booking Aktif', 'fa-calendar-check', 'Daftar booking yang masih menunggu untuk diproses menjadi sewa.'],
        'calendar.index'   => ['Kalender Rental', 'fa-calendar-days', 'Kalender ketersediaan barang/unit dan jadwal booking.'],
        'bookings.done'    => ['Booking Selesai / Batal', 'fa-calendar-xmark', 'Riwayat booking yang sudah selesai diproses atau dibatalkan.'],
    ];

    private const BOOKING_KEYS = ['bookings.create', 'bookings.active', 'calendar.index', 'bookings.done'];

    public function show(Outlet $outlet): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }

        $key = Str::after(Route::currentRouteName(), 'sewa.');

        if (in_array($key, self::BOOKING_KEYS, true) && !$outlet->enable_booking) {
            abort(404, 'Fitur Booking belum diaktifkan untuk outlet ini.');
        }

        [$title, $icon, $desc] = self::SECTIONS[$key] ?? ['Fitur Rental', 'fa-hammer', 'Fitur ini sedang dikembangkan.'];

        $outlet->load('outletType');

        return view('sewa.coming-soon', compact('outlet', 'title', 'icon', 'desc'));
    }
}
