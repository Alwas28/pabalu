<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public const TIMEZONES = [
        'Asia/Jakarta'  => 'WIB — Waktu Indonesia Barat',
        'Asia/Makassar' => 'WITA — Waktu Indonesia Tengah',
        'Asia/Jayapura' => 'WIT — Waktu Indonesia Timur',
    ];

    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
    }

    public function edit(): View
    {
        $this->authorizeAdmin();

        $timezone = AppSetting::get('timezone', 'Asia/Makassar');

        return view('settings.system', [
            'timezone'  => $timezone,
            'timezones' => self::TIMEZONES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'timezone' => ['required', 'in:' . implode(',', array_keys(self::TIMEZONES))],
        ]);

        AppSetting::set('timezone', $data['timezone']);

        return back()->with('success', 'Zona waktu berhasil disimpan.');
    }
}
