<x-laundry-layout :outlet="$outlet" pageTitle="Detail Pesanan #{{ $laundryOrder->order_number }}">

<style>
.info-row{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)}
.info-row:last-child{border-bottom:none}
.info-icon{width:32px;height:32px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:13px;flex-shrink:0;margin-top:1px}
.info-label{font-size:11px;font-weight:600;color:var(--muted);margin-bottom:2px}
.info-value{font-size:14px;font-weight:500;color:var(--text)}
.step-line{display:flex;align-items:center;gap:0;flex:1}
.step-node{display:flex;flex-direction:column;align-items:center;gap:6px}
.step-circle{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;font-size:13px;border:2px solid var(--border);flex-shrink:0}
.step-label{font-size:10.5px;font-weight:600;text-align:center;white-space:nowrap}
.step-connector{flex:1;height:2px;background:var(--border);margin-bottom:22px}
.step-done .step-circle{background:var(--ac);border-color:var(--ac);color:#fff}
.step-done .step-label{color:var(--ac)}
.step-active .step-circle{background:var(--ac-lt);border-color:var(--ac);color:var(--ac)}
.step-active .step-label{color:var(--ac)}
.step-done + .step-connector,.step-active + .step-connector{background:var(--ac)}
.two-detail{display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start}
@media(max-width:900px){.two-detail{grid-template-columns:1fr!important}}
</style>

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
  <a href="{{ $outlet->route('laundry-orders.index') }}" style="color:var(--muted);text-decoration:none">Pesanan Laundry</a>
  <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
  <span style="color:var(--text);font-weight:600">{{ $laundryOrder->order_number }}</span>
</div>

<div class="two-detail">

  {{-- Kolom Kiri --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Header Card --}}
    <div class="card">
      <div class="card-header">
        <div>
          <div style="font-size:11px;color:var(--muted);margin-bottom:3px">Nomor Pesanan</div>
          <div class="font-display" style="font-size:22px;font-weight:700;color:var(--ac)">{{ $laundryOrder->order_number }}</div>
        </div>
        <span class="badge {{ \App\Models\LaundryOrder::$statusColors[$laundryOrder->status] ?? 'badge-gray' }}" style="font-size:13px;padding:6px 14px">
          {{ \App\Models\LaundryOrder::$statusLabels[$laundryOrder->status] ?? $laundryOrder->status }}
        </span>
      </div>

      {{-- Status Stepper --}}
      <div style="padding:20px;display:flex;align-items:flex-start;gap:0">
        @php
          $steps = ['masuk'=>['icon'=>'fa-inbox','label'=>'Masuk'], 'proses'=>['icon'=>'fa-rotate','label'=>'Diproses'], 'selesai'=>['icon'=>'fa-circle-check','label'=>'Selesai'], 'diambil'=>['icon'=>'fa-check-double','label'=>'Diambil']];
          $statusOrder = array_keys($steps);
          $currentIdx = array_search($laundryOrder->status, $statusOrder);
        @endphp
        @foreach($steps as $sKey => $sInfo)
          @php $idx = array_search($sKey, $statusOrder); @endphp
          @if(!$loop->first)
          <div class="step-connector {{ $idx <= $currentIdx ? 'step-done' : '' }}" style="{{ $idx <= $currentIdx ? 'background:var(--ac)' : '' }}"></div>
          @endif
          <div class="step-node {{ $idx < $currentIdx ? 'step-done' : ($idx == $currentIdx ? 'step-active' : '') }}">
            <div class="step-circle">
              <i class="fa-solid {{ $sInfo['icon'] }}"></i>
            </div>
            <div class="step-label" style="{{ $idx <= $currentIdx ? 'color:var(--ac)' : 'color:var(--muted)' }}">{{ $sInfo['label'] }}</div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Items / Layanan --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-list" style="color:var(--ac);margin-right:8px"></i>Item Cucian</span>
        <span style="font-size:12px;color:var(--muted)">{{ $laundryOrder->items->count() }} item</span>
      </div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr>
              <th>Layanan</th>
              <th style="text-align:center">Qty</th>
              <th style="text-align:right">Harga/Unit</th>
              <th style="text-align:right">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @foreach($laundryOrder->items as $item)
            <tr>
              <td class="td-main">
                {{ $item->product_name }}
                @if($item->item_notes)
                <div style="font-size:11px;color:var(--muted);margin-top:2px">{{ $item->item_notes }}</div>
                @endif
              </td>
              <td style="text-align:center;color:var(--text)">
                {{ number_format($item->qty, $item->qty == (int)$item->qty ? 0 : 2) }}
                @if($item->unit) <span style="font-size:11px;color:var(--muted)">{{ $item->unit }}</span> @endif
              </td>
              <td style="text-align:right">Rp {{ number_format($item->product_price) }}</td>
              <td style="text-align:right;font-weight:600;color:var(--text)">Rp {{ number_format($item->subtotal) }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="border-top:2px solid var(--border)">
              <td colspan="3" style="text-align:right;font-weight:600;color:var(--sub);padding:12px 14px">Total</td>
              <td style="text-align:right;font-weight:700;font-size:16px;color:var(--text);padding:12px 14px">Rp {{ number_format($laundryOrder->total) }}</td>
            </tr>
            @if($laundryOrder->paid_at)
            <tr>
              <td colspan="3" style="text-align:right;color:var(--muted);font-size:12px;padding:4px 14px">
                Dibayar ({{ \App\Models\LaundryOrder::$paymentLabels[$laundryOrder->payment_method] ?? $laundryOrder->payment_method }})
              </td>
              <td style="text-align:right;color:#34d399;font-size:12px;font-weight:600;padding:4px 14px">
                Rp {{ number_format($laundryOrder->payment_amount) }}
              </td>
            </tr>
            @if($laundryOrder->change_amount > 0)
            <tr>
              <td colspan="3" style="text-align:right;color:var(--muted);font-size:12px;padding:4px 14px">Kembalian</td>
              <td style="text-align:right;color:#fbbf24;font-size:12px;font-weight:600;padding:4px 14px">
                Rp {{ number_format($laundryOrder->change_amount) }}
              </td>
            </tr>
            @endif
            @endif
          </tfoot>
        </table>
      </div>
    </div>

    {{-- Catatan & Info Tambahan --}}
    @if($laundryOrder->notes)
    <div class="card card-body">
      <div style="display:flex;align-items:flex-start;gap:10px">
        <div style="width:32px;height:32px;border-radius:9px;background:rgba(245,158,11,.12);color:#fbbf24;display:grid;place-items:center;flex-shrink:0;font-size:13px">
          <i class="fa-solid fa-note-sticky"></i>
        </div>
        <div>
          <div style="font-size:11px;font-weight:600;color:var(--muted);margin-bottom:4px">Catatan Khusus</div>
          <div style="font-size:14px;color:var(--text);white-space:pre-line">{{ $laundryOrder->notes }}</div>
        </div>
      </div>
    </div>
    @endif

  </div>

  {{-- Kolom Kanan --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Info Pelanggan --}}
    <div class="card card-body">
      <div class="card-title" style="margin-bottom:14px"><i class="fa-solid fa-user" style="color:var(--ac);margin-right:8px"></i>Pelanggan</div>
      <div class="info-row">
        <div class="info-icon"><i class="fa-solid fa-user"></i></div>
        <div><div class="info-label">Nama</div><div class="info-value">{{ $laundryOrder->customer_name }}</div></div>
      </div>
      @if($laundryOrder->customer_phone)
      <div class="info-row">
        <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
        <div>
          <div class="info-label">Nomor HP</div>
          <a href="tel:{{ $laundryOrder->customer_phone }}" class="info-value" style="color:var(--ac);text-decoration:none">{{ $laundryOrder->customer_phone }}</a>
        </div>
      </div>
      @endif
    </div>

    {{-- Info Laundry --}}
    <div class="card card-body">
      <div class="card-title" style="margin-bottom:14px"><i class="fa-solid fa-shirt" style="color:var(--ac);margin-right:8px"></i>Info Laundry</div>
      @if($laundryOrder->weight_kg)
      <div class="info-row">
        <div class="info-icon"><i class="fa-solid fa-weight-hanging"></i></div>
        <div><div class="info-label">Berat</div><div class="info-value">{{ number_format($laundryOrder->weight_kg, 1) }} kg</div></div>
      </div>
      @endif
      <div class="info-row">
        <div class="info-icon"><i class="fa-solid fa-clock"></i></div>
        <div><div class="info-label">Tanggal Masuk</div><div class="info-value">{{ $laundryOrder->created_at->format('d M Y, H:i') }}</div></div>
      </div>
      @if($laundryOrder->estimated_done_at)
      <div class="info-row">
        <div class="info-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div>
          <div class="info-label">Estimasi Selesai</div>
          <div class="info-value" style="{{ $laundryOrder->status !== 'diambil' && $laundryOrder->estimated_done_at->isPast() ? 'color:#f87171' : '' }}">
            {{ $laundryOrder->estimated_done_at->format('d M Y, H:i') }}
            @if($laundryOrder->status !== 'diambil' && $laundryOrder->estimated_done_at->isPast())
            <span style="font-size:11px;background:rgba(239,68,68,.12);color:#f87171;padding:2px 8px;border-radius:99px;margin-left:4px">Terlambat</span>
            @endif
          </div>
        </div>
      </div>
      @endif
      @if($laundryOrder->paid_at)
      <div class="info-row">
        <div class="info-icon"><i class="fa-solid fa-circle-check" style="color:#34d399"></i></div>
        <div><div class="info-label">Diambil & Dibayar</div><div class="info-value">{{ $laundryOrder->paid_at->format('d M Y, H:i') }}</div></div>
      </div>
      @endif
      <div class="info-row" style="border-bottom:none">
        <div class="info-icon"><i class="fa-solid fa-user-tie"></i></div>
        <div><div class="info-label">Diterima oleh</div><div class="info-value">{{ $laundryOrder->user?->name ?? 'Sistem' }}</div></div>
      </div>
    </div>

    {{-- Aksi --}}
    @if($laundryOrder->status !== 'diambil')
    <div class="card card-body" style="display:flex;flex-direction:column;gap:10px">
      <div class="card-title" style="margin-bottom:4px">Aksi</div>

      {{-- Maju Status --}}
      @if($laundryOrder->nextStatus())
      @php
        $nextLabel = $laundryOrder->nextStatusLabel();
        $nextIcon  = ['proses'=>'fa-rotate','selesai'=>'fa-circle-check'][$laundryOrder->nextStatus()] ?? 'fa-arrow-right';
      @endphp
      <button id="btn-next-status" onclick="updateStatus()"
        style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:11px;background:var(--ac);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit">
        <i class="fa-solid {{ $nextIcon }}"></i>
        Tandai: {{ $nextLabel }}
      </button>
      @endif

      {{-- Bayar & Ambil --}}
      @if($laundryOrder->status === 'selesai')
      <button onclick="openModal('modal-pay'); selectPayMethod('cash');"
        style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:11px;background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.3);border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit">
        <i class="fa-solid fa-hand-holding-dollar"></i> Terima Pembayaran
      </button>
      @endif

      {{-- Cetak Struk Masuk --}}
      <a href="{{ $outlet->route('laundry-orders.receipt', [$laundryOrder->id]) }}" target="_blank"
        style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:10px;background:var(--surface2);color:var(--sub);border:1px solid var(--border);border-radius:12px;font-size:13px;font-weight:600;text-decoration:none">
        <i class="fa-solid fa-print"></i> Cetak Struk Masuk
      </a>

      {{-- Hapus --}}
      <button onclick="confirmDelete()"
        style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:10px;background:none;color:#f87171;border:1px solid rgba(239,68,68,.3);border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
        <i class="fa-solid fa-trash"></i> Batalkan Pesanan
      </button>
    </div>
    @else
    <div class="card card-body" style="display:flex;flex-direction:column;gap:10px">
      <a href="{{ $outlet->route('laundry-orders.receipt', [$laundryOrder->id]) }}" target="_blank"
        style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:10px;background:var(--surface2);color:var(--sub);border:1px solid var(--border);border-radius:12px;font-size:13px;font-weight:600;text-decoration:none">
        <i class="fa-solid fa-print"></i> Cetak Struk
      </a>
    </div>
    @endif

  </div>
</div>

{{-- Modal Bayar --}}
@php
  $payButtons = [['cash','Tunai','fa-money-bill-wave']];
  $pmIcons    = ['qris_transfer'=>'fa-qrcode','qris_pay'=>'fa-qrcode','transfer'=>'fa-building-columns','card'=>'fa-credit-card'];
  foreach($outlet->activePaymentMethods() as $pmCode => $pmInfo) {
    $payButtons[] = [$pmCode, $pmInfo[0], $pmIcons[$pmCode] ?? 'fa-credit-card'];
  }
  $payColCount = min(count($payButtons), 4);
@endphp
<div id="modal-pay" class="modal-backdrop">
  <div class="modal-box" style="max-width:420px">

    {{-- Header --}}
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
          <i class="fa-solid fa-cash-register" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Terima Pembayaran
        </div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">Pesanan #{{ $laundryOrder->order_number }}</div>
      </div>
      <button onclick="closeModal('modal-pay')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer;color:var(--sub);font-size:13px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="padding:18px 22px;display:flex;flex-direction:column;gap:16px">

      {{-- Total --}}
      <div style="text-align:center;padding:16px;background:var(--ac-lt);border-radius:14px">
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px">Total Tagihan</div>
        <div class="font-display" style="font-size:32px;font-weight:800;color:var(--ac)">Rp {{ number_format($laundryOrder->total) }}</div>
      </div>

      {{-- Metode Pembayaran — button grid --}}
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">Metode Pembayaran</div>
        <div style="display:grid;grid-template-columns:repeat({{ $payColCount }},1fr);gap:8px">
          @foreach($payButtons as [$pmCode,$pmLabel,$pmIcon])
          <button class="pay-method-btn" data-method="{{ $pmCode }}" onclick="selectPayMethod('{{ $pmCode }}')"
            style="padding:10px 6px;border-radius:12px;border:2px solid var(--border);background:var(--surface2);cursor:pointer;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:6px">
            <i class="fa-solid {{ $pmIcon }}" style="font-size:17px;color:var(--muted)"></i>
            <span style="font-size:10.5px;font-weight:700;color:var(--sub);white-space:nowrap">{{ $pmLabel }}</span>
          </button>
          @endforeach
        </div>
      </div>

      {{-- Seksi Tunai --}}
      <div id="pay-cash-section" style="display:flex;flex-direction:column;gap:10px">
        <div>
          <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">Uang Diterima</div>
          <div style="position:relative">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--muted);font-weight:600">Rp</span>
            <input id="pay-amount" type="text" inputmode="numeric" placeholder="0"
              style="width:100%;background:var(--surface2);border:2px solid var(--ac);border-radius:12px;padding:10px 10px 10px 34px;font-size:20px;font-weight:700;color:var(--text);outline:none;font-family:inherit"
              oninput="calcChange()">
          </div>
        </div>
        {{-- Quick amounts --}}
        <div id="pay-quick-amounts" style="display:flex;gap:6px;flex-wrap:wrap"></div>
        {{-- Kembalian --}}
        <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 14px;border-radius:10px;background:var(--surface2)">
          <span style="font-size:13px;color:var(--sub);font-weight:600">Kembalian</span>
          <span id="pay-change" class="font-display" style="font-size:17px;font-weight:800;color:#34d399">Rp 0</span>
        </div>
      </div>

      {{-- Seksi Non-Tunai --}}
      <div id="pay-noncash-section" style="display:none">
        <div style="text-align:center;padding:16px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
          <i class="fa-solid fa-circle-check" style="font-size:30px;color:#34d399;margin-bottom:10px;display:block"></i>
          <div style="font-size:13.5px;color:var(--sub)">Konfirmasi pembayaran
            <span id="pay-method-label" style="font-weight:700;color:var(--text)"></span>
          </div>
          <div class="font-display" style="font-size:20px;font-weight:800;color:var(--text);margin-top:6px">
            Rp {{ number_format($laundryOrder->total) }}
          </div>
        </div>
      </div>

    </div>

    {{-- Footer --}}
    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
      <button onclick="closeModal('modal-pay')"
        style="flex:0 0 auto;padding:11px 18px;border-radius:12px;border:1px solid var(--border);background:none;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">
        Batal
      </button>
      <button id="btn-confirm-pay" onclick="confirmPay()"
        style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:linear-gradient(135deg,var(--ac),var(--ac2,#b91c2c));color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
        <i class="fa-solid fa-circle-check"></i> Konfirmasi Pembayaran
      </button>
    </div>

  </div>
</div>

{{-- Modal Konfirmasi Ubah Status --}}
@if($laundryOrder->nextStatus())
@php
  $nextSt     = $laundryOrder->nextStatus();
  $nextLbl    = \App\Models\LaundryOrder::$statusLabels[$nextSt] ?? $nextSt;
  $nextConfig = [
    'proses'  => [
      'icon'    => 'fa-rotate',
      'color'   => '#f59e0b',
      'bg'      => 'rgba(245,158,11,.12)',
      'btnBg'   => '#f59e0b',
      'desc'    => 'Cucian akan ditandai sedang dalam proses pencucian.',
      'hint'    => 'Pelanggan bisa melihat status ini melalui struk QR mereka.',
    ],
    'selesai' => [
      'icon'    => 'fa-circle-check',
      'color'   => '#10b981',
      'bg'      => 'rgba(16,185,129,.12)',
      'btnBg'   => '#10b981',
      'desc'    => 'Cucian sudah selesai dan siap diambil pelanggan.',
      'hint'    => 'Setelah ini Anda dapat menerima pembayaran dari pelanggan.',
    ],
  ][$nextSt] ?? ['icon'=>'fa-arrow-right','color'=>'var(--ac)','bg'=>'var(--ac-lt)','btnBg'=>'var(--ac)','desc'=>'','hint'=>''];
@endphp
<div id="modal-confirm-status" class="modal-backdrop">
  <div class="modal-box" style="max-width:360px;text-align:center;padding:0;overflow:hidden">

    {{-- Ikon besar --}}
    <div style="padding:32px 28px 20px;display:flex;flex-direction:column;align-items:center;gap:14px">
      <div style="width:68px;height:68px;border-radius:50%;background:{{ $nextConfig['bg'] }};display:grid;place-items:center">
        <i class="fa-solid {{ $nextConfig['icon'] }}" style="font-size:28px;color:{{ $nextConfig['color'] }}"></i>
      </div>
      <div>
        <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">
          Tandai {{ $nextLbl }}?
        </div>
        <div style="font-size:13px;color:var(--sub);line-height:1.5">
          {{ $nextConfig['desc'] }}
        </div>
      </div>

      {{-- Info hint --}}
      @if($nextConfig['hint'])
      <div style="width:100%;padding:10px 14px;background:var(--surface2);border-radius:10px;font-size:12px;color:var(--muted);text-align:left;display:flex;gap:8px;align-items:flex-start">
        <i class="fa-solid fa-circle-info" style="color:{{ $nextConfig['color'] }};margin-top:1px;flex-shrink:0"></i>
        <span>{{ $nextConfig['hint'] }}</span>
      </div>
      @endif
    </div>

    {{-- Pesanan info strip --}}
    <div style="margin:0 28px 20px;background:var(--surface2);border-radius:12px;padding:10px 14px;text-align:left;display:flex;justify-content:space-between;align-items:center">
      <div>
        <div style="font-size:10px;color:var(--muted);font-weight:600;margin-bottom:2px">PESANAN</div>
        <div class="font-display" style="font-size:13px;font-weight:700;color:var(--ac)">{{ $laundryOrder->order_number }}</div>
      </div>
      <div style="text-align:right">
        <div style="font-size:10px;color:var(--muted);font-weight:600;margin-bottom:2px">PELANGGAN</div>
        <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $laundryOrder->customer_name }}</div>
      </div>
    </div>

    {{-- Tombol --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;border-top:1px solid var(--border)">
      <button onclick="closeModal('modal-confirm-status')"
        style="padding:14px;border:none;border-right:1px solid var(--border);background:none;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit;border-radius:0 0 0 20px">
        Batal
      </button>
      <button id="btn-confirm-status" onclick="doUpdateStatus()"
        style="padding:14px;border:none;background:{{ $nextConfig['btnBg'] }};color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;border-radius:0 0 20px 0;display:flex;align-items:center;justify-content:center;gap:8px">
        <i class="fa-solid {{ $nextConfig['icon'] }}"></i>
        Ya, Tandai
      </button>
    </div>
  </div>
</div>
@endif

{{-- Form Delete (hidden) --}}
<form id="form-delete" method="POST" action="{{ $outlet->route('laundry-orders.destroy', [$laundryOrder->id]) }}" style="display:none">
  @csrf @method('DELETE')
</form>

@push('scripts')
<script>
const STATUS_URL = '{{ $outlet->route('laundry-orders.update-status', [$laundryOrder->id]) }}';
const PAY_URL    = '{{ $outlet->route('laundry-orders.pay', [$laundryOrder->id]) }}';
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
const TOTAL      = {{ $laundryOrder->total }};

function fmtRp(n){ return 'Rp '+Number(n).toLocaleString('id-ID'); }

function updateStatus() {
  openModal('modal-confirm-status');
}

async function doUpdateStatus() {
  const confirmBtn = document.getElementById('btn-confirm-status');
  const triggerBtn = document.getElementById('btn-next-status');

  if (confirmBtn) {
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
  }

  try {
    const res  = await fetch(STATUS_URL, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Gagal.');

    closeModal('modal-confirm-status');
    showToast('success', 'Status diubah ke: ' + data.status_label);
    setTimeout(() => location.reload(), 700);
  } catch(err) {
    closeModal('modal-confirm-status');
    showToast('error', err.message);
    if (confirmBtn) {
      confirmBtn.disabled = false;
      confirmBtn.innerHTML = confirmBtn.dataset.origHtml || 'Ya, Tandai';
    }
  }
}

/* ── Pembayaran ── */
let currentPayMethod = 'cash';

const PAY_METHOD_LABELS = @json(array_merge(
  ['cash' => 'Tunai'],
  array_map(fn($pm) => $pm[0], $outlet->activePaymentMethods())
));

function selectPayMethod(m) {
  currentPayMethod = m;
  const isCash = m === 'cash';

  // Highlight tombol terpilih
  document.querySelectorAll('.pay-method-btn').forEach(btn => {
    const sel = btn.dataset.method === m;
    btn.style.borderColor = sel ? 'var(--ac)' : 'var(--border)';
    btn.style.background  = sel ? 'var(--ac-lt)' : 'var(--surface2)';
    btn.querySelector('i').style.color   = sel ? 'var(--ac)' : 'var(--muted)';
    btn.querySelector('span').style.color = sel ? 'var(--ac)' : 'var(--sub)';
  });

  // Tampilkan section yang sesuai
  document.getElementById('pay-cash-section').style.display   = isCash ? 'flex' : 'none';
  document.getElementById('pay-noncash-section').style.display = isCash ? 'none' : 'block';

  if (!isCash) {
    const lbl = PAY_METHOD_LABELS[m] || m;
    document.getElementById('pay-method-label').textContent = lbl;
  } else {
    buildQuickAmounts();
    document.getElementById('pay-amount').value = '';
    calcChange();
  }
}

function buildQuickAmounts() {
  const container = document.getElementById('pay-quick-amounts');
  const rounds    = [TOTAL, ...([5000,10000,20000,50000,100000].map(r => Math.ceil(TOTAL / r) * r).filter(v => v >= TOTAL))];
  const unique    = [...new Set(rounds)].slice(0, 5);
  container.innerHTML = unique.map(amt =>
    `<button type="button" onclick="setQuickAmount(${amt})"
      style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .12s"
      onmouseover="this.style.borderColor='var(--ac)';this.style.color='var(--ac)'"
      onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--sub)'">
      ${fmtRp(amt)}
    </button>`
  ).join('');
}

function setQuickAmount(amt) {
  document.getElementById('pay-amount').value = amt.toLocaleString('id-ID');
  calcChange();
}

function calcChange() {
  const raw    = document.getElementById('pay-amount').value.replace(/\D/g,'');
  const paid   = parseInt(raw) || 0;
  const change = Math.max(0, paid - TOTAL);
  document.getElementById('pay-change').textContent = fmtRp(change);
  document.getElementById('pay-change').style.color = change >= 0 ? '#34d399' : '#f87171';
}


async function confirmPay() {
  const btn    = document.getElementById('btn-confirm-pay');
  const method = currentPayMethod;
  const raw    = document.getElementById('pay-amount').value.replace(/\D/g,'');
  const amount = method === 'cash' ? (parseInt(raw) || 0) : TOTAL;

  if (method === 'cash' && amount < TOTAL) {
    showToast('error', 'Uang diterima kurang dari total tagihan.');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

  try {
    const res  = await fetch(PAY_URL, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ payment_method: method, payment_amount: amount })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Gagal.');
    closeModal('modal-pay');
    showToast('success', 'Pembayaran berhasil dicatat!');
    setTimeout(() => { window.open(data.receipt_url + '?autoprint=1', '_blank'); location.reload(); }, 700);
  } catch(err) {
    showToast('error', err.message);
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Konfirmasi Pembayaran';
  }
}

function confirmDelete() {
  if (confirm('Yakin ingin membatalkan/menghapus pesanan ini? Tindakan ini tidak dapat diurungkan.')) {
    document.getElementById('form-delete').submit();
  }
}
</script>
@endpush

</x-laundry-layout>
