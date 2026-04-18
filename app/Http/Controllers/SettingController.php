<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::all()->keyBy('key');

        // Ensure all defaults exist
        foreach (Setting::DEFAULTS as $default) {
            if (!$settings->has($default['key'])) {
                $settings->put($default['key'], (object) array_merge($default, ['value' => $default['value']]));
            }
        }

        // Group them
        $grouped = collect(Setting::DEFAULTS)
            ->groupBy('group')
            ->map(fn($rows) => $rows->map(fn($row) => array_merge(
                $row,
                ['value' => $settings->get($row['key'])?->value ?? $row['value']]
            )));

        $groupLabels = [
            'aplikasi' => 'Aplikasi',
            'stok'     => 'Stok',
            'keuangan' => 'Keuangan',
            'payment'  => 'Payment Gateway',
        ];

        $outlets = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();

        return view('settings.index', compact('grouped', 'groupLabels', 'outlets'));
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = collect(Setting::DEFAULTS)->pluck('key')->toArray();

        // Save toggle fields — unchecked checkboxes don't appear in request
        $toggleKeys = collect(Setting::DEFAULTS)
            ->where('type', 'toggle')
            ->pluck('key');

        $data = $request->only($keys);
        foreach ($toggleKeys as $tk) {
            $data[$tk] = $request->has($tk) ? '1' : '0';
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        // Clear all setting cache
        foreach ($keys as $key) {
            Cache::forget("setting:{$key}");
        }

        // Save per-outlet payment gateway toggles
        $enabledOutlets = $request->input('payment_outlet_ids', []);
        Outlet::query()->update(['payment_gateway_enabled' => false]);
        if (!empty($enabledOutlets)) {
            Outlet::whereIn('id', $enabledOutlets)->update(['payment_gateway_enabled' => true]);
        }

        ActivityLog::record('update_settings', 'Pengaturan aplikasi diperbarui');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
