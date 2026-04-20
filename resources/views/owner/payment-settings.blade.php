<x-app-layout title="Pembayaran Online">

<div style="max-width:680px">

  {{-- Status Card --}}
  <div class="card animate-fadeUp">
    <div class="card-header" style="justify-content:space-between;align-items:center">
      <div class="card-title">
        <i class="fa-solid fa-qrcode" style="color:var(--ac);margin-right:8px"></i>
        Pembayaran Online
      </div>
      @if($isActive)
        <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:7px"></i> Aktif</span>
      @elseif($hasKeys)
        <span class="badge badge-gray">Nonaktif</span>
      @else
        <span class="badge" style="background:rgba(245,158,11,.12);color:#f59e0b">Belum Dikonfigurasi</span>
      @endif
    </div>

    <div class="card-body">
      @if($isActive)
        {{-- Aktif --}}
        <div style="display:flex;align-items:flex-start;gap:16px">
          <div style="width:48px;height:48px;border-radius:14px;background:rgba(52,211,153,.1);color:#34d399;
                      display:grid;place-items:center;font-size:22px;flex-shrink:0">
            <i class="fa-solid fa-circle-check"></i>
          </div>
          <div>
            <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:6px">
              Pembayaran Online Sedang Aktif
            </div>
            <div style="font-size:13px;color:var(--sub);line-height:1.7">
              Pelanggan dapat membayar pesanan melalui QRIS, transfer bank, atau dompet digital
              di halaman order outlet Anda. Pengaturan ini dikelola oleh <strong style="color:var(--text)">Admin Pabalu</strong>.
            </div>
          </div>
        </div>

        <div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div style="padding:12px 14px;border-radius:10px;background:var(--surface2);border:1px solid var(--border)">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px">Metode Tersedia</div>
            <div style="font-size:13px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:6px">
              <i class="fa-solid fa-qrcode" style="color:var(--ac);font-size:12px"></i> QRIS
            </div>
          </div>
          <div style="padding:12px 14px;border-radius:10px;background:var(--surface2);border:1px solid var(--border)">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px">Dikelola Oleh</div>
            <div style="font-size:13px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:6px">
              <i class="fa-solid fa-shield-halved" style="color:var(--ac);font-size:12px"></i> Admin
            </div>
          </div>
        </div>

      @else
        {{-- Tidak aktif / belum dikonfigurasi --}}
        <div style="display:flex;align-items:flex-start;gap:16px">
          <div style="width:48px;height:48px;border-radius:14px;background:rgba(245,158,11,.1);color:#f59e0b;
                      display:grid;place-items:center;font-size:22px;flex-shrink:0">
            <i class="fa-solid fa-gear"></i>
          </div>
          <div>
            <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:6px">
              Pembayaran Online Belum Aktif
            </div>
            <div style="font-size:13px;color:var(--sub);line-height:1.7">
              Fitur ini belum diaktifkan untuk akun Anda. Hanya <strong style="color:var(--text)">Admin Pabalu</strong>
              yang dapat mengaktifkan pembayaran online. Ikuti langkah di bawah untuk mengajukan aktivasi.
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  {{-- Cara Mengaktifkan — hanya tampil jika belum aktif --}}
  @if(!$isActive)
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
          Siapkan dokumen persyaratan: identitas usaha, NPWP (jika ada), dan informasi rekening tujuan dana.
        </div>
      </div>

      <div style="display:flex;gap:12px;align-items:flex-start">
        <div style="width:26px;height:26px;border-radius:8px;background:var(--ac-lt);color:var(--ac);
                    display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0;margin-top:1px">3</div>
        <div style="font-size:13px;color:var(--sub);line-height:1.7">
          Setelah Admin memverifikasi dan mengkonfigurasi akun Anda, pembayaran online akan aktif secara otomatis
          dan status di halaman ini akan berubah menjadi <strong style="color:#34d399">Aktif</strong>.
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
        <i class="fa-solid fa-envelope" style="margin-right:6px"></i>
        Hubungi administrator sistem Pabalu untuk informasi lebih lanjut.
      </div>
      @endif

    </div>
  </div>
  @endif

</div>

</x-app-layout>
