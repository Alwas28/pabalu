<x-outlet-layout :outlet="$outlet" pageTitle="Detail Order">

@php
  $statuses = \App\Models\Order::$statuses;
  $st       = $statuses[$order->status];
  $allServed = $order->allServed();
  $canPay   = in_array($order->status, ['open','preparing','served']);
  $canChange = in_array($order->status, ['open','preparing','served']);
  $payLocked = $outlet->isKitchenMode() && $order->status !== 'served';
  $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

<style>
.item-row { display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border); transition:background .12s; }
.item-row:last-child { border-bottom:none; }
.serve-btn { width:30px; height:30px; border-radius:8px; border:2px solid var(--border); background:transparent; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; flex-shrink:0; }
.serve-btn.served { background:#34d399; border-color:#34d399; }

.merge-modal { position:fixed; inset:0; background:rgba(0,0,0,.78); z-index:400; display:none; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(4px); }
.merge-modal.open { display:flex; }
.merge-box { background:var(--surface); border-radius:18px; width:100%; max-width:420px; max-height:85vh; display:flex; flex-direction:column; overflow:hidden; }
.merge-head { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid var(--border); flex-shrink:0; }
.merge-head span { font-size:14px; font-weight:700; color:var(--text); }
.merge-head button { background:none; border:none; font-size:20px; color:var(--muted); cursor:pointer; line-height:1; }
.merge-search { padding:12px 18px; border-bottom:1px solid var(--border); flex-shrink:0; }
.merge-search input { width:100%; padding:9px 12px 9px 34px; border-radius:10px; border:1px solid var(--border); background:var(--surface2); color:var(--text); font-size:13px; outline:none; }
.merge-search-wrap { position:relative; }
.merge-search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:12px; }
.merge-list { overflow-y:auto; flex:1; padding:4px 8px; }
.merge-row { display:flex; align-items:center; gap:10px; padding:10px 10px; font-size:12.5px; cursor:pointer; border-radius:10px; }
.merge-row:hover { background:var(--surface2); }
.merge-empty { padding:24px; text-align:center; color:var(--muted); font-size:12.5px; }
.merge-foot { padding:12px 18px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:10px; flex-shrink:0; }

/* Responsive */
@media(max-width:640px){
  .detail-grid{grid-template-columns:1fr!important}
}
</style>

