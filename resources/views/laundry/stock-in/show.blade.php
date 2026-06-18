<x-outlet-layout :outlet="$outlet" pageTitle="Detail Stok Masuk">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <a href="{{ $outlet->route('stock-in.index') }}"
    style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:8px;transition:color .15s"
    onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
    <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Riwayat Tambah Stok
  </a>
  <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
      <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">
        Detail Penerimaan Stok
      </h2>
      <p style="font-size:13px;color:var(--muted);margin-top:2px">
        Dicatat pada {{ $stockIn->created_at->translatedFormat('d F Y, H:i') }}
      </p>
    </div>
    {{-- Aksi + Badge total --}}
    <div style="display:flex;align-items:center;gap:10px">
      @if($user->hasPermission('stock.in'))
      <a href="{{ $outlet->route('stock-in.edit', [$stockIn]) }}"
        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;transition:all .15s"
        onmouseover="this.style.borderColor='var(--ac)';this.style.color='var(--text)'"
        onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--sub)'">
        <i class="fa-solid fa-pen-to-square" style="font-size:11px"></i> Edit
      </a>
      <button onclick="document.getElementById('modal-hapus').classList.add('open')"
        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;border:1px solid rgba(239,68,68,.35);background:transparent;color:#f87171;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s"
        onmouseover="this.style.background='rgba(239,68,68,.1)'"
        onmouseout="this.style.background='transparent'">
        <i class="fa-solid fa-trash-can" style="font-size:11px"></i> Hapus
      </button>
      @endif
      <div style="text-align:right">
        <div style="font-size:22px;font-weight:800;color:var(--text)">+{{ number_format($stockIn->items->sum('qty')) }}</div>
        <div style="font-size:11.5px;color:var(--muted)">total unit masuk</div>
      </div>
      <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--ac),var(--ac2));display:grid;place-items:center;color:#fff;font-size:18px">
        <i class="fa-solid fa-truck-ramp-box"></i>
      </div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start">

  {{-- ── DAFTAR PRODUK ── --}}
  <div>
    <div class="card animate-fadeUp d1">
      <div class="card-header">
        <span class="card-title">
          <i class="fa-solid fa-boxes-stacking" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
          Daftar Produk Masuk
          <span style="margin-left:8px;font-size:12px;font-weight:500;color:var(--muted)">({{ $stockIn->items->count() }} item)</span>
        </span>
      </div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr>
              <th>Produk</th>
              <th style="text-align:center;width:90px">Jumlah</th>
              <th>Satuan</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($stockIn->items as $item)
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  @if($item->product?->image)
                    <img src="{{ Storage::url($item->product->image) }}" alt=""
                      style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
                  @else
                    <div style="width:36px;height:36px;border-radius:8px;background:var(--surface2);display:grid;place-items:center;color:var(--muted);font-size:14px">
                      <i class="fa-solid fa-cube"></i>
                    </div>
                  @endif
                  <div>
                    <div style="font-weight:600;font-size:13.5px;color:var(--text)">{{ $item->product?->name ?? '(Produk dihapus)' }}</div>
                    @if($item->product?->category)
                      <div style="font-size:11.5px;color:var(--muted)">{{ $item->product->category->name }}</div>
                    @endif
                  </div>
                </div>
              </td>
              <td style="text-align:center">
                <span style="font-size:17px;font-weight:800;color:var(--ac)">+{{ number_format($item->qty) }}</span>
              </td>
              <td style="font-size:13px;color:var(--sub)">{{ $item->product?->unit ?? '—' }}</td>
              <td style="font-size:12.5px;color:var(--muted)">{{ $item->notes ?: '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ── INFO SIDEBAR ── --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Info penerimaan --}}
    <div class="card animate-fadeUp d1">
      <div class="card-header">
        <span class="card-title">
          <i class="fa-solid fa-circle-info" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
          Info Penerimaan
        </span>
      </div>
      <div style="padding:16px 20px;display:flex;flex-direction:column;gap:14px">

        <div>
          <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Tanggal</div>
          <div style="font-size:14px;font-weight:600;color:var(--text)">
            {{ $stockIn->date->translatedFormat('d F Y') }}
          </div>
        </div>

        <div style="height:1px;background:var(--border)"></div>

        @if($stockIn->notes)
        <div style="height:1px;background:var(--border)"></div>
        <div>
          <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Catatan</div>
          <div style="font-size:13.5px;color:var(--sub);line-height:1.5">{{ $stockIn->notes }}</div>
        </div>
        @endif

        <div style="height:1px;background:var(--border)"></div>

        <div>
          <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Dicatat oleh</div>
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--ac),var(--ac2));display:grid;place-items:center;color:#fff;font-size:11px;font-weight:700">
              {{ strtoupper(substr($stockIn->user->name, 0, 1)) }}
            </div>
            <div>
              <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $stockIn->user->name }}</div>
              <div style="font-size:11px;color:var(--muted)">{{ $stockIn->created_at->format('H:i') }}</div>
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- Ringkasan --}}
    <div class="card animate-fadeUp d2">
      <div style="padding:16px 20px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div style="text-align:center;padding:12px;background:var(--surface2);border-radius:12px">
          <div style="font-size:22px;font-weight:800;color:var(--text)">{{ $stockIn->items->count() }}</div>
          <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Jenis Produk</div>
        </div>
        <div style="text-align:center;padding:12px;background:var(--ac-lt);border-radius:12px">
          <div style="font-size:22px;font-weight:800;color:var(--ac)">{{ number_format($stockIn->items->sum('qty')) }}</div>
          <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Total Unit</div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- ── MODAL HAPUS ── --}}
<div id="modal-hapus" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:28px 24px;text-align:center">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 14px;font-size:20px;color:#f87171">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:8px">Hapus Data Tambah Stok?</h3>
      <p style="font-size:13.5px;color:var(--muted);max-width:300px;margin:0 auto;line-height:1.5">
        Stok <strong style="color:var(--text)">{{ $stockIn->items->count() }} produk</strong> akan dikurangi kembali sesuai jumlah yang pernah dimasukkan.
      </p>
    </div>
    <form action="{{ $outlet->route('stock-in.destroy', [$stockIn]) }}" method="POST">
    @csrf @method('DELETE')
    <div style="padding:0 20px 20px;display:flex;gap:10px">
      <button type="button" onclick="document.getElementById('modal-hapus').classList.remove('open')"
        style="flex:1;padding:10px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="flex:1;padding:10px;border-radius:12px;border:none;background:#ef4444;color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-trash-can" style="margin-right:6px;font-size:12px"></i>Hapus &amp; Kembalikan Stok
      </button>
    </div>
    </form>
  </div>
</div>

</x-outlet-layout>
