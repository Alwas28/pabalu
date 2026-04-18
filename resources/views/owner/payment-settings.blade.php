<x-app-layout title="Pembayaran Online">

@php
  $enabled   = $settings['midtrans_enabled'] ?? '0';
  $isEnabled = $enabled === '1';
@endphp

@push('styles')
<style>
.toggle-wrap{display:flex;align-items:center;gap:12px}
.toggle{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-slider{
  position:absolute;inset:0;border-radius:99px;
  background:var(--border);cursor:pointer;transition:background .2s;
}
.toggle-slider:before{
  content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;
  background:#fff;border-radius:50%;transition:transform .2s;
}
.toggle input:checked + .toggle-slider{background:var(--ac)}
.toggle input:checked + .toggle-slider:before{transform:translateX(20px)}
.toggle-label{font-size:13.5px;font-weight:600;color:var(--text);cursor:pointer}
</style>
@endpush

<div style="max-width:680px">

  {{-- Status Card --}}
  <div class="card animate-fadeUp">
    <div class="card-header" style="justify-content:space-between;align-items:center">
      <div class="card-title">
        <i class="fa-solid fa-qrcode" style="color:var(--ac);margin-right:8px"></i>
        Pembayaran Online
      </div>
      @if($hasKeys && $isEnabled)
        <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:7px"></i> Aktif</span>
      @elseif($hasKeys)
        <span class="badge badge-gray">Nonaktif</span>
      @else
        <span class="badge" style="background:rgba(245,158,11,.12);color:#f59e0b">Belum Dikonfigurasi</span>
      @endif
    </div>

    <div class="card-body">
      @if($hasKeys)
      {{-- Sudah dikonfigurasi admin — tampilkan toggle --}}
      <form method="POST" action="{{ route('owner.payment-settings.update') }}">
        @csrf @method('PUT')

        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px">
          <div>
            <div style="font-size:14px;font-weight:600;color:var(--text)">Aktifkan Pembayaran Online</div>
            <div style="font-size:12.5px;color:var(--muted);margin-top:4px;line-height:1.6">
              Pelanggan dapat membayar pesanan secara online melalui QRIS, transfer bank,
              atau dompet digital di halaman order outlet Anda.
            </div>
          </div>
          <div class="toggle-wrap" style="flex-shrink:0">
            <label class="toggle">
              <input type="checkbox" name="midtrans_enabled" value="1" id="pg-toggle"
                {{ $isEnabled ? 'checked' : '' }}
                onchange="document.getElementById('pg-label').textContent=this.checked?'Aktif':'Nonaktif'">
              <span class="toggle-slider"></span>
            </label>
            <span class="toggle-label" id="pg-label">{{ $isEnabled ? 'Aktif' : 'Nonaktif' }}</span>
          </div>
        </div>

        @if($isEnabled)
        <div style="margin-top:16px;padding:10px 14px;border-radius:10px;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);font-size:12.5px;color:#34d399;display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-circle-check"></i>
          Pembayaran online sedang aktif. Pelanggan akan melihat pilihan bayar di halaman order.
        </div>
        @endif

        <div style="margin-top:20px;display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>

      @else
      {{-- Belum dikonfigurasi admin --}}
      <div style="text-align:center;padding:24px 16px">
        <div style="width:60px;height:60px;border-radius:16px;background:rgba(245,158,11,.1);color:#f59e0b;
                    display:grid;place-items:center;font-size:26px;margin:0 auto 16px">
          <i class="fa-solid fa-gear"></i>
        </div>
        <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:8px">
          Fitur ini belum dikonfigurasi
        </div>
        <div style="font-size:13px;color:var(--muted);line-height:1.7;max-width:400px;margin:0 auto">
          Pembayaran online untuk akun Anda belum diatur oleh administrator.
          Hubungi admin Pabalu untuk mengaktifkan fitur ini.
        </div>
      </div>
      @endif
    </div>
  </div>

  {{-- Cara Mengaktifkan --}}
  <div class="card animate-fadeUp d2">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-circle-question" style="color:var(--ac);margin-right:8px"></i>
        Cara Mengaktifkan Pembayaran Online
      </div>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:14px">

      <div style="display:flex;gap:12px;align-items:flex-start">
        <div style="width:26px;height:26px;border-radius:8px;background:var(--ac-lt);color:var(--ac);
                    display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0;margin-top:1px">1</div>
        <div style="font-size:13px;color:var(--sub);line-height:1.7">
          Hubungi <strong style="color:var(--text)">Admin Pabalu</strong> melalui WhatsApp untuk mengajukan aktivasi fitur pembayaran online.
        </div>
      </div>

      <div style="display:flex;gap:12px;align-items:flex-start">
        <div style="width:26px;height:26px;border-radius:8px;background:var(--ac-lt);color:var(--ac);
                    display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0;margin-top:1px">2</div>
        <div style="font-size:13px;color:var(--sub);line-height:1.7">
          Siapkan dokumen persyaratan sesuai ketentuan layanan pembayaran yang berlaku
          (identitas usaha, NPWP jika ada, dan informasi rekening tujuan).
        </div>
      </div>

      <div style="display:flex;gap:12px;align-items:flex-start">
        <div style="width:26px;height:26px;border-radius:8px;background:var(--ac-lt);color:var(--ac);
                    display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0;margin-top:1px">3</div>
        <div style="font-size:13px;color:var(--sub);line-height:1.7">
          Setelah admin memverifikasi dan mengkonfigurasi akun Anda, fitur pembayaran online
          akan tersedia dan Anda dapat mengaktifkannya di halaman ini.
        </div>
      </div>

      <div style="height:1px;background:var(--border)"></div>

      @if($adminWa)
      <a href="{{ $adminWa }}?text={{ urlencode('Halo Admin Pabalu, saya ingin mengaktifkan fitur pembayaran online untuk akun saya. Nama: ' . auth()->user()->name . ' | Email: ' . auth()->user()->email) }}"
        target="_blank" rel="noopener"
        style="display:inline-flex;align-items:center;gap:10px;padding:12px 20px;border-radius:12px;
               background:rgba(37,211,102,.12);border:1px solid rgba(37,211,102,.25);
               color:#25d366;font-size:13.5px;font-weight:600;text-decoration:none;
               align-self:flex-start;transition:background .15s"
        onmouseover="this.style.background='rgba(37,211,102,.22)'"
        onmouseout="this.style.background='rgba(37,211,102,.12)'">
        <i class="fa-brands fa-whatsapp" style="font-size:18px"></i>
        Hubungi Admin via WhatsApp
      </a>
      @else
      <div style="font-size:12.5px;color:var(--muted)">
        <i class="fa-solid fa-envelope" style="margin-right:6px"></i>admin@pabalu.com
      </div>
      @endif

    </div>
  </div>

</div>

</x-app-layout>