<div style="max-width:700px;margin:0 auto;display:flex;flex-direction:column;gap:20px">

  {{-- Back + header --}}
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <a href="{{ $outlet->route('orders.index') }}"
      style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none"
      onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
      <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Antrian
    </a>
    <div style="flex:1">
      <h1 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">
        {{ $order->customer_name ?: ($order->table_number ? 'Meja '.$order->table_number : 'Walk-in') }}
      </h1>
      <p style="font-size:12px;color:var(--muted);font-family:monospace">{{ $order->order_number }}</p>
    </div>
    {{-- Status badge --}}
    <div style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:12px;background:{{ $st['bg'] }};border:1px solid {{ $st['color'] }}33">
      <i class="fa-solid {{ $st['icon'] }}" style="color:{{ $st['color'] }};font-size:12px"></i>
      <span style="font-weight:700;color:{{ $st['color'] }};font-size:13px">{{ $st['label'] }}</span>
    </div>
  </div>

  @if(session('success'))
  <div style="padding:12px 16px;border-radius:12px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);color:#34d399;font-size:13.5px;font-weight:600">
    <i class="fa-solid fa-circle-check" style="margin-right:8px"></i>{{ session('success') }}
  </div>
  @endif
  @if(session('error'))
  <div style="padding:12px 16px;border-radius:12px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;font-size:13.5px;font-weight:600">
    <i class="fa-solid fa-circle-xmark" style="margin-right:8px"></i>{{ session('error') }}
  </div>
  @endif

  <div class="detail-grid" style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">

    {{-- Left: item checklist --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden">
      <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <span class="font-display" style="font-size:14px;font-weight:700;color:var(--text)">Item Pesanan</span>
        <span style="font-size:12px;color:var(--muted)" id="item-progress">
          {{ $order->items->where('is_served', true)->count() }}/{{ $order->items->count() }} diantarkan
        </span>
      </div>

      @foreach($order->items as $item)
      <div class="item-row" id="item-row-{{ $item->id }}" style="{{ $item->is_served ? 'background:rgba(52,211,153,.04)' : '' }}">
        @if($canPay)
        <button class="serve-btn {{ $item->is_served ? 'served' : '' }}"
          onclick="toggleServe({{ $item->id }}, this)"
          title="{{ $item->is_served ? 'Tandai belum diantarkan' : 'Tandai sudah diantarkan' }}">
          @if($item->is_served)
          <i class="fa-solid fa-check" style="color:#fff;font-size:11px"></i>
          @else
          <i class="fa-solid fa-check" style="color:var(--border);font-size:11px"></i>
          @endif
        </button>
        @else
        <div style="width:30px;height:30px;border-radius:8px;background:{{ $item->is_served ? 'rgba(52,211,153,.15)' : 'var(--surface2)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fa-solid fa-check" style="color:{{ $item->is_served ? '#34d399' : 'var(--border)' }};font-size:11px"></i>
        </div>
        @endif
        <div style="flex:1;min-width:0">
          <div id="item-name-{{ $item->id }}" style="font-size:13.5px;font-weight:600;color:var(--text);{{ $item->is_served ? 'text-decoration:line-through;color:var(--muted)' : '' }}">
            {{ $item->product_name }}
          </div>
          <div style="font-size:12px;color:var(--muted)">{{ $fmt($item->product_price) }} × {{ $item->qty }}</div>
        </div>
        <div style="font-size:13px;font-weight:700;color:var(--text);flex-shrink:0">{{ $fmt($item->subtotal) }}</div>
      </div>
      @endforeach

      {{-- Item dari order lain yang digabung (diisi via JS) --}}
      <div id="merged-items-injection"></div>

      {{-- Notes --}}
      @if($order->notes)
      <div style="padding:12px 16px;border-top:1px solid var(--border);background:var(--surface2)">
        <span style="font-size:11.5px;color:var(--muted)"><i class="fa-solid fa-note-sticky" style="margin-right:5px"></i>{{ $order->notes }}</span>
      </div>
      @endif
    </div>

    {{-- Right: info + actions --}}
    <div style="display:flex;flex-direction:column;gap:12px">

      {{-- Ringkasan --}}
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px">
        <div class="font-display" style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:12px">Ringkasan</div>
        <div style="display:flex;flex-direction:column;gap:7px;font-size:13px">
          <div id="merged-orders-tag" style="display:none;font-size:11px;color:var(--ac);font-weight:700;margin-bottom:2px">
            <i class="fa-solid fa-link" style="margin-right:4px"></i><span id="merged-orders-count">0</span> order digabung
          </div>
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--muted)">Subtotal</span>
            <span style="color:var(--text)" id="summary-subtotal">{{ $fmt($order->subtotal) }}</span>
          </div>
          <div id="summary-discount-row" style="display:{{ $order->discount_amount > 0 ? 'flex' : 'none' }};justify-content:space-between">
            <span style="color:#34d399" id="summary-discount-label">
              Diskon{{ $order->discount_percent > 0 ? " ({$order->discount_percent}%)" : '' }}
            </span>
            <span style="color:#34d399" id="summary-discount-value">−{{ $fmt($order->discount_amount) }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;color:var(--text);border-top:1px solid var(--border);padding-top:8px;margin-top:2px">
            <span class="font-display">TOTAL</span>
            <span id="summary-total">{{ $fmt($order->total) }}</span>
          </div>
        </div>
      </div>

      {{-- Info --}}
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px">
        <div class="font-display" style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:10px">Info Order</div>
        <div style="display:flex;flex-direction:column;gap:7px;font-size:12.5px">
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--muted)">Dibuat</span>
            <span style="color:var(--text)">{{ $order->created_at->format('H:i') }}</span>
          </div>
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--muted)">Kasir</span>
            <span style="color:var(--text)">{{ $order->user?->name ?? '—' }}</span>
          </div>
          @if($order->customer_name)
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--muted)">Pemesan</span>
            <span style="color:var(--text);font-weight:600">{{ $order->customer_name }}</span>
          </div>
          @endif
          @if($order->table_number)
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--muted)">No. Meja</span>
            <span style="color:var(--text)">{{ $order->table_number }}</span>
          </div>
          @endif
        </div>
      </div>

      {{-- Cetak struk dapur --}}
      <a href="{{ $outlet->route('orders.kitchen', [$order]) }}" target="_blank"
        style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none"
        onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
        <i class="fa-solid fa-print" style="font-size:11px"></i> Cetak Struk Dapur
      </a>

      {{-- Cetak struk pesanan (kasir, dengan QR) --}}
      <a href="{{ $outlet->route('orders.bill', [$order]) }}" target="_blank"
        style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none"
        onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
        <i class="fa-solid fa-receipt" style="font-size:11px"></i> Cetak Struk Pesanan
      </a>

      @if($canChange)
      {{-- Update status --}}
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:14px">
        <div class="font-display" style="font-size:12.5px;font-weight:700;color:var(--muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px">Ubah Status</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          @if($order->status === 'open')
          <form method="POST" action="{{ $outlet->route('orders.status', [$order]) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="preparing">
            <button type="submit" style="width:100%;padding:9px;border-radius:10px;border:none;background:rgba(96,165,250,.15);color:#60a5fa;font-size:13px;font-weight:700;cursor:pointer">
              <i class="fa-solid fa-fire-burner" style="margin-right:6px;font-size:11px"></i>Tandai Diproses
            </button>
          </form>
          @endif
          @if(in_array($order->status, ['open','preparing']))
          <form method="POST" action="{{ $outlet->route('orders.status', [$order]) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="served">
            <button type="submit" style="width:100%;padding:9px;border-radius:10px;border:none;background:rgba(52,211,153,.15);color:#34d399;font-size:13px;font-weight:700;cursor:pointer">
              <i class="fa-solid fa-bell-concierge" style="margin-right:6px;font-size:11px"></i>Tandai Diantarkan
            </button>
          </form>
          @endif
          @if($order->status === 'open')
          <form method="POST" action="{{ $outlet->route('orders.status', [$order]) }}"
            onsubmit="return confirm('Batalkan order ini? Stok akan dikembalikan.')">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" style="width:100%;padding:9px;border-radius:10px;border:none;background:rgba(239,68,68,.1);color:#f87171;font-size:13px;font-weight:700;cursor:pointer">
              <i class="fa-solid fa-ban" style="margin-right:6px;font-size:11px"></i>Batalkan Order
            </button>
          </form>
          @else
          <p style="font-size:11.5px;color:var(--muted);text-align:center;padding:4px 0">
            <i class="fa-solid fa-circle-info" style="margin-right:4px"></i>Order yang sedang diproses/diantarkan tidak bisa dibatalkan
          </p>
          @endif
        </div>
      </div>

      {{-- Bayar --}}
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:14px">
        <div class="font-display" style="font-size:12.5px;font-weight:700;color:var(--muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px">Pembayaran</div>
        @if($payLocked)
        <div style="padding:14px;border-radius:10px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);text-align:center">
          <i class="fa-solid fa-lock" style="font-size:18px;color:#f59e0b;margin-bottom:6px;display:block"></i>
          <p style="font-size:12.5px;color:#b45309;font-weight:600">Pembayaran terkunci. Semua item harus diantarkan terlebih dahulu.</p>
        </div>
        @else
        <form method="POST" action="{{ $outlet->route('orders.pay', [$order]) }}" id="pay-form" onsubmit="doPay(event)" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="payment_method" id="hidden-method" value="cash">
          <input type="hidden" name="payment_amount" id="hidden-amount" value="{{ $order->total }}">

          @if($mergeable->isNotEmpty())
          {{-- Gabungkan struk order lain --}}
          <div style="margin-bottom:12px">
            <button type="button" onclick="openMergeModal()" id="merge-trigger-btn"
              style="width:100%;display:flex;align-items:center;gap:7px;padding:9px 10px;border-radius:10px;border:1px dashed var(--border);background:var(--surface2);cursor:pointer;font-size:12px;font-weight:600;color:var(--sub)">
              <i class="fa-solid fa-link" style="font-size:11px"></i>
              <span style="flex:1;text-align:left">Gabungkan struk order lain (opsional)</span>
              <span id="merge-count-badge" style="display:none;background:var(--ac);color:#fff;border-radius:99px;font-size:10.5px;font-weight:700;padding:1px 7px;min-width:18px;text-align:center"></span>
            </button>
          </div>

          {{-- Modal pilih order untuk digabung --}}
          <div id="merge-modal" class="merge-modal">
            <div class="merge-box">
              <div class="merge-head">
                <span><i class="fa-solid fa-link" style="margin-right:7px;color:var(--ac)"></i>Gabungkan Struk Order</span>
                <button type="button" onclick="closeMergeModal()">&times;</button>
              </div>
              <div class="merge-search">
                <div class="merge-search-wrap">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input type="text" id="merge-search-input" placeholder="Cari no. order, nama, atau meja..." oninput="filterMergeList(this.value)" autocomplete="off">
                </div>
              </div>
              <div class="merge-list" id="merge-list">
                @foreach($mergeable as $m)
                @php
                  $label = $m->customer_name ?: ($m->table_number ? 'Meja '.$m->table_number : 'Walk-in');
                  $searchKey = mb_strtolower($m->order_number.' '.$label);
                @endphp
                <label class="merge-row" data-search="{{ $searchKey }}">
                  <input type="checkbox" name="merge_order_ids[]" class="merge-chk" value="{{ $m->id }}" data-total="{{ $m->total }}" onchange="recalcTotal()">
                  <span style="flex:1;color:var(--text)">
                    <span style="font-family:monospace;color:var(--muted);font-size:11px">{{ $m->order_number }}</span><br>
                    {{ $label }}
                  </span>
                  <span style="font-weight:700;color:var(--text)">{{ $fmt($m->total) }}</span>
                </label>
                @endforeach
                <div class="merge-empty" id="merge-no-result" style="display:none">Tidak ada order yang cocok.</div>
              </div>
              <div class="merge-foot">
                <span style="font-size:12px;color:var(--muted)"><span id="merge-selected-count">0</span> order dipilih</span>
                <button type="button" onclick="closeMergeModal()"
                  style="padding:8px 18px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:12.5px;font-weight:700;cursor:pointer">
                  Selesai
                </button>
              </div>
            </div>
          </div>

          {{-- Data order yang bisa digabung, dipakai JS untuk update Item Pesanan & Ringkasan --}}
          <script type="application/json" id="merge-orders-data">
            {!! $mergeable->mapWithKeys(function ($m) {
                return [$m->id => [
                    'order_number'     => $m->order_number,
                    'label'            => $m->customer_name ?: ($m->table_number ? 'Meja '.$m->table_number : 'Walk-in'),
                    'subtotal'         => $m->subtotal,
                    'discount_amount'  => $m->discount_amount,
                    'discount_percent' => $m->discount_percent,
                    'total'            => $m->total,
                    'items'            => $m->items->map(fn ($i) => [
                        'product_name'  => $i->product_name,
                        'qty'           => $i->qty,
                        'product_price' => $i->product_price,
                        'subtotal'      => $i->subtotal,
                    ])->values(),
                ]];
            })->toJson() !!}
          </script>
          @endif

          {{-- Pilih metode --}}
          <div style="margin-bottom:12px">
            <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Metode Pembayaran</div>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px">
              @foreach(array_merge(['cash'=>['Tunai','fa-money-bill-wave']], $paymentMethods) as $m=>[$ml,$mi])
              <button type="button" class="pm-btn" data-method="{{ $m }}"
                onclick="selectPm('{{ $m }}')"
                style="display:flex;align-items:center;gap:7px;padding:8px 10px;border-radius:8px;border:2px solid var(--border);background:var(--surface2);cursor:pointer;font-size:12px;font-weight:600;color:var(--sub);transition:all .12s">
                <i class="fa-solid {{ $mi }}" style="font-size:13px"></i>{{ $ml }}
              </button>
              @endforeach
            </div>
          </div>

          {{-- Tunai: input + quick amounts + kembalian --}}
          <div id="cash-section" style="margin-bottom:12px">
            <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Uang Diterima</div>
            <div style="position:relative">
              <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--muted);font-weight:600">Rp</span>
              <input type="text" inputmode="numeric" id="cash-input" placeholder="0"
                style="width:100%;padding:10px 10px 10px 34px;border-radius:10px;border:2px solid var(--ac);background:var(--surface2);color:var(--text);font-size:17px;font-weight:700;outline:none;font-family:inherit"
                oninput="syncCash(this)" onblur="formatCash(this)">
            </div>
            {{-- Quick amounts --}}
            <div id="quick-amts" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px"></div>
            {{-- Kembalian --}}
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding:8px 12px;border-radius:10px;background:var(--surface2)">
              <span style="font-size:13px;color:var(--sub);font-weight:600">Kembalian</span>
              <span id="change-out" style="font-size:16px;font-weight:800;color:#34d399">Rp 0</span>
            </div>
          </div>

          {{-- Non-tunai manual (QRIS Transfer / Transfer / Kartu): nominal + referensi + upload bukti --}}
          <div id="noncash-section" style="display:none;margin-bottom:12px">
            <div style="padding:12px;border-radius:10px;background:var(--surface2)">
              <p style="font-size:13px;color:var(--sub);text-align:center">Konfirmasi pembayaran <span id="pm-method-label" style="font-weight:700;color:var(--text)"></span></p>
              <p style="font-size:16px;font-weight:800;color:var(--text);margin-top:2px;text-align:center" id="pay-total-display">{{ $fmt($order->total) }}</p>

              <div style="margin-top:12px">
                <label style="font-size:11.5px;color:var(--muted);display:block;margin-bottom:5px">Nomor Referensi</label>
                <input type="text" name="reference_number" id="reference-input" placeholder="No. referensi transfer/EDC"
                  style="width:100%;padding:9px 10px;border-radius:9px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13px;outline:none">
              </div>

              <div style="margin-top:10px">
                <label style="font-size:11.5px;color:var(--muted);display:block;margin-bottom:5px">Foto Bukti Transaksi</label>
                <input type="file" name="proof_image" id="proof-input" accept="image/*" capture="environment"
                  onchange="previewProof(this)" style="display:none">

                <div id="proof-dropzone" onclick="document.getElementById('proof-input').click()"
                  style="border:1.5px dashed var(--border);border-radius:10px;padding:18px 12px;text-align:center;cursor:pointer;transition:all .15s;background:var(--surface)"
                  onmouseover="this.style.borderColor='var(--ac)';this.style.background='var(--ac-lt)'"
                  onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--surface)'">
                  <i class="fa-solid fa-camera" style="font-size:19px;color:var(--muted)"></i>
                  <p style="font-size:12.5px;color:var(--sub);font-weight:600;margin-top:7px">Ketuk untuk ambil/unggah foto</p>
                  <p style="font-size:11px;color:var(--muted);margin-top:2px">JPG/PNG, maks 2MB</p>
                </div>

                <div id="proof-result" style="display:none;margin-top:8px">
                  <div style="position:relative">
                    <img id="proof-preview" style="width:100%;max-height:220px;object-fit:cover;border-radius:10px;border:1px solid var(--border);display:block">
                    <button type="button" onclick="clearProof()"
                      style="position:absolute;top:7px;right:7px;width:26px;height:26px;border-radius:50%;border:none;background:rgba(0,0,0,.6);color:#fff;font-size:11px;cursor:pointer;display:grid;place-items:center">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </div>
                  <p style="display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted);margin-top:6px">
                    <i class="fa-solid fa-circle-check" style="color:#34d399;font-size:11px"></i>
                    <span id="proof-filename" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          {{-- QRIS Pay (Midtrans): generate QR dinamis, polling status --}}
          <div id="qrispay-section" style="display:none;margin-bottom:12px">
            <div id="qrispay-idle" style="padding:12px;border-radius:10px;background:var(--surface2);text-align:center">
              <p style="font-size:13px;color:var(--sub)">Total tagihan QRIS Pay</p>
              <p style="font-size:16px;font-weight:800;color:var(--text);margin-top:2px" id="qrispay-total-display">{{ $fmt($order->total) }}</p>
              <button type="button" onclick="startQrisPay()" id="qrispay-start-btn"
                style="width:100%;margin-top:10px;padding:11px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
                <i class="fa-solid fa-qrcode" style="margin-right:7px;font-size:12px"></i>Tampilkan QRIS
              </button>
            </div>
            <div id="qrispay-waiting" style="display:none;padding:16px;border-radius:10px;background:var(--surface2);text-align:center">
              <img id="qrispay-img" style="width:200px;height:200px;border-radius:10px;background:#fff;padding:8px;margin:0 auto;display:block">
              <p style="font-size:12.5px;color:var(--muted);margin-top:10px">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right:6px"></i>
                Menunggu pelanggan membayar... <span id="qrispay-status-text">memeriksa status</span>
              </p>
              <button type="button" onclick="cancelQrisPay()"
                style="margin-top:10px;padding:8px 16px;border-radius:9px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:12.5px;font-weight:600;cursor:pointer">
                Batal
              </button>
            </div>
          </div>

          <button type="submit" id="confirm-pay-btn"
            style="width:100%;padding:12px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
            <i class="fa-solid fa-check" style="margin-right:8px;font-size:12px"></i>Konfirmasi Bayar
          </button>
        </form>
        @endif
      </div>
      @elseif($order->status === 'paid' && $order->transaction)
      {{-- Paid info --}}
      <div style="background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.25);border-radius:14px;padding:14px;text-align:center">
        <i class="fa-solid fa-circle-check" style="font-size:24px;color:#34d399;margin-bottom:8px;display:block"></i>
        <div style="font-weight:700;color:#34d399;font-size:14px">Sudah Lunas</div>
        <div style="font-size:12px;color:var(--muted);margin-top:4px">{{ $order->paid_at?->format('H:i, d M Y') }}</div>
        <a href="{{ $outlet->route('transactions.receipt', [$order->transaction]) }}?autoprint=1" target="_blank"
          style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:8px 16px;border-radius:10px;border:1px solid rgba(52,211,153,.3);background:transparent;color:#34d399;font-size:12.5px;font-weight:600;text-decoration:none"
          onmouseover="this.style.background='rgba(52,211,153,.1)'" onmouseout="this.style.background='transparent'">
          <i class="fa-solid fa-print" style="font-size:10px"></i> Cetak Struk
        </a>
      </div>
      @endif

    </div>
  </div>

