<x-app-layout title="Kelola Produk">

  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div style="font-size:13px;color:var(--sub)">Kelola produk yang dijual di setiap outlet.</div>
    @can('product.create')
    <a href="{{ route('products.create') }}" class="btn btn-primary" style="text-decoration:none">
      <i class="fa-solid fa-plus"></i> Tambah Produk
    </a>
    @endcan
  </div>

  {{-- Stats --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)"><i class="fa-solid fa-cubes"></i></div>
      <div><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-label">Total Produk</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
      <div><div class="stat-num" style="color:#34d399">{{ $stats['aktif'] }}</div><div class="stat-label">Aktif</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(148,163,184,.12);color:#94a3b8"><i class="fa-solid fa-circle-xmark"></i></div>
      <div><div class="stat-num" style="color:#94a3b8">{{ $stats['nonaktif'] }}</div><div class="stat-label">Nonaktif</div></div>
    </div>
  </div>

  {{-- Filter --}}
  <div class="card">
    <div class="card-body" style="padding:14px 20px">
      <form method="GET" action="{{ route('products.index') }}"
        style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div style="position:relative;flex:1;min-width:180px">
          <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, kode…"
            class="f-input" style="padding-left:34px;padding-top:8px;padding-bottom:8px">
        </div>
        @if ($assignedOutletId ?? false)
          @php $assignedOutlet = $outlets->firstWhere('id', $assignedOutletId); @endphp
          <div class="f-input" style="display:flex;align-items:center;gap:8px;color:var(--ac);font-weight:600;pointer-events:none;padding-top:8px;padding-bottom:8px">
            <i class="fa-solid fa-store"></i>{{ $assignedOutlet?->nama ?? 'Outlet #'.$assignedOutletId }}
            <i class="fa-solid fa-lock" style="font-size:10px;opacity:.7;margin-left:auto"></i>
          </div>
          <input type="hidden" name="outlet_id" value="{{ $assignedOutletId }}">
        @else
          <select name="outlet_id" class="f-input" style="width:auto;padding-top:8px;padding-bottom:8px">
            <option value="">Semua Outlet</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        @endif
        <select name="category_id" class="f-input" style="width:auto;padding-top:8px;padding-bottom:8px">
          <option value="">Semua Kategori</option>
          @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->nama }}</option>
          @endforeach
        </select>
        <select name="status" class="f-input" style="width:auto;padding-top:8px;padding-bottom:8px">
          <option value="">Semua Status</option>
          <option value="1" @selected(request('status') === '1')>Aktif</option>
          <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:8px 16px">
          <i class="fa-solid fa-filter"></i> Filter
        </button>
        @if(request('q') || request('outlet_id') || request('category_id') || request('status') !== null && request('status') !== '')
        <a href="{{ route('products.index') }}" class="btn" style="padding:8px 14px;text-decoration:none">
          <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-cubes" style="color:var(--ac);margin-right:8px"></i>Daftar Produk</div>
      <div style="font-size:12px;color:var(--muted)">{{ $products->total() }} produk ditemukan</div>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:40px">#</th>
            <th>Produk</th>
            <th>Outlet</th>
            <th>Kategori</th>
            <th style="text-align:right">Harga Jual</th>
            <th>Satuan</th>
            <th style="text-align:center">Status</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($products as $product)
          <tr>
            <td style="color:var(--muted);font-size:12px">{{ $products->firstItem() + $loop->index }}</td>
            <td class="td-main">
              <div>
                <div style="font-weight:600;color:var(--text)">{{ $product->nama }}</div>
                @if($product->kode)
                <code style="font-size:10.5px;color:var(--muted);font-family:monospace">{{ $product->kode }}</code>
                @endif
              </div>
            </td>
            <td>
              <span style="font-size:12.5px;background:var(--ac-lt);color:var(--ac);padding:3px 9px;border-radius:7px;font-weight:600">
                {{ $product->outlet?->nama ?? '—' }}
              </span>
            </td>
            <td style="font-size:13px">{{ $product->category?->nama ?? '—' }}</td>
            <td style="text-align:right;font-weight:600;color:var(--text);font-family:'Clash Display',sans-serif">
              Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
            </td>
            <td style="font-size:12.5px">{{ $product->satuan }}</td>
            <td style="text-align:center">
              @if($product->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:7px"></i> Aktif</span>
              @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:7px"></i> Nonaktif</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:6px;justify-content:flex-end">
                @can('product.update')
                <a href="{{ route('products.edit', $product) }}"
                  class="btn" style="padding:6px 12px;font-size:12px;text-decoration:none">
                  <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
                @endcan
                @can('product.delete')
                <button type="button"
                  onclick="askDelete({{ $product->id }}, '{{ addslashes($product->nama) }}')"
                  class="btn btn-danger" style="padding:6px 10px;font-size:12px">
                  <i class="fa-solid fa-trash"></i>
                </button>
                @endcan
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="9" style="text-align:center;padding:48px;color:var(--muted)">
              <i class="fa-solid fa-cubes" style="font-size:32px;display:block;margin-bottom:12px;opacity:.4"></i>
              Belum ada produk. Tambahkan produk pertama.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($products->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div style="font-size:12px;color:var(--muted)">Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $products->total() }}</div>
      <div style="display:flex;gap:4px">
        @if($products->onFirstPage())
        <span class="btn" style="padding:6px 10px;font-size:12px;opacity:.4;cursor:default"><i class="fa-solid fa-chevron-left"></i></span>
        @else
        <a href="{{ $products->previousPageUrl() }}" class="btn" style="padding:6px 10px;font-size:12px;text-decoration:none"><i class="fa-solid fa-chevron-left"></i></a>
        @endif
        @foreach($products->getUrlRange(max(1,$products->currentPage()-2),min($products->lastPage(),$products->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="btn {{ $page == $products->currentPage() ? 'btn-primary' : '' }}"
          style="padding:6px 12px;font-size:12px;text-decoration:none;min-width:34px;justify-content:center">{{ $page }}</a>
        @endforeach
        @if($products->hasMorePages())
        <a href="{{ $products->nextPageUrl() }}" class="btn" style="padding:6px 10px;font-size:12px;text-decoration:none"><i class="fa-solid fa-chevron-right"></i></a>
        @else
        <span class="btn" style="padding:6px 10px;font-size:12px;opacity:.4;cursor:default"><i class="fa-solid fa-chevron-right"></i></span>
        @endif
      </div>
    </div>
    @endif
  </div>

  {{-- Delete Dialog --}}
  <div id="confirm-backdrop"
    style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.6);
           backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px;
           opacity:0;transition:opacity .2s">
    <div id="confirm-box"
      style="background:var(--surface);border:1px solid var(--border);border-radius:20px;
             width:100%;max-width:380px;box-shadow:0 24px 64px rgba(0,0,0,.5);
             transform:scale(.94) translateY(12px);transition:transform .25s,opacity .25s;opacity:0">
      <div style="padding:28px 28px 0;text-align:center">
        <div style="width:56px;height:56px;border-radius:16px;background:rgba(239,68,68,.15);
                    display:grid;place-items:center;margin:0 auto 14px;font-size:22px;color:#f87171">
          <i class="fa-solid fa-cube"></i>
        </div>
        <div id="confirm-title" style="font-family:'Clash Display',sans-serif;font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px"></div>
        <div id="confirm-body" style="font-size:13px;color:var(--sub);line-height:1.6">Produk ini akan dihapus secara permanen.</div>
      </div>
      <div style="padding:20px 28px 24px;display:flex;gap:10px;margin-top:16px">
        <button type="button" onclick="closeConfirm()" class="btn" style="flex:1;justify-content:center;font-size:13px;padding:10px">Batal</button>
        <form id="confirm-form" method="POST" style="flex:1">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;font-size:13px;padding:10px">
            <i class="fa-solid fa-trash"></i> Ya, Hapus
          </button>
        </form>
      </div>
    </div>
  </div>

  @push('scripts')
  <script>
  function askDelete(id, nama) {
    var backdrop = document.getElementById('confirm-backdrop');
    var box      = document.getElementById('confirm-box');
    document.getElementById('confirm-title').textContent = 'Hapus "' + nama + '"?';
    document.getElementById('confirm-form').action       = '/products/' + id;
    backdrop.style.display = 'flex';
    requestAnimationFrame(function(){ requestAnimationFrame(function(){
      backdrop.style.opacity = '1';
      box.style.opacity      = '1';
      box.style.transform    = 'scale(1) translateY(0)';
    }); });
  }
  function closeConfirm() {
    var backdrop = document.getElementById('confirm-backdrop');
    var box      = document.getElementById('confirm-box');
    backdrop.style.opacity = '0';
    box.style.opacity      = '0';
    box.style.transform    = 'scale(.94) translateY(12px)';
    setTimeout(function(){ backdrop.style.display = 'none'; }, 220);
  }
  document.getElementById('confirm-backdrop').addEventListener('click', function(e){ if(e.target===this) closeConfirm(); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeConfirm(); });
  </script>
  @endpush

</x-app-layout>
