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
                @if($outlet->slug)
                <button type="button"
                  onclick="openQR('{{ $outlet->slug }}','{{ addslashes($outlet->nama) }}','{{ $outlet->alamat ?? '' }}')"
                  class="btn" style="padding:6px 10px;font-size:12px" title="Print QR Code Order">
                  <i class="fa-solid fa-qrcode"></i>
                </button>
                @endif
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

  {{-- QR Code Modal --}}
  <div id="qr-backdrop"
    style="display:none;position:fixed;inset:0;z-index:9100;background:rgba(0,0,0,.7);
           backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:20px;
           opacity:0;transition:opacity .2s">
    <div id="qr-box"
      style="background:var(--surface);border:1px solid var(--border);border-radius:20px;
             width:100%;max-width:460px;box-shadow:0 32px 80px rgba(0,0,0,.6);
             transform:scale(.94) translateY(12px);transition:transform .25s,opacity .25s;opacity:0;overflow:hidden">

      {{-- Modal header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:10px;background:var(--ac-lt);color:var(--ac);
                      display:grid;place-items:center;font-size:15px">
            <i class="fa-solid fa-qrcode"></i>
          </div>
          <div>
            <div style="font-weight:700;font-size:14px;color:var(--text)">QR Code Menu Order</div>
            <div style="font-size:11.5px;color:var(--muted)" id="qr-outlet-name-sub"></div>
          </div>
        </div>
        <button onclick="closeQR()" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:18px;padding:4px;line-height:1">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      {{-- Preview card --}}
      <div style="padding:24px;display:flex;justify-content:center">
        <div id="qr-print-area"
          style="background:#ffffff;border-radius:16px;padding:28px 24px;width:300px;
                 box-shadow:0 4px 24px rgba(0,0,0,.12);text-align:center;font-family:'Plus Jakarta Sans',sans-serif">

          {{-- Logo / brand --}}
          <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:20px">
            <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#ef4444);
                        display:grid;place-items:center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                <path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9zm0 0V7a2 2 0 012-2h2M9 5V3m6 2V3m6 4H3"/>
              </svg>
            </div>
            <span style="font-size:15px;font-weight:800;color:#0f1117;letter-spacing:-.5px">Pabalu</span>
          </div>

          {{-- QR Code --}}
          <div style="display:flex;justify-content:center;margin-bottom:18px">
            <div style="border:3px solid #f59e0b;border-radius:12px;padding:10px;background:#fff;display:inline-block">
              <div id="qr-canvas"></div>
            </div>
          </div>

          {{-- Teks outlet --}}
          <div id="qr-outlet-name"
            style="font-size:16px;font-weight:800;color:#0f1117;margin-bottom:4px;letter-spacing:-.3px"></div>
          <div id="qr-outlet-addr"
            style="font-size:11px;color:#64748b;margin-bottom:16px;line-height:1.4"></div>

          {{-- Instruksi --}}
          <div style="background:#fef9ec;border:1px solid #fde68a;border-radius:10px;padding:10px 12px;margin-bottom:14px">
            <div style="font-size:13px;font-weight:700;color:#92400e;margin-bottom:3px">
              📱 Scan untuk Pesan
            </div>
            <div style="font-size:11px;color:#78350f;line-height:1.5">
              Arahkan kamera HP ke QR Code<br>untuk melihat menu &amp; pesan online
            </div>
          </div>

          {{-- URL teks --}}
          <div id="qr-url-text"
            style="font-size:10px;color:#94a3b8;word-break:break-all;font-family:monospace;
                   background:#f8fafc;border-radius:6px;padding:6px 8px"></div>

          {{-- Footer --}}
          <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #e2e8f0;
                      font-size:10px;color:#cbd5e1">
            Powered by <strong style="color:#f59e0b">Pabalu</strong> — Sistem Kasir UMKM
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div style="padding:0 24px 24px;display:flex;gap:10px">
        <button onclick="closeQR()" class="btn" style="flex:1;justify-content:center;font-size:13px;padding:10px">
          <i class="fa-solid fa-xmark"></i> Tutup
        </button>
        <button onclick="printQR()" class="btn btn-primary" style="flex:2;justify-content:center;font-size:13px;padding:10px">
          <i class="fa-solid fa-print"></i> Print QR Code
        </button>
      </div>
    </div>
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
  /* ══ QR Code Modal ══════════════════════════════════════ */
  var qrInstance = null;

  function openQR(slug, nama, alamat) {
    var url = '{{ url('/order') }}/' + slug;

    document.getElementById('qr-outlet-name-sub').textContent = nama;
    document.getElementById('qr-outlet-name').textContent     = nama;
    document.getElementById('qr-outlet-addr').textContent     = alamat || '';
    document.getElementById('qr-url-text').textContent        = url;

    // Hapus QR lama
    var canvas = document.getElementById('qr-canvas');
    canvas.innerHTML = '';
    if (qrInstance) { qrInstance.clear(); qrInstance = null; }

    qrInstance = new QRCode(canvas, {
      text          : url,
      width         : 200,
      height        : 200,
      colorDark     : '#0f1117',
      colorLight    : '#ffffff',
      correctLevel  : QRCode.CorrectLevel.H,
    });

    var backdrop = document.getElementById('qr-backdrop');
    var box      = document.getElementById('qr-box');
    backdrop.style.display = 'flex';
    requestAnimationFrame(function(){ requestAnimationFrame(function(){
      backdrop.style.opacity = '1';
      box.style.opacity      = '1';
      box.style.transform    = 'scale(1) translateY(0)';
    }); });
  }

  function closeQR() {
    var backdrop = document.getElementById('qr-backdrop');
    var box      = document.getElementById('qr-box');
    backdrop.style.opacity = '0';
    box.style.opacity      = '0';
    box.style.transform    = 'scale(.94) translateY(12px)';
    setTimeout(function(){ backdrop.style.display = 'none'; }, 220);
  }

  function printQR() {
    var area    = document.getElementById('qr-print-area').outerHTML;
    var appName = '{{ \App\Models\Setting::get('app_name', config('app.name')) }}';
    var win     = window.open('', '_blank', 'width=420,height=680');
    win.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>QR Code — ` + document.getElementById('qr-outlet-name').textContent + `</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{
      font-family:'Plus Jakarta Sans',sans-serif;
      background:#f8fafc;
      display:flex;align-items:center;justify-content:center;
      min-height:100vh;padding:24px;
    }
    @media print{
      body{background:#fff;padding:0}
      .no-print{display:none!important}
      @page{margin:10mm;size:A5 portrait}
    }
  </style>
</head>
<body>
  <div>` + area + `</div>
  <div class="no-print" style="margin-top:20px;text-align:center">
    <button onclick="window.print()"
      style="background:#f59e0b;color:#fff;border:none;border-radius:10px;
             padding:12px 32px;font-size:14px;font-weight:700;cursor:pointer;
             font-family:'Plus Jakarta Sans',sans-serif">
      🖨️ Print
    </button>
    <button onclick="window.close()"
      style="background:#e2e8f0;color:#475569;border:none;border-radius:10px;
             padding:12px 20px;font-size:14px;font-weight:600;cursor:pointer;margin-left:10px;
             font-family:'Plus Jakarta Sans',sans-serif">
      Tutup
    </button>
  </div>
</body>
</html>`);
    win.document.close();
  }

  document.getElementById('qr-backdrop').addEventListener('click', function(e){
    if (e.target === this) closeQR();
  });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeQR(); });
  </script>
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