</div>

@push('scripts')
<script>
const serveUrl       = '{{ route("fnb.orders.serve-item", [$outlet, $order, "__ID__"]) }}'.replace('__ID__', '');
const qrisChargeUrl  = '{{ $outlet->route("orders.qris-pay.charge", [$order]) }}';
const qrisStatusUrl  = '{{ $outlet->route("orders.qris-pay.status", [$order]) }}';
const csrfToken = '{{ csrf_token() }}';
const baseTotal           = {{ $order->total }};
const baseSubtotal        = {{ $order->subtotal }};
const baseDiscountAmount  = {{ $order->discount_amount }};
const baseDiscountPercent = {{ $order->discount_percent }};
let total       = baseTotal;
const fmt       = n => 'Rp ' + Number(n).toLocaleString('id-ID');
let currentMethod = 'cash';

const mergeDataEl = document.getElementById('merge-orders-data');
const mergeData   = mergeDataEl ? JSON.parse(mergeDataEl.textContent) : {};

// ── Checklist item ─────────────────────────────────────
async function toggleServe(itemId, btn) {
  btn.disabled = true;
  try {
    const resp = await fetch(serveUrl + itemId, {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrfToken },
    });
    const data = await resp.json();
    if (resp.ok) {
      const row    = document.getElementById('item-row-' + itemId);
      const nameEl = document.getElementById('item-name-' + itemId);
      btn.classList.toggle('served', data.is_served);
      btn.innerHTML = `<i class="fa-solid fa-check" style="color:${data.is_served ? '#fff' : 'var(--border)'};font-size:11px"></i>`;
      row.style.background        = data.is_served ? 'rgba(52,211,153,.04)' : '';
      nameEl.style.textDecoration = data.is_served ? 'line-through' : '';
      nameEl.style.color          = data.is_served ? 'var(--muted)' : 'var(--text)';
    }
  } catch(e) {}
  btn.disabled = false;
}

