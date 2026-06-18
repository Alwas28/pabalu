<x-outlet-layout :outlet="$outlet" pageTitle="Detail Transaksi">

@php
  $methods = ['cash'=>['Tunai','fa-money-bill-wave','#34d399','rgba(16,185,129,.15)'],'qris'=>['QRIS','fa-qrcode','#60a5fa','rgba(99,162,241,.15)'],'transfer'=>['Transfer','fa-building-columns','#a78bfa','rgba(139,92,246,.15)'],'card'=>['Kartu','fa-credit-card','#fbbf24','rgba(245,158,11,.15)']];
  [$ml,$mi,$mc,$mb] = $methods[$transaction->payment_method] ?? ['Lainnya','fa-circle-question','#94a3b8','rgba(148,163,184,.12)'];
  $isVoided = $transaction->status === 'voided';
@endphp

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div style="display:flex;align-items:center;gap:14px">
    <a href="{{ $outlet->route('transactions.index') }}"
      style="width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:var(--surface);display:grid;place-items:center;color:var(--sub);text-decoration:none;font-size:14px;flex-shrink:0;transition:all .15s"
      onmouseover="this.style.borderColor='var(--ac)';this.style.color='var(--ac)'"
      onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--sub)'">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text);font-family:monospace">
          {{ $transaction->transaction_number }}
        </h2>
        @if($isVoided)
          <span class="badge badge-red"><i class="fa-solid fa-ban" style="font-size:9px"></i> VOID</span>
        @else
          <span class="badge badge-green"><i class="fa-solid fa-circle-check" style="font-size:9px"></i> Selesai</span>
        @endif
      </div>
      <p style="font-size:13px;color:var(--muted);margin-top:3px">
        {{ $transaction->date->translatedFormat('l, d F Y') }} · {{ $transaction->created_at->format('H:i') }}
      </p>
    </div>
  </div>

  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="{{ $outlet->route('transactions.receipt', [$transaction]) }}?autoprint=1"
       target="_blank"
       style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .15s"
       onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
      <i class="fa-solid fa-print" style="font-size:11px"></i> Cetak Struk
    </a>
    @if($canVoid)
    <button onclick="document.getElementById('modal-void').classList.add('open')"
      style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;border:1px solid rgba(239,68,68,.3);background:transparent;color:#f87171;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s"
      onmouseover="this.style.background='rgba(239,68,68,.08)'" onmouseout="this.style.background='transparent'">
      <i class="fa-solid fa-ban" style="font-size:11px"></i> Void Transaksi
    </button>
    @endif
  </div>
</div>

{{-- ── VOID BANNER ── --}}
@if($isVoided)
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:14px;padding:14px 18px;display:flex;align-items:center;gap:12px" class="animate-fadeUp d1">
  <i class="fa-solid fa-triangle-exclamation" style="color:#f87171;font-size:16px;flex-shrink:0"></i>
  <div>
    <div style="font-size:13.5px;font-weight:700;color:#f87171">Transaksi Ini Telah Divoid</div>
    <div style="font-size:12.5px;color:var(--muted);margin-top:2px">Stok produk telah dikembalikan secara otomatis saat void dilakukan.</div>
  </div>
</div>
@endif

{{-- ── TWO-COL ── --}}
<div class="two-col animate-fadeUp d2">

{{-- LEFT: Items table --}}
<div class="card">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-list" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Item Pesanan</span>
    <span style="font-size:12px;color:var(--muted)">{{ $transaction->items->count() }} produk</span>
  </div>
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Produk</th>
          <th style="text-align:center;width:80px">Qty</th>
          <th style="text-align:right;width:130px">Harga Satuan</th>
          <th style="text-align:right;width:130px">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($transaction->items as $i => $item)
        <tr>
          <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
          <td class="td-main">{{ $item->product_name }}</td>
          <td style="text-align:center;font-weight:600;color:var(--text)">{{ $item->qty }}</td>
          <td style="text-align:right;color:var(--sub)">Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
          <td style="text-align:right;font-weight:600;color:var(--text)">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="border-top:2px solid var(--border)">
          <td colspan="4" style="text-align:right;padding:12px 14px;font-size:13px;color:var(--sub);font-weight:600">Subtotal</td>
          <td style="text-align:right;padding:12px 14px;font-size:14px;font-weight:700;color:var(--text)">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->discount_amount > 0)
        <tr>
          <td colspan="4" style="text-align:right;padding:4px 14px;font-size:13px;color:#34d399;font-weight:600">
            Diskon{{ $transaction->discount_percent > 0 ? ' ('.$transaction->discount_percent.'%)' : '' }}
          </td>
          <td style="text-align:right;padding:4px 14px;font-size:13px;font-weight:700;color:#34d399">−Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr style="background:var(--ac-lt)">
          <td colspan="4" style="text-align:right;padding:14px;font-size:16px;font-weight:800;color:var(--ac);font-family:'Clash Display',sans-serif">TOTAL</td>
          <td style="text-align:right;padding:14px;font-size:18px;font-weight:800;color:var(--ac);font-family:'Clash Display',sans-serif">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>{{-- end left --}}

