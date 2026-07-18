@php
  /** @var \App\Models\User $user */
  $roleLabels = [
    'admin'        => ['label' => 'Administrator',  'icon' => 'fa-shield-halved', 'color' => '#a78bfa'],
    'owner'        => ['label' => 'Pemilik Toko',   'icon' => 'fa-store',         'color' => '#34d399'],
    'admin_outlet' => ['label' => 'Admin Outlet',   'icon' => 'fa-user-tie',      'color' => '#60a5fa'],
    'kasir'        => ['label' => 'Kasir',           'icon' => 'fa-cash-register', 'color' => '#fbbf24'],
  ];
  $roleInfo = $roleLabels[$user->role] ?? ['label' => $user->role, 'icon' => 'fa-user', 'color' => 'var(--muted)'];
@endphp

<x-app-layout>
<x-slot name="pageTitle">Profil Saya</x-slot>

<div style="max-width:680px;display:flex;flex-direction:column;gap:20px" class="animate-fadeUp">

  {{-- Header profil --}}
  <div class="card">
    <div class="card-body" style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
      <div class="a-grad" style="width:64px;height:64px;border-radius:18px;display:grid;place-items:center;color:#fff;font-size:26px;font-weight:800;flex-shrink:0">
        {{ strtoupper(substr($user->name, 0, 1)) }}
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:20px;font-weight:800;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user->name }}</div>
        <div style="font-size:13px;color:var(--muted);margin-top:3px">{{ $user->email }}</div>
        <div style="margin-top:8px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
          <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;background:rgba(255,255,255,.06);border:1px solid var(--border);font-size:12px;font-weight:600;color:{{ $roleInfo['color'] }}">
            <i class="fa-solid {{ $roleInfo['icon'] }}" style="font-size:11px"></i>{{ $roleInfo['label'] }}
          </span>
          @if($user->hasVerifiedEmail())
          <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);font-size:12px;font-weight:600;color:#34d399">
            <i class="fa-solid fa-circle-check" style="font-size:10px"></i>Terverifikasi
          </span>
          @else
          <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);font-size:12px;font-weight:600;color:#fbbf24">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:10px"></i>Belum Terverifikasi
          </span>
          @endif
        </div>
      </div>
      {{-- Tombol ke halaman ganti password --}}
      <a href="{{ route('profile.password') }}"
        style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;flex-shrink:0">
        <i class="fa-solid fa-lock" style="font-size:12px"></i>Ganti Password
      </a>
    </div>
  </div>

  {{-- Edit Profil --}}
  <div class="card animate-fadeUp d1">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-user a-text" style="margin-right:8px"></i>Edit Profil
      </div>
    </div>

    <form method="POST" action="{{ route('profile.update') }}">
      @csrf
      @method('PATCH')

      <div class="card-body" style="display:flex;flex-direction:column;gap:18px">

        @if(session('status') === 'profile-updated')
        <div style="padding:12px 16px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);border-radius:11px;display:flex;align-items:center;gap:10px;font-size:13px;color:#34d399;font-weight:600">
          <i class="fa-solid fa-circle-check"></i>Profil berhasil diperbarui.
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div>
            <label class="f-label" for="name">Nama Lengkap <span style="color:#f87171">*</span></label>
            <input id="name" name="name" type="text" class="f-input"
              value="{{ old('name', $user->name) }}"
              placeholder="Nama lengkap kamu" autocomplete="name">
            @error('name')
              <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="f-label" for="phone">No. HP</label>
            <input id="phone" name="phone" type="tel" class="f-input"
              value="{{ old('phone', $user->phone) }}"
              placeholder="08xxxxxxxxxx" autocomplete="tel">
            @error('phone')
              <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
            @enderror
          </div>
        </div>

        <div>
          <label class="f-label" for="email">Alamat Email <span style="color:#f87171">*</span></label>
          <input id="email" name="email" type="email" class="f-input"
            value="{{ old('email', $user->email) }}"
            placeholder="email@domain.com" autocomplete="email">
          @if(!$user->hasVerifiedEmail())
          <p style="font-size:11.5px;color:var(--muted);margin-top:5px">
            <i class="fa-solid fa-circle-info"></i> Mengubah email akan mereset status verifikasi.
          </p>
          @endif
          @error('email')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

      </div>

      <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
        <button type="submit"
          class="a-grad" style="padding:9px 22px;border-radius:11px;border:none;color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px">
          <i class="fa-solid fa-floppy-disk"></i>Simpan Profil
        </button>
      </div>
    </form>
  </div>

</div>
</x-app-layout>