// ── Gabungkan struk order lain (modal + search) ────────
function openMergeModal() {
  document.getElementById('merge-modal').classList.add('open');
}

function closeMergeModal() {
  document.getElementById('merge-modal').classList.remove('open');
}

function filterMergeList(query) {
  const q = query.trim().toLowerCase();
  const rows = document.querySelectorAll('#merge-list .merge-row');
  let visible = 0;
  rows.forEach(row => {
    const match = row.dataset.search.includes(q);
    row.style.display = match ? 'flex' : 'none';
    if (match) visible++;
  });
  const empty = document.getElementById('merge-no-result');
  if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
}

function recalcTotal() {
  const checked = Array.from(document.querySelectorAll('.merge-chk:checked'));
  const picked  = checked.length;

  let sum            = baseTotal;
  let sumSubtotal     = baseSubtotal;
  let sumDiscount     = baseDiscountAmount;
  const mergedOrders  = [];

  checked.forEach(chk => {
    sum += parseInt(chk.dataset.total);
    const o = mergeData[chk.value];
    if (o) {
      sumSubtotal += Number(o.subtotal);
      sumDiscount += Number(o.discount_amount);
      mergedOrders.push(o);
    }
  });

  total = sum;
  document.getElementById('hidden-amount').value = total;

  const display = document.getElementById('pay-total-display');
  if (display) display.textContent = fmt(total);

  const selCount = document.getElementById('merge-selected-count');
  if (selCount) selCount.textContent = picked;

  const badge = document.getElementById('merge-count-badge');
  if (badge) {
    badge.style.display = picked > 0 ? 'inline-block' : 'none';
    badge.textContent   = picked;
  }

  updateMergedDisplay(mergedOrders, sumSubtotal, sumDiscount);

  document.getElementById('cash-input').value = '';
  calcChange();
  renderQuickAmounts();
}