{{-- RIGHT: Info sidebar --}}
<div style="display:flex;flex-direction:column;gap:16px">

  {{-- Pembayaran --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-wallet" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Pembayaran</span>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:12px">

      <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:12px;background:{{ $mb }};border:1px solid {{ $mc }}30">
        <div style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid {{ $mi }}" style="font-size:18px;color:{{ $mc }}"></i>
          <div>
            <div style="font-size:14px;font-weight:700;color:{{ $mc }}">{{ $ml }}</div>
            <div style="font-size:11px;color:var(--muted)">Metode Pembayaran</div>
          </div>
        </div>
        <div style="font-size:15px;font-weight:800;color:var(--text)">Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</div>
      </div>

      @if($transaction->payment_method === 'cash' && $transaction->change_amount > 0)
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:var(--surface2);border-radius:10px">
        <span style="font-size:13px;color:var(--sub)">Kembalian</span>
        <span style="font-size:14px;font-weight:700;color:#34d399">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
      </div>
      @endif

      <div style="display:flex;flex-direction:column;gap:8px;padding:12px 14px;background:var(--surface2);border-radius:12px">
        <div style="display:flex;justify-content:space-between;font-size:13px">
          <span style="color:var(--sub)">Subtotal</span>
          <span style="color:var(--text);font-weight:500">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($transaction->discount_amount > 0)
        <div style="display:flex;justify-content:space-between;font-size:13px">
          <span style="color:#34d399">Diskon</span>
          <span style="color:#34d399;font-weight:600">−Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;border-top:1px solid var(--border);padding-top:8px;margin-top:2px">
          <span style="color:var(--text)">Total</span>
          <span style="color:var(--ac)">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Info transaksi --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-circle-info" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Informasi</span>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
      @php
        $rows = [
          ['No. Transaksi', $transaction->transaction_number, 'monospace'],
          ['Tanggal',       $transaction->date->translatedFormat('d F Y'), null],
          ['Waktu',         $transaction->created_at->format('H:i:s'), null],
          ['Kasir',         $transaction->user->name ?? '—', null],
          ['Outlet',        $outlet->name, null],
        ];
      @endphp
      @foreach($rows as [$label, $val, $font])
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--surface2);border-radius:10px;gap:8px">
        <span style="font-size:12px;color:var(--muted);flex-shrink:0">{{ $label }}</span>
        <span style="font-size:13px;font-weight:600;color:var(--text);text-align:right;{{ $font ? 'font-family:'.$font : '' }}">{{ $val }}</span>
      </div>
      @endforeach
      @if($transaction->notes)
      <div style="padding:10px 12px;background:var(--surface2);border-radius:10px">
        <div style="font-size:12px;color:var(--muted);margin-bottom:4px">Catatan</div>
        <div style="font-size:13px;color:var(--text)">{{ $transaction->notes }}</div>
      </div>
      @endif
    </div>
  </div>

</div>{{-- end right --}}
</div>{{-- end two-col --}}

{{-- ── MODAL VOID ── --}}
@if($canVoid)
<div id="modal-void" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:420px">
    <div style="padding:20px 22px;border-bottom:1px solid var(--border)">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:#f87171">
        <i class="fa-solid fa-ban" style="margin-right:8px;font-size:13px"></i>Void Transaksi
      </h3>
    </div>
    <div style="padding:20px 22px">
      <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:14px 16px;margin-bottom:16px">
        <p style="font-size:13.5px;color:#f87171;font-weight:600;margin-bottom:4px">Tindakan ini tidak dapat dibatalkan</p>
        <p style="font-size:13px;color:var(--muted)">Stok semua produk dalam transaksi ini akan dikembalikan secara otomatis.</p>
      </div>
      <div style="padding:12px 14px;background:var(--surface2);border-radius:10px;font-size:13px;color:var(--sub)">
        <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:4px">{{ $transaction->transaction_number }}</div>
        <div>Total: <strong style="color:var(--text)">Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong></div>
        <div>{{ $transaction->items->count() }} item · {{ $transaction->date->translatedFormat('d F Y') }}</div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
      <button onclick="document.getElementById('modal-void').classList.remove('open')"
        style="flex:0 0 auto;padding:10px 18px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <form method="POST" action="{{ $outlet->route('transactions.void', [$transaction]) }}" style="flex:1">
        @csrf
        @method('PATCH')
        <button type="submit"
          style="width:100%;padding:10px;border-radius:12px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
          <i class="fa-solid fa-ban" style="margin-right:6px;font-size:11px"></i>Ya, Void Transaksi Ini
        </button>
      </form>
    </div>
  </div>
</div>
@endif


@if(session('success'))
@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>showToast('success','{{ session('success') }}'));</script>
@endpush
@endif

</x-outlet-layout>
