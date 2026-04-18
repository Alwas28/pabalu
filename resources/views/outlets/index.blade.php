<x-app-layout title="Kelola Outlet">

  {{-- Header bar --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div style="font-size:13px;color:var(--sub)">Kelola data outlet yang terdaftar dalam sistem.</div>
    @can('outlet.create')
    <a href="{{ route('outlets.create') }}" class="btn btn-primary" style="text-decoration:none">
      <i class="fa-solid fa-plus"></i> Tambah Outlet
    </a>
    @endcan
  </div>

  {{-- Stats --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)"><i class="fa-solid fa-shop"></i></div>
      <div>
        <div class="stat-num">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Outlet</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
      <div>
        <div class="stat-num" style="color:#34d399">{{ $stats['aktif'] }}</div>
        <div class="stat-label">Aktif</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(148,163,184,.12);color:#94a3b8"><i class="fa-solid fa-circle-xmark"></i></div>
      <div>
        <div class="stat-num" style="color:#94a3b8">{{ $stats['nonaktif'] }}</div>
        <div class="stat-label">Nonaktif</div>
      </div>
    </div>
  </div>

  {{-- Filter --}}
  <div class="card">
    <div class="card-body" style="padding:14px 20px">
      <form method="GET" action="{{ route('outlets.index') }}"
        style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div style="position:relative;flex:1;min-width:200px">
          <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, alamat, telepon…"
            class="f-input" style="padding-left:34px;padding-top:8px;padding-bottom:8px">
        </div>
        <select name="status" class="f-input" style="width:auto;padding-top:8px;padding-bottom:8px">
          <option value="">Semua Status</option>
          <option value="1" @selected(request('status') === '1')>Aktif</option>
          <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:8px 16px">
          <i class="fa-solid fa-filter"></i> Filter
        </button>
        @if(request('q') || request('status') !== null && request('status') !== '')
        <a href="{{ route('outlets.index') }}" class="btn" style="padding:8px 14px;text-decoration:none">
          <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-shop" style="color:var(--ac);margin-right:8px"></i>Daftar Outlet</div>
      <div style="font-size:12px;color:var(--muted)">{{ $outlets->total() }} outlet ditemukan</div>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:40px">#</th>
            <th>Nama Outlet</th>
            <th>Alamat</th>
            <th>Kontak</th>
            <th style="text-align:center">Produk</th>
            <th style="text-align:center">Status</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($outlets as $outlet)
          <tr>
            <td style="color:var(--muted);font-size:12px">{{ $outlets->firstItem() + $loop->index }}</td>
            <td class="td-main">
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;
                            flex-shrink:0;background:var(--ac-lt);color:var(--ac);font-size:15px">
                  <i class="fa-solid fa-store"></i>
                </div>
                <div>
                  <div style="font-weight:600;color:var(--text)">{{ $outlet->nama }}</div>
                  @if($outlet->email)
                  <div style="font-size:11.5px;color:var(--muted)">{{ $outlet->email }}</div>
                  @endif
                  @if(auth()->user()->isAdmin() && !$outlet->owner_id)
                  <div style="font-size:11px;color:#f59e0b;margin-top:2px">
                    <i class="fa-solid fa-triangle-exclamation"></i> Belum ada owner
                  </div>
                  @endif
                </div>
              </div>
            </td>
            <td style="font-size:13px;max-width:200px">
              <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">
                {{ $outlet->alamat ?? '—' }}
              </div>
            </td>
            <td style="font-size:13px">{{ $outlet->telepon ?? '—' }}</td>
            <td style="text-align:center">
              <span style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:var(--ac)">
                {{ $outlet->products_count }}
              </span>
            </td>
            <td style="text-align:center">
              @if($outlet->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:7px"></i> Aktif</span>
              @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:7px"></i> Nonaktif</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:6px;justify-content:flex-end">
                @can('outlet.update')
                <a href="{{ route('outlets.edit', $outlet) }}"
                  class="btn" style="padding:6px 12px;font-size:12px;text-decoration:none">
                  <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
                @endcan
                @can('outlet.delete')
                <button type="button"
                  onclick="askDelete({{ $outlet->id }}, '{{ addslashes($outlet->nama) }}', {{ $outlet->products_count }})"
                  class="btn btn-danger" style="padding:6px 10px;font-size:12px">
                  <i class="fa-solid fa-trash"></i>
                </button>
                @endcan
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" style="text-align:center;padding:48px;color:var(--muted)">
              <i class="fa-solid fa-store-slash" style="font-size:32px;display:block;margin-bottom:12px"></i>
              @if(request('q') || request('status') !== '')
                Tidak ada outlet yang cocok dengan filter.
              @else
                Belum ada outlet terdaftar.
              @endif
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($outlets->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div style="font-size:12px;color:var(--muted)">
        Menampilkan {{ $outlets->firstItem() }}–{{ $outlets->lastItem() }} dari {{ $outlets->total() }} outlet
      </div>
      <div style="display:flex;gap:4px">
        @if($outlets->onFirstPage())
        <span class="btn" style="padding:6px 10px;font-size:12px;opacity:.4;cursor:default"><i class="fa-solid fa-chevron-left"></i></span>
        @else
        <a href="{{ $outlets->previousPageUrl() }}" class="btn" style="padding:6px 10px;font-size:12px;text-decoration:none"><i class="fa-solid fa-chevron-left"></i></a>
        @endif
        @foreach($outlets->getUrlRange(max(1,$outlets->currentPage()-2),min($outlets->lastPage(),$outlets->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="btn {{ $page == $outlets->currentPage() ? 'btn-primary' : '' }}"
          style="padding:6px 12px;font-size:12px;text-decoration:none;min-width:34px;justify-content:center">{{ $page }}</a>
        @endforeach
        @if($outlets->hasMorePages())
        <a href="{{ $outlets->nextPageUrl() }}" class="btn" style="padding:6px 10px;font-size:12px;text-decoration:none"><i class="fa-solid fa-chevron-right"></i></a>
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
          <i class="fa-solid fa-store-slash"></i>
        </div>
        <div id="confirm-title" style="font-family:'Clash Display',sans-serif;font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px"></div>
        <div id="confirm-body" style="font-size:13px;color:var(--sub);line-height:1.6"></div>
      </div>
      <div id="confirm-warning"
        style="display:none;margin:14px 28px 0;padding:10px 14px;border-radius:10px;
               background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);
               font-size:12px;color:#fbbf24;gap:8px;align-items:flex-start">
        <i class="fa-solid fa-triangle-exclamation" style="flex-shrink:0;margin-top:1px"></i>
        <span id="confirm-warning-text"></span>
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
  function askDelete(id, nama, produkCount) {
    var backdrop = document.getElementById('confirm-backdrop');
    var box      = document.getElementById('confirm-box');
    var warning  = document.getElementById('confirm-warning');
    document.getElementById('confirm-title').textContent = 'Hapus Outlet "' + nama + '"?';
    document.getElementById('confirm-body').textContent  = 'Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('confirm-form').action       = '/outlets/' + id;
    if (produkCount > 0) {
      document.getElementById('confirm-warning-text').textContent =
        'Outlet ini masih memiliki ' + produkCount + ' produk dan tidak dapat dihapus.';
      warning.style.display = 'flex';
    } else {
      warning.style.display = 'none';
    }
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