// ── Sinkronkan tampilan Item Pesanan & Ringkasan dengan order yang digabung ──
function updateMergedDisplay(mergedOrders, sumSubtotal, sumDiscount) {
  const injection = document.getElementById('merged-items-injection');
  if (injection) {
    injection.innerHTML = mergedOrders.map(o => `
      <div class="item-row" style="background:rgba(96,165,250,.05)">
        <div style="width:30px;flex-shrink:0;text-align:center;color:var(--ac);font-size:11px">
          <i class="fa-solid fa-link"></i>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:11px;color:var(--muted);font-family:monospace;margin-bottom:1px">${o.order_number} — ${o.label}</div>
          ${o.items.map(item => `
            <div style="display:flex;justify-content:space-between;gap:8px">
              <span style="font-size:13px;color:var(--text)">${item.product_name} <span style="color:var(--muted)">x${item.qty}</span></span>
              <span style="font-size:13px;font-weight:600;color:var(--text)">${fmt(item.subtotal)}</span>
            </div>
          `).join('')}
        </div>
      </div>
    `).join('');
  }

  const tag      = document.getElementById('merged-orders-tag');
  const tagCount = document.getElementById('merged-orders-count');
  if (tag && tagCount) {
    tagCount.textContent = mergedOrders.length;
    tag.style.display    = mergedOrders.length > 0 ? 'block' : 'none';
  }

  const subtotalEl = document.getElementById('summary-subtotal');
  if (subtotalEl) subtotalEl.textContent = fmt(sumSubtotal);

  const discRow   = document.getElementById('summary-discount-row');
  const discLabel = document.getElementById('summary-discount-label');
  const discValue = document.getElementById('summary-discount-value');
  if (discRow && discValue) {
    discRow.style.display = sumDiscount > 0 ? 'flex' : 'none';
    discValue.textContent = '−' + fmt(sumDiscount);
    if (discLabel) {
      discLabel.textContent = mergedOrders.length > 0
        ? 'Diskon'
        : ('Diskon' + (baseDiscountPercent > 0 ? ` (${baseDiscountPercent}%)` : ''));
    }
  }

  const totalEl = document.getElementById('summary-total');
  if (totalEl) totalEl.textContent = fmt(total);
}

