<x-app-layout title="Metode Pembayaran">

@push('styles')
<style>
.toggle-wrap{display:flex;align-items:center;gap:10px}
.toggle{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-slider{position:absolute;inset:0;border-radius:99px;background:var(--border);cursor:pointer;transition:background .2s}
.toggle-slider:before{content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:transform .2s}
.toggle input:checked + .toggle-slider{background:var(--ac)}
.toggle input:checked + .toggle-slider:before{transform:translateX(20px)}
.pm-card{
  display:flex;align-items:center;justify-content:space-between;gap:16px;
  padding:16px 20px;border-radius:14px;border:1.5px solid var(--border);
  background:var(--surface);transition:border-color .15s,background .15s;
}
.pm-card.active{border-color:rgba(var(--ac-rgb),.35);background:var(--surface2)}
.pm-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:18px;flex-shrink:0}
</style>
@endpush

<div style="max-width:620px">

  <form method="POST" action="{{ route('owner.payment-methods.update') }}">
    @csrf @method('PUT')

    <div class="card animate-fadeUp">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-wallet" style="color:var(--ac);margin-right:8px"></i>
          Metode Pembayaran
        </div>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:10px">

        <div style="font-size:12.5px;color:var(--muted);margin-bottom:4px">
          Pilih metode pembayaran yang tersedia di POS/Kasir. Minimal satu metode harus aktif.
        </div>

        @foreach($methods as $key => $meta)
        @php
          $isActive   = $enabled[$key];
          $isRequired = $meta['required'] ?? false;
          $isGateway  = $meta['gateway'] ?? false;
          $disabled   = $isRequired || ($isGateway && !$gatewayConfigured);
        @endphp

        <div class="pm-card {{ $isActive && !($isGateway && !$gatewayConfigured) ? 'active' : '' }}" id="pm-card-{{ $key }}">
          <div style="display:flex;align-items:center;gap:14px">
            <div class="pm-icon" style="background:{{ $meta['color'] }}18;color:{{ $meta['color'] }}">
              <i class="fa-solid {{ $meta['icon'] }}"></i>
            </div>
            <div>
              <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $meta['label'] }}</div>
              @if($isRequired)
                <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Wajib — tidak dapat dinonaktifkan</div>
              @elseif($isGateway && !$gatewayConfigured)
                <div style="font-size:11.5px;color:#f59e0b;margin-top:2px">
                  Belum dikonfigurasi —
                  <a href="{{ route('owner.payment-settings.index') }}" style="color:#818cf8;text-decoration:underline">Lihat cara aktivasi</a>
                </div>
              @elseif($isGateway)
                <div style="font-size:11.5px;color:var(--muted);margin-top:2px">QRIS, Transfer Bank, E-Wallet melalui Payment Gateway</div>
              @else
                <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Pembayaran langsung</div>
              @endif
            </div>
          </div>
          <div class="toggle-wrap">
            <label class="toggle">
              <input type="checkbox" name="pm_{{ $key }}" value="1" id="pm-{{ $key }}"
                {{ ($isActive && !($isGateway && !$gatewayConfigured)) ? 'checked' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                onchange="updateCard('{{ $key }}', this.checked)">
              <span class="toggle-slider" style="{{ $disabled ? 'opacity:.5;cursor:not-allowed' : '' }}"></span>
            </label>
          </div>
        </div>
        @endforeach

      </div>
      <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Simpan
        </button>
      </div>
    </div>

  </form>

  {{-- Info --}}
  <div class="card animate-fadeUp d2">
    <div class="card-body" style="display:flex;gap:12px;align-items:flex-start;font-size:13px;color:var(--sub);line-height:1.7">
      <i class="fa-solid fa-circle-info" style="color:#818cf8;margin-top:2px;flex-shrink:0"></i>
      <div>
        Metode yang dinonaktifkan tidak akan muncul di tampilan kasir.
        <strong style="color:var(--text)">Tunai</strong> selalu tersedia sebagai fallback.
        Untuk mengaktifkan <strong style="color:var(--text)">Payment Gateway</strong>, hubungi admin terlebih dahulu melalui menu
        <a href="{{ route('owner.payment-settings.index') }}" style="color:var(--ac)">Pembayaran Online</a>.
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
function updateCard(key, on) {
  var card = document.getElementById('pm-card-' + key);
  if (on) card.classList.add('active'); else card.classList.remove('active');
}
</script>
@endpush

</x-app-layout>
