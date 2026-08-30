<?php

namespace App\Http\Controllers;

use App\Http\Middleware\DynamicThrottle;
use App\Models\AppSetting;
use App\Services\WhatsApp\WhatsAppGatewayManager;
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

    public function general(): View
    {
        $this->authorizeAdmin();

        return view('settings.system', [
            'tab'       => 'general',
            'timezone'  => AppSetting::get('timezone', 'Asia/Makassar'),
            'timezones' => self::TIMEZONES,
        ]);
    }

    public function whatsapp(): View
    {
        $this->authorizeAdmin();

        return view('settings.system', [
            'tab'         => 'whatsapp',
            'waEnabled'   => WhatsAppGatewayManager::isEnabled(),
            'waProvider'  => AppSetting::get('wa_gateway_provider', 'fonnte'),
            'waHasKey'    => (bool) AppSetting::get('wa_gateway_api_key'),
            'waDomain'    => AppSetting::get('wa_gateway_api_domain', ''),
            'waSender'    => AppSetting::get('wa_gateway_sender_number', ''),
            'waProviders' => WhatsAppGatewayManager::PROVIDERS,
        ]);
    }

    public function rateLimits(): View
    {
        $this->authorizeAdmin();

        $limits = [];
        foreach (DynamicThrottle::DEFAULTS as $name => $default) {
            [$max, $minutes] = DynamicThrottle::resolveLimit($name);
            $limits[$name] = ['max' => $max, 'minutes' => $minutes, 'label' => DynamicThrottle::LABELS[$name]];
        }

        return view('settings.system', ['tab' => 'rate-limit', 'rateLimits' => $limits]);
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

    // API key TIDAK wajib diisi ulang tiap simpan (field ditampilkan kosong di form,
    // kirim kosong = pertahankan key lama) — supaya admin tidak perlu buka lagi
    // dashboard provider cuma untuk copy-paste ulang key yang sama.
    public function updateWhatsapp(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'wa_gateway_provider'      => ['required', 'in:' . implode(',', array_keys(WhatsAppGatewayManager::PROVIDERS))],
            'wa_gateway_api_key'       => ['nullable', 'string', 'max:255'],
            'wa_gateway_api_domain'    => ['nullable', 'required_if:wa_gateway_provider,wablas,selfhost', 'string', 'max:255'],
            'wa_gateway_sender_number' => ['nullable', 'string', 'max:20'],
        ], [
            'wa_gateway_api_domain.required_if' => 'Domain/URL server wajib diisi untuk provider yang dipilih.',
        ]);

        $enabled     = $request->boolean('wa_gateway_enabled');
        $existingKey = AppSetting::get('wa_gateway_api_key');

        if ($enabled && empty($data['wa_gateway_api_key']) && empty($existingKey)) {
            return back()->withErrors([
                'wa_gateway_api_key' => 'API Key wajib diisi untuk mengaktifkan verifikasi WhatsApp.',
            ])->withInput();
        }

        AppSetting::set('wa_gateway_enabled', $enabled ? '1' : '0');
        AppSetting::set('wa_gateway_provider', $data['wa_gateway_provider']);
        AppSetting::set('wa_gateway_api_domain', $data['wa_gateway_api_domain'] ?? '');
        AppSetting::set('wa_gateway_sender_number', $data['wa_gateway_sender_number'] ?? '');

        if (!empty($data['wa_gateway_api_key'])) {
            AppSetting::set('wa_gateway_api_key', $data['wa_gateway_api_key']);
        }

        return back()->with('success', 'Pengaturan gateway WhatsApp berhasil disimpan.');
    }

    public function testWhatsapp(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'test_phone' => ['required', 'string', 'max:20'],
        ]);

        $result = WhatsAppGatewayManager::send(
            $data['test_phone'],
            'Tes pengiriman dari Pengaturan Sistem Pabalu. Kalau Anda menerima pesan ini, konfigurasi gateway WhatsApp sudah benar.'
        );

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function updateRateLimits(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $rules = [];
        foreach (array_keys(DynamicThrottle::DEFAULTS) as $name) {
            $rules["{$name}_max"]     = ['required', 'integer', 'min:1', 'max:1000'];
            $rules["{$name}_minutes"] = ['required', 'integer', 'min:1', 'max:1440'];
        }

        $data = $request->validate($rules);

        foreach (array_keys(DynamicThrottle::DEFAULTS) as $name) {
            AppSetting::set("rate_limit_{$name}_max", (string) $data["{$name}_max"]);
            AppSetting::set("rate_limit_{$name}_minutes", (string) $data["{$name}_minutes"]);
        }

        return back()->with('success', 'Pengaturan rate limiting berhasil disimpan.');
    }
}
