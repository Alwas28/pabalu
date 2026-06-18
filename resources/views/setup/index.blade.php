<x-setup-layout :step="1" pageTitle="Info Bisnis">

<div class="card animate-fadeUp" style="width:100%;max-width:480px">

  <div style="padding:24px 28px 0">
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Info Bisnis Anda</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:6px;line-height:1.6">
      Isi informasi dasar bisnis Anda. Data ini digunakan untuk mengidentifikasi toko Anda di sistem.
    </p>
  </div>

  <form method="POST" action="{{ route('setup.profile') }}" style="padding:24px 28px;display:flex;flex-direction:column;gap:18px">
    @csrf

    {{-- Nama & Email (readonly, sudah dari akun) --}}
    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:12px">
      <div class="a-grad" style="width:38px;height:38px;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:700;flex-shrink:0">
        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
      </div>
      <div style="min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--text)">{{ Auth::user()->name }}</div>
        <div style="font-size:11.5px;color:var(--muted)">{{ Auth::user()->email }}</div>
        @if(Auth::user()->phone)
        <div style="font-size:11.5px;color:var(--muted);display:flex;align-items:center;gap:5px;margin-top:2px">
          <i class="fa-brands fa-whatsapp" style="color:#25d366"></i>
          {{ Auth::user()->phone }}
        </div>
        @endif
      </div>
      <span class="badge badge-green" style="margin-left:auto;flex-shrink:0">
        <i class="fa-solid fa-circle-check" style="font-size:9px"></i>Terverifikasi
      </span>
    </div>

    {{-- Nama Bisnis --}}
    <div>
      <label class="f-label" for="business_name">
        Nama Bisnis / Toko <span style="color:#f87171">*</span>
      </label>
      <input id="business_name" name="business_name" type="text" class="f-input"
        value="{{ old('business_name', session('setup_business_name')) }}"
        placeholder="Contoh: Toko Pak Budi, Kafe Santai..."
        autofocus autocomplete="off">
      <p style="font-size:11.5px;color:var(--muted);margin-top:5px">
        <i class="fa-solid fa-circle-info"></i> Nama ini muncul di laporan dan struk.
      </p>
      @error('business_name')
        <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="a-grad"
      style="width:100%;padding:11px;border-radius:12px;border:none;color:#fff;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:4px">
      Lanjutkan <i class="fa-solid fa-arrow-right"></i>
    </button>
  </form>

</div>

</x-setup-layout>
