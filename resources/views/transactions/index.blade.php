@php use Illuminate\Support\Facades\Storage; @endphp
<x-app-layout title="Riwayat Transaksi">

<style>
.btn-act {
  width:32px;height:32px;border-radius:8px;
  border:1px solid var(--border);background:var(--surface2);
  color:var(--sub);font-size:13px;cursor:pointer;
  display:inline-grid;place-items:center;
  text-decoration:none;transition:all .15s;padding:0;font-family:inherit;
}
.btn-act:hover                { border-color:var(--ac);color:var(--ac); }
.btn-act-danger:hover         { border-color:#f87171 !important;color:#f87171 !important; }
.btn-act.active               { background:var(--ac-lt);border-color:var(--ac);color:var(--ac); }

/* Pagination */
.pg-wrap { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 20px;border-top:1px solid var(--border) }
.pg-info  { font-size:12.5px;color:var(--muted) }
.pg-btns  { display:flex;gap:4px;align-items:center }
.pg-btn   {
  min-width:34px;height:34px;padding:0 10px;border-radius:8px;
  border:1px solid var(--border);background:var(--surface2);
  color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;
  text-decoration:none;font-family:inherit;transition:all .15s;
}
.pg-btn:hover:not(.pg-active):not(.pg-disabled) { border-color:var(--ac);color:var(--ac);background:var(--ac-lt); }
.pg-active   { background:var(--ac);border-color:var(--ac);color:#fff;cursor:default; }
.pg-disabled { opacity:.35;cursor:not-allowed;pointer-events:none; }
.pg-ellipsis { color:var(--muted);padding:0 4px;font-size:13px; }

/* Detail row */
.detail-row td { padding:0 !important;border-bottom:1px solid var(--border) !important; }
.detail-inner  { padding:14px 20px 16px;background:var(--surface2) }
</style>

  {{-- Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div style="font-size:13px;color:var(--sub)">Daftar seluruh transaksi penjualan.</div>
    @can('transaction.create')
    <a href="{{ route('transactions.pos') }}" class="btn btn-primary" style="font-size:13px;padding:8px 16px">
      <i class="fa-solid fa-cash-register"></i> Buka Kasir
    </a>
    @endcan
  </div>

  {{-- Stats --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px">
    <div class="card" style="padding:16px 20px">
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;margin-bottom:4px">Total Transaksi</div>
      <div style="font-family:'Clash Display',sans-serif;font-size:26px;font-weight:700;color:var(--text)">
        {{ number_format($stats['total_transaksi']) }}
      </div>
    </div>
    <div class="card" style="padding:16px 20px">
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;margin-bottom:4px">Total Omzet</div>
      <div style="font-family:'Clash Display',sans-serif;font-size:26px;font-weight:700;color:var(--ac)">
        Rp {{ number_format($stats['total_omzet'], 0, ',', '.') }}
      </div>
    </div>
  </div>

  {{-- Filter --}}
  <form method="GET" action="{{ route('transactions.index') }}" id="filter-form">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end">
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Outlet</label>
        @if ($assignedOutletId ?? false)
          @php $assignedOutlet = $outlets->firstWhere('id', $assignedOutletId); @endphp
          <div class="f-input" style="display:flex;align-items:center;gap:8px;color:var(--ac);font-weight:600;pointer-events:none">
            <i class="fa-solid fa-store"></i>{{ $assignedOutlet?->nama ?? 'Outlet #'.$assignedOutletId }}
            <i class="fa-solid fa-lock" style="font-size:10px;opacity:.7;margin-left:auto"></i>
          </div>
          <input type="hidden" name="outlet_id" value="{{ $assignedOutletId }}">
        @else
          <select name="outlet_id" class="f-input">
            <option value="">— Semua Outlet —</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        @endif
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Tanggal</label>
        <input type="date" name="tanggal" class="f-input" value="{{ request('tanggal', today()->toDateString()) }}">
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Metode</label>
        <select name="metode_bayar" class="f-input">
          <option value="">— Semua —</option>
          <option value="tunai"    @selected(request('metode_bayar')==='tunai')>Tunai</option>
          <option value="qris"     @selected(request('metode_bayar')==='qris')>QRIS</option>
          <option value="transfer" @selected(request('metode_bayar')==='transfer')>Transfer</option>
        </select>
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Status</label>
        <select name="status" class="f-input">
          <option value="">— Semua —</option>
          <option value="paid" @selected(request('status')==='paid')>Paid</option>
          <option value="void" @selected(request('status')==='void')>Void</option>
        </select>
      </div>
      <div style="display:flex;gap:8px;align-self:end">
        <button type="submit" class="btn btn-primary" style="padding:9px 16px;flex:1;justify-content:center">
          <i class="fa-solid fa-magnifying-glass"></i> Filter
        </button>
        <a href="{{ route('transactions.index') }}" class="btn" style="padding:9px 13px" title="Reset filter">
          <i class="fa-solid fa-rotate"></i>
        </a>
      </div>
    </div>
  </form>

  {{-- Table --}}
  <div class="card animate-fadeUp" style="overflow:hidden">
    @if($transactions->isEmpty())
    <div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
      <i class="fa-solid fa-receipt" style="font-size:40px;display:block;margin-bottom:14px;opacity:.3"></i>
      Belum ada transaksi untuk filter ini.
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>No. Transaksi</th>
            <th>Outlet</th>
            <th>Kasir</th>
            <th style="text-align:center;width:60px">Item</th>
            <th style="text-align:right">Total</th>
            <th style="text-align:center">Metode</th>
            <th style="text-align:right">Kembalian</th>
            <th style="text-align:center;width:80px">Status</th>
            <th style="text-align:center;width:110px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($transactions as $trx)
          @php
            $metodeLabel = ['tunai'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer'];
            $metodeIcon  = ['tunai'=>'money-bill-wave','qris'=>'qrcode','transfer'=>'building-columns'];
            $m = $trx->metode_bayar ?? 'tunai';
          @endphp

          {{-- Main row --}}
          <tr style="{{ $trx->status === 'void' ? 'opacity:.5' : '' }}">
            <td class="td-main">
              <div style="font-weight:700;font-size:13px;font-family:monospace">{{ $trx->nomor_transaksi }}</div>
              <div style="font-size:11px;color:var(--muted)">
                {{ \Carbon\Carbon::parse($trx->tanggal)->translatedFormat('d M Y') }}
                · {{ $trx->created_at->format('H:i') }}
              </div>
            </td>
            <td>{{ $trx->outlet->nama ?? '—' }}</td>
            <td>{{ $trx->kasir->name ?? '—' }}</td>
            <td style="text-align:center;font-weight:600">{{ $trx->items_count }}</td>
            <td style="text-align:right">
              <span style="font-weight:700;color:var(--text)">Rp {{ number_format($trx->total, 0, ',', '.') }}</span>
            </td>
            <td style="text-align:center">
              <div style="display:inline-flex;flex-direction:column;align-items:center;gap:2px">
                <span style="font-size:12px;font-weight:600;color:var(--sub);display:inline-flex;align-items:center;gap:4px">
                  <i class="fa-solid fa-{{ $metodeIcon[$m] }}" style="color:var(--ac)"></i>
                  {{ $metodeLabel[$m] }}
                </span>
                @if($trx->bukti_bayar)
                <a href="{{ Storage::url($trx->bukti_bayar) }}" target="_blank"
                  style="font-size:11px;color:var(--ac);text-decoration:none">
                  <i class="fa-solid fa-image"></i> Bukti
                </a>
                @endif
              </div>
            </td>
            <td style="text-align:right">
              @if($m === 'tunai')
                <span style="color:var(--sub)">Rp {{ number_format($trx->kembalian, 0, ',', '.') }}</span>
              @else
                <span style="color:#34d399;font-weight:600;font-size:12px">
                  <i class="fa-solid fa-circle-check"></i> Lunas
                </span>
              @endif
            </td>
            <td style="text-align:center">
              @if($trx->status === 'paid')
              <span class="badge badge-green">Paid</span>
              @else
              <span class="badge badge-red">Void</span>
              @endif
            </td>
            <td style="text-align:center">
              <div style="display:flex;gap:6px;justify-content:center">
                {{-- Detail toggle --}}
                <button type="button" class="btn-act" id="btn-detail-{{ $trx->id }}"
                  onclick="toggleDetail({{ $trx->id }})" title="Lihat Item">
                  <i class="fa-solid fa-list-ul"></i>
                </button>
                {{-- Print --}}
                <a href="{{ route('transactions.show', $trx) }}" target="_blank"
                  class="btn-act" title="Cetak Struk">
                  <i class="fa-solid fa-print"></i>
                </a>
                {{-- Void --}}
                @can('transaction.void')
                @if($trx->status === 'paid')
                <button type="button" class="btn-act btn-act-danger"
                  onclick="openVoidModal('{{ $trx->nomor_transaksi }}', {{ $trx->id }}, 'Rp {{ number_format($trx->total, 0, ',', '.') }}')"
                  title="Void Transaksi">
                  <i class="fa-solid fa-ban"></i>
                </button>
                @endif
                @endcan
              </div>
            </td>
          </tr>

          {{-- Detail row (inline) --}}
          <tr class="detail-row" id="detail-{{ $trx->id }}" style="display:none">
            <td colspan="9">
              <div class="detail-inner">
                @if($trx->keterangan)
                <div style="font-size:12.5px;color:var(--sub);margin-bottom:10px">
                  <i class="fa-solid fa-note-sticky" style="color:var(--ac);margin-right:6px"></i>
                  <em>{{ $trx->keterangan }}</em>
                </div>
                @endif
                <table style="width:100%;max-width:540px;border-collapse:collapse;font-size:12.5px">
                  <thead>
                    <tr>
                      <th style="padding:6px 10px;text-align:left;color:var(--muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Produk</th>
                      <th style="padding:6px 10px;text-align:right;color:var(--muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Harga</th>
                      <th style="padding:6px 10px;text-align:center;color:var(--muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Qty</th>
                      <th style="padding:6px 10px;text-align:right;color:var(--muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($trx->items as $item)
                    <tr style="border-bottom:1px dotted var(--border)">
                      <td style="padding:7px 10px;color:var(--text)">{{ $item->nama_produk }}</td>
                      <td style="padding:7px 10px;text-align:right;color:var(--sub)">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                      <td style="padding:7px 10px;text-align:center;font-weight:700;color:var(--text)">{{ $item->qty }}</td>
                      <td style="padding:7px 10px;text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </td>
          </tr>

          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="pg-wrap">
      <div class="pg-info">
        Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}
        dari {{ number_format($transactions->total()) }} transaksi
      </div>
      <div class="pg-btns">
        {{-- Prev --}}
        @if($transactions->onFirstPage())
        <span class="pg-btn pg-disabled"><i class="fa-solid fa-chevron-left" style="font-size:11px"></i></span>
        @else
        <a href="{{ $transactions->previousPageUrl() }}" class="pg-btn">
          <i class="fa-solid fa-chevron-left" style="font-size:11px"></i>
        </a>
        @endif

        {{-- Pages --}}
        @foreach($transactions->getUrlRange(max(1, $transactions->currentPage()-2), min($transactions->lastPage(), $transactions->currentPage()+2)) as $page => $url)
          @if($page == $transactions->currentPage())
          <span class="pg-btn pg-active">{{ $page }}</span>
          @else
          <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
          @endif
        @endforeach

        {{-- Next --}}
        @if($transactions->hasMorePages())
        <a href="{{ $transactions->nextPageUrl() }}" class="pg-btn">
          <i class="fa-solid fa-chevron-right" style="font-size:11px"></i>
        </a>
        @else
        <span class="pg-btn pg-disabled"><i class="fa-solid fa-chevron-right" style="font-size:11px"></i></span>
        @endif
      </div>
    </div>
    @endif

    @endif
  </div>

  {{-- Void forms --}}
  @foreach($transactions as $trx)
  @can('transaction.void')
  @if($trx->status === 'paid')
  <form id="void-form-{{ $trx->id }}" method="POST"
    action="{{ route('transactions.void', $trx) }}" style="display:none">
    @csrf
  </form>
  @endif
  @endcan
  @endforeach

  {{-- ═══ VOID MODAL ═══ --}}
  <div id="void-backdrop"
    style="display:none;position:fixed;inset:0;z-index:9100;
           background:rgba(0,0,0,.7);backdrop-filter:blur(6px);
           align-items:center;justify-content:center;padding:20px;
           opacity:0;transition:opacity .2s">
    <div id="void-box"
      style="background:var(--surface);border:1px solid var(--border);border-radius:22px;
             width:100%;max-width:400px;box-shadow:0 24px 72px rgba(0,0,0,.55);
             transform:scale(.93) translateY(14px);transition:transform .25s,opacity .25s;opacity:0;overflow:hidden">

      <div style="height:5px;background:linear-gradient(90deg,#ef4444,#f87171)"></div>

      <div style="padding:22px 24px 0;display:flex;gap:14px;align-items:flex-start">
        <div style="width:48px;height:48px;border-radius:14px;background:rgba(239,68,68,.12);
                    flex-shrink:0;display:grid;place-items:center">
          <i class="fa-solid fa-ban" style="font-size:20px;color:#f87171"></i>
        </div>
        <div style="flex:1;padding-top:2px">
          <div style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;color:var(--text)">
            Void Transaksi
          </div>
          <div style="font-size:13px;color:var(--sub);margin-top:4px">
            Transaksi yang di-void <strong>tidak dapat dikembalikan</strong>.
          </div>
        </div>
        <button onclick="closeVoidModal()"
          style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);
                 background:var(--surface2);cursor:pointer;color:var(--muted);font-size:12px;
                 flex-shrink:0;transition:color .15s"
          onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div style="margin:18px 24px 0;padding:14px 16px;border-radius:12px;
                  background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <span style="font-size:11.5px;color:#f87171;font-weight:600;text-transform:uppercase;letter-spacing:.5px">
            <i class="fa-solid fa-receipt" style="margin-right:5px"></i>No. Transaksi
          </span>
          <span id="void-nomor" style="font-family:monospace;font-size:13px;font-weight:700;color:var(--text)">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:11.5px;color:#f87171;font-weight:600;text-transform:uppercase;letter-spacing:.5px">
            <i class="fa-solid fa-coins" style="margin-right:5px"></i>Total
          </span>
          <span id="void-total" style="font-family:'Clash Display',sans-serif;font-size:16px;font-weight:700;color:var(--text)">—</span>
        </div>
      </div>

      <div style="padding:18px 24px 0">
        <label style="font-size:12.5px;color:var(--sub);display:block;margin-bottom:8px">
          Ketik <strong style="color:#f87171;font-family:monospace;letter-spacing:1px">VOID</strong> untuk konfirmasi
        </label>
        <input type="text" id="void-input"
          oninput="checkVoidInput()" onpaste="setTimeout(checkVoidInput,10)"
          placeholder="Ketik VOID di sini…"
          autocomplete="off" autocorrect="off" spellcheck="false"
          style="width:100%;padding:11px 14px;border-radius:10px;border:2px solid var(--border);
                 background:var(--surface2);color:var(--text);font-size:15px;
                 font-family:monospace;font-weight:700;letter-spacing:3px;
                 transition:border-color .2s;outline:none;box-sizing:border-box"
          onfocus="this.style.borderColor='#f87171'"
          onblur="this.style.borderColor='var(--border)'">
        <div id="void-input-error" style="display:none;font-size:11.5px;color:#f87171;margin-top:5px">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right:4px"></i>
          Ketik tepat <strong>VOID</strong> (huruf kapital semua)
        </div>
      </div>

      <div style="margin:14px 24px 0;display:flex;gap:8px;align-items:flex-start;
                  padding:10px 12px;border-radius:10px;
                  background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.2)">
        <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;font-size:13px;margin-top:1px;flex-shrink:0"></i>
        <div style="font-size:12px;color:var(--sub);line-height:1.5">
          Stok <strong style="color:var(--text)">tidak akan dipulihkan</strong> otomatis.
          Lakukan penyesuaian stok manual jika diperlukan.
        </div>
      </div>

      <div style="padding:18px 24px 22px;display:flex;gap:10px">
        <button type="button" onclick="closeVoidModal()"
          style="flex:1;padding:11px;border-radius:11px;border:1px solid var(--border);
                 background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;
                 cursor:pointer;font-family:inherit;transition:all .15s"
          onmouseover="this.style.background='var(--border)';this.style.color='var(--text)'"
          onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)'">
          Batal
        </button>
        <button type="button" id="void-btn-confirm" onclick="submitVoid()" disabled
          style="flex:2;padding:11px;border-radius:11px;border:none;background:#ef4444;
                 color:#fff;font-size:13.5px;font-weight:700;cursor:not-allowed;
                 font-family:inherit;opacity:.4;transition:opacity .15s;
                 display:flex;align-items:center;justify-content:center;gap:8px">
          <i class="fa-solid fa-ban"></i> Ya, Void Sekarang
        </button>
      </div>
    </div>
  </div>

  @push('scripts')
  <script>
  // ── Detail toggle ──────────────────────────────────────
  function toggleDetail(id) {
    var row = document.getElementById('detail-' + id);
    var btn = document.getElementById('btn-detail-' + id);
    var ico = btn.querySelector('i');
    var open = row.style.display !== 'none';
    row.style.display = open ? 'none' : 'table-row';
    btn.classList.toggle('active', !open);
    ico.className = open ? 'fa-solid fa-list-ul' : 'fa-solid fa-chevron-up';
  }

  // ── Void modal ─────────────────────────────────────────
  var voidTargetId = null;

  function openVoidModal(nomor, id, total) {
    voidTargetId = id;
    document.getElementById('void-nomor').textContent = nomor;
    document.getElementById('void-total').textContent = total;
    document.getElementById('void-input').value = '';
    document.getElementById('void-input-error').style.display = 'none';
    var btn = document.getElementById('void-btn-confirm');
    btn.disabled = true;
    btn.style.opacity = '.4';
    btn.style.cursor  = 'not-allowed';

    var bd = document.getElementById('void-backdrop');
    var bx = document.getElementById('void-box');
    bd.style.display = 'flex';
    requestAnimationFrame(function(){ requestAnimationFrame(function(){
      bd.style.opacity = '1';
      bx.style.opacity = '1';
      bx.style.transform = 'scale(1) translateY(0)';
      setTimeout(function(){ document.getElementById('void-input').focus(); }, 180);
    }); });
  }

  function closeVoidModal() {
    var bd = document.getElementById('void-backdrop');
    var bx = document.getElementById('void-box');
    bd.style.opacity = '0';
    bx.style.opacity = '0';
    bx.style.transform = 'scale(.93) translateY(14px)';
    setTimeout(function(){ bd.style.display = 'none'; voidTargetId = null; }, 220);
  }

  function checkVoidInput() {
    var ok  = document.getElementById('void-input').value.trim() === 'VOID';
    var btn = document.getElementById('void-btn-confirm');
    var err = document.getElementById('void-input-error');
    btn.disabled      = !ok;
    btn.style.opacity = ok ? '1' : '.4';
    btn.style.cursor  = ok ? 'pointer' : 'not-allowed';
    err.style.display = (document.getElementById('void-input').value.length > 0 && !ok) ? 'block' : 'none';
  }

  function submitVoid() {
    if (!voidTargetId) return;
    var form = document.getElementById('void-form-' + voidTargetId);
    if (!form) return;
    var btn = document.getElementById('void-btn-confirm');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses…';
    form.submit();
  }

  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeVoidModal(); });
  document.getElementById('void-backdrop').addEventListener('click', function(e){
    if (e.target === this) closeVoidModal();
  });
  </script>
  @endpush

</x-app-layout>