// ── Pilih metode pembayaran ────────────────────────────
function selectPm(method) {
  currentMethod = method;
  document.getElementById('hidden-method').value = method;

  document.querySelectorAll('.pm-btn').forEach(btn => {
    const sel = btn.dataset.method === method;
    btn.style.borderColor = sel ? 'var(--ac)' : 'var(--border)';
    btn.style.background  = sel ? 'var(--ac-lt)' : 'var(--surface2)';
    btn.style.color       = sel ? 'var(--ac)' : 'var(--sub)';
    btn.querySelector('i').style.color = sel ? 'var(--ac)' : 'var(--muted)';
  });

  const cashSec    = document.getElementById('cash-section');
  const noncashSec = document.getElementById('noncash-section');
  const qrispaySec = document.getElementById('qrispay-section');
  const submitBtn  = document.getElementById('confirm-pay-btn');
  const pmLabel    = document.getElementById('pm-method-label');

  cashSec.style.display    = 'none';
  noncashSec.style.display = 'none';
  qrispaySec.style.display = 'none';
  submitBtn.style.display  = 'block';

  if (method !== 'qris_transfer' && method !== 'transfer' && method !== 'card') {
    const refInput   = document.getElementById('reference-input');
    const proofInput = document.getElementById('proof-input');
    if (refInput)   refInput.value   = '';
    if (proofInput) proofInput.value = '';
    resetProofUI();
  }

  if (method === 'cash') {
    cashSec.style.display = 'block';
    document.getElementById('hidden-amount').value = total;
    document.getElementById('cash-input').value    = '';
    calcChange();
    renderQuickAmounts();
  } else if (method === 'qris_pay') {
    qrispaySec.style.display = 'block';
    submitBtn.style.display  = 'none';
    document.getElementById('hidden-amount').value = total;
    document.getElementById('qrispay-total-display').textContent = fmt(total);
    resetQrisPay();
  } else {
    noncashSec.style.display = 'block';
    document.getElementById('hidden-amount').value = total;
    const labels = { qris_transfer: 'QRIS Transfer', transfer: 'Transfer Bank', card: 'Kartu Debit/Kredit' };
    if (pmLabel) pmLabel.textContent = labels[method] || method;
  }
}

// ── Tombol nominal cepat ───────────────────────────────
function renderQuickAmounts() {
  const container = document.getElementById('quick-amts');
  const amounts   = [
    { label: 'Pas', value: total },
    ...[10000, 20000, 50000, 100000, 200000, 500000]
      .filter(a => a > total)
      .slice(0, 4)
      .map(a => ({ label: fmt(a).replace('Rp ', ''), value: a })),
  ];
  container.innerHTML = amounts.map(a =>
    `<button type="button" onclick="setCash(${a.value})"
      style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);font-size:12px;font-weight:600;color:var(--sub);cursor:pointer;transition:all .12s"
      onmouseover="this.style.background='var(--ac-lt)';this.style.color='var(--ac)';this.style.borderColor='var(--ac)'"
      onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)';this.style.borderColor='var(--border)'">
      ${a.label}
    </button>`
  ).join('');
}

function setCash(amount) {
  const input = document.getElementById('cash-input');
  input.value = Number(amount).toLocaleString('id-ID');
  document.getElementById('hidden-amount').value = amount;
  calcChange();
}

function syncCash(input) {
  const raw = input.value.replace(/\D/g, '');
  document.getElementById('hidden-amount').value = raw || 0;
  calcChange();
}

function formatCash(input) {
  const raw = input.value.replace(/\D/g, '');
  if (raw) input.value = Number(raw).toLocaleString('id-ID');
}

function calcChange() {
  const paid   = parseInt(document.getElementById('hidden-amount').value) || 0;
  const change = Math.max(0, paid - total);
  const el     = document.getElementById('change-out');
  if (!el) return;
  el.textContent = fmt(change);
  el.style.color = paid >= total ? '#34d399' : '#f87171';
}

// ── Validasi sebelum submit ────────────────────────────
function validatePay() {
  if (currentMethod === 'cash') {
    const paid = parseInt(document.getElementById('hidden-amount').value) || 0;
    if (paid < total) {
      document.getElementById('cash-input').style.borderColor = '#f87171';
      document.getElementById('cash-input').focus();
      return false;
    }
    document.getElementById('cash-input').style.borderColor = 'var(--ac)';
  } else if (currentMethod !== 'qris_pay') {
    const ref   = document.getElementById('reference-input');
    const proof = document.getElementById('proof-input');
    if (!ref.value.trim()) {
      ref.style.borderColor = '#f87171';
      ref.focus();
      return false;
    }
    ref.style.borderColor = 'var(--border)';
    if (!proof.files || proof.files.length === 0) {
      alert('Unggah foto bukti transaksi terlebih dahulu.');
      return false;
    }
  }
  return true;
}

// ── Preview foto bukti transaksi ───────────────────────
function resetProofUI() {
  const dropzone = document.getElementById('proof-dropzone');
  const result   = document.getElementById('proof-result');
  if (dropzone) dropzone.style.display = 'block';
  if (result)   result.style.display   = 'none';
}

function previewProof(input) {
  if (!input.files || !input.files[0]) {
    resetProofUI();
    return;
  }
  const file     = input.files[0];
  const dropzone = document.getElementById('proof-dropzone');
  const result   = document.getElementById('proof-result');
  const preview  = document.getElementById('proof-preview');
  const filename = document.getElementById('proof-filename');

  preview.src           = URL.createObjectURL(file);
  filename.textContent  = file.name;
  dropzone.style.display = 'none';
  result.style.display   = 'block';
}

function clearProof() {
  const input = document.getElementById('proof-input');
  input.value = '';
  resetProofUI();
}

// ── QRIS Pay: charge + polling status ──────────────────
let qrisPollTimer = null;

function resetQrisPay() {
  clearInterval(qrisPollTimer);
  qrisPollTimer = null;
  document.getElementById('qrispay-idle').style.display    = 'block';
  document.getElementById('qrispay-waiting').style.display = 'none';
  document.getElementById('qrispay-start-btn').disabled    = false;
}

function currentMergeIds() {
  return Array.from(document.querySelectorAll('.merge-chk:checked')).map(chk => chk.value);
}

async function startQrisPay() {
  const btn = document.getElementById('qrispay-start-btn');
  btn.disabled  = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  try {
    const res  = await fetch(qrisChargeUrl, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ merge_order_ids: currentMergeIds() }),
    });
    const data = await res.json();

    if (!res.ok) {
      alert(data.message || 'Gagal membuat QRIS.');
      resetQrisPay();
      return;
    }

    document.getElementById('qrispay-img').src      = data.qr_url;
    document.getElementById('qrispay-idle').style.display    = 'none';
    document.getElementById('qrispay-waiting').style.display = 'block';

    qrisPollTimer = setInterval(() => pollQrisStatus(data.midtrans_order_id), 4000);
  } catch (err) {
    alert('Terjadi kesalahan jaringan. Coba lagi.');
    resetQrisPay();
  }
}

async function pollQrisStatus(midtransOrderId) {
  try {
    const params = new URLSearchParams({ midtrans_order_id: midtransOrderId });
    currentMergeIds().forEach(id => params.append('merge_order_ids[]', id));

    const res  = await fetch(qrisStatusUrl + '?' + params.toString(), {
      headers: { 'Accept': 'application/json' },
    });
    const data = await res.json();

    if (data.status === 'paid' && data.receipt_url) {
      clearInterval(qrisPollTimer);
      window.open(data.receipt_url + '?autoprint=1', '_blank');
      location.reload();
      return;
    }

    const statusText = document.getElementById('qrispay-status-text');
    if (statusText) {
      const labels = { pending: 'menunggu pembayaran', expire: 'QRIS sudah kedaluwarsa', deny: 'pembayaran ditolak', cancel: 'pembayaran dibatalkan' };
      statusText.textContent = labels[data.status] || 'memeriksa status';
    }
  } catch (err) {
    // diamkan, akan dicoba lagi di polling berikutnya
  }
}

function cancelQrisPay() {
  resetQrisPay();
}

// ── Proses bayar via AJAX, struk dibuka di tab baru ────
async function doPay(e) {
  e.preventDefault();
  if (!validatePay()) return false;

  const form = document.getElementById('pay-form');
  const btn  = document.getElementById('confirm-pay-btn');
  const originalBtnHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;font-size:12px"></i>Memproses...';

  try {
    const res  = await fetch(form.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: new FormData(form),
    });
    const data = await res.json();

    if (!res.ok) {
      alert(data.message || 'Pembayaran gagal.');
      btn.disabled  = false;
      btn.innerHTML = originalBtnHtml;
      return false;
    }

    window.open(data.receipt_url + '?autoprint=1', '_blank');
    location.reload();
  } catch (err) {
    alert('Terjadi kesalahan jaringan. Coba lagi.');
    btn.disabled  = false;
    btn.innerHTML = originalBtnHtml;
  }
  return false;
}

// ── Init ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  selectPm('cash');
});
</script>
@endpush

</x-outlet-layout>
