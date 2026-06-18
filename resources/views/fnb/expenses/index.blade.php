<x-outlet-layout :outlet="$outlet" pageTitle="Pengeluaran">

@php
  // Siapkan data chart: label, nilai, warna per kategori (desc)
  $chartLabels = [];
  $chartValues = [];
  $chartColors = [];
  $chartBgs    = [];
  foreach ($byCategory as $slug => $amount) {
      $meta           = \App\Models\Expense::getMeta($slug);
      $chartLabels[]  = $categories[$slug] ?? $slug;
      $chartValues[]  = (float) $amount;
      $chartColors[]  = $meta['color'];
      $chartBgs[]     = $meta['color'] . '33'; // 20% opacity hex
  }
  $chartHeight = 220;
@endphp

@push('scripts')
<script>
// ── Expense category chart ──────────────────────────────
const expData = {
  labels: @json($chartLabels),
  values: @json($chartValues),
  colors: @json($chartColors),
  bgs:    @json($chartBgs),
};
let expChart = null;

function rebuildChart() {
  const el = document.getElementById('expense-chart');
  if (!el || !expData.labels.length) return;

  const dark      = !document.body.classList.contains('light');
  const textColor = dark ? '#94a3b8' : '#64748b';
  const gridColor = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';
  const tooltipBg = dark ? '#1c2336' : '#ffffff';
  const tooltipTx = dark ? '#e2e8f0' : '#1e293b';

  if (expChart) expChart.destroy();

  expChart = new Chart(el, {
    type: 'bar',
    data: {
      labels: expData.labels,
      datasets: [{
        data:            expData.values,
        backgroundColor: expData.bgs,
        borderColor:     expData.colors,
        borderWidth:     2,
        borderRadius:    8,
        borderSkipped:   false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 500 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: tooltipBg,
          titleColor: tooltipTx,
          bodyColor:  textColor,
          borderColor: dark ? '#252d42' : '#e2e8f0',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 10,
          callbacks: {
            label: ctx => '  Rp ' + ctx.raw.toLocaleString('id-ID'),
          }
        }
      },
      scales: {
        x: {
          grid:   { display: false },
          border: { display: false },
          ticks:  {
            color: textColor,
            font:  { size: 11 },
            maxRotation: 30,
            callback: (v, i, ticks) => {
              const lbl = expData.labels[i] || '';
              return lbl.length > 10 ? lbl.slice(0, 10) + '…' : lbl;
            }
          }
        },
        y: {
          grid:   { color: gridColor },
          border: { color: gridColor },
          ticks:  {
            color: textColor,
            font:  { size: 11 },
            callback: v => {
              if (v >= 1_000_000) return (v / 1_000_000).toFixed(v % 1_000_000 === 0 ? 0 : 1) + 'jt';
              if (v >= 1_000)     return (v / 1_000).toFixed(0) + 'rb';
              return v === 0 ? '0' : String(v);
            }
          }
        }
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', rebuildChart);

function openAdd() {
  document.getElementById('modal-add').classList.add('open');
  document.getElementById('add-date').value = new Date().toISOString().slice(0,10);
  document.getElementById('add-description').focus();
}

function openEdit(id, date, category, description, amount, notes) {
  document.getElementById('edit-date').value        = date;
  document.getElementById('edit-category').value    = category;
  document.getElementById('edit-description').value = description;
  document.getElementById('edit-amount-raw').value  = amount;
  document.getElementById('edit-amount-display').value = formatRupiah(amount);
  document.getElementById('edit-notes').value       = notes || '';
  document.getElementById('edit-form').action =
    '{{ url("outlets/".$outlet->id."/expenses") }}/' + id;
  document.getElementById('modal-edit').classList.add('open');
}

function confirmDelete(id, desc) {
  document.getElementById('delete-desc').textContent = desc;
  document.getElementById('delete-form').action =
    '{{ url("outlets/".$outlet->id."/expenses") }}/' + id;
  document.getElementById('modal-delete').classList.add('open');
}

function confirmDeleteCat(slug, label) {
  document.getElementById('del-cat-label').textContent = label;
  document.getElementById('del-cat-form').action =
    '{{ url("outlets/".$outlet->id."/expense-categories") }}/' + slug;
  document.getElementById('modal-del-cat').classList.add('open');
}

function formatRupiah(num) {
  return Number(num).toLocaleString('id-ID');
}

function syncRupiah(displayEl, rawId) {
  const before = displayEl.value.substring(0, displayEl.selectionStart);
  const digitsBeforeCursor = (before.match(/\d/g) || []).length;
  const raw = displayEl.value.replace(/\D/g, '');
  document.getElementById(rawId).value = raw;
  const formatted = raw ? Number(raw).toLocaleString('id-ID') : '';
  displayEl.value = formatted;
  let seen = 0, newPos = formatted.length;
  for (let i = 0; i < formatted.length; i++) {
    if (/\d/.test(formatted[i])) seen++;
    if (seen === digitsBeforeCursor) { newPos = i + 1; break; }
  }
  try { displayEl.setSelectionRange(newPos, newPos); } catch (_) {}
}

document.addEventListener('DOMContentLoaded', () => {
  ['add','edit'].forEach(prefix => {
    const display = document.getElementById(`${prefix}-amount-display`);
    if (!display) return;
    display.addEventListener('input', () => syncRupiah(display, `${prefix}-amount-raw`));
    display.addEventListener('focus', () => {
      if (!display.value) return;
      const raw = display.value.replace(/\D/g, '');
      display.value = raw;
      document.getElementById(`${prefix}-amount-raw`).value = raw;
    });
    display.addEventListener('blur', () => {
      const raw = display.value.replace(/\D/g, '');
      display.value = raw ? Number(raw).toLocaleString('id-ID') : '';
      document.getElementById(`${prefix}-amount-raw`).value = raw;
    });
  });
});
</script>
@endpush

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Pengeluaran</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Catat dan pantau pengeluaran outlet · {{ now()->translatedFormat('F Y') }}
    </p>
  </div>
  @if($user->hasPermission('expense.create'))
  <button onclick="openAdd()"
    style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;transition:opacity .15s"
    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
    <i class="fa-solid fa-plus" style="font-size:12px"></i> Tambah Pengeluaran
  </button>
  @endif
</div>

{{-- ── STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171">
      <i class="fa-solid fa-arrow-trend-down"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:20px">Rp {{ number_format($statTotal, 0, ',', '.') }}</div>
      <div class="stat-label">Total Pengeluaran Bulan Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-receipt"></i>
    </div>
    <div>
      <div class="stat-num">{{ $statCount }}</div>
      <div class="stat-label">Jumlah Transaksi Bulan Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24">
      <i class="fa-solid fa-calculator"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:20px">Rp {{ number_format($statAvg, 0, ',', '.') }}</div>
      <div class="stat-label">Rata-rata per Transaksi</div>
    </div>
  </div>
</div>

{{-- ── CHART ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-chart-bar" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
      Pengeluaran per Kategori
    </span>
    <span style="font-size:12px;color:var(--muted)">{{ now()->translatedFormat('F Y') }}</span>
  </div>

  @if($byCategory->isEmpty())
  <div style="padding:40px 24px;text-align:center">
    <i class="fa-solid fa-chart-bar" style="font-size:28px;color:var(--muted);display:block;margin-bottom:10px;opacity:.4"></i>
    <p style="font-size:13px;color:var(--muted)">Belum ada data pengeluaran bulan ini</p>
  </div>
  @else
  <div style="padding:20px 24px">
    <div style="position:relative;height:{{ $chartHeight }}px">
      <canvas id="expense-chart"></canvas>
    </div>
    {{-- Total label --}}
    <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:12px;color:var(--muted)">{{ $byCategory->count() }} kategori · {{ now()->translatedFormat('F Y') }}</span>
      <span style="font-size:13px;font-weight:700;color:#f87171">
        Total Rp {{ number_format($byCategory->sum(), 0, ',', '.') }}
      </span>
    </div>
  </div>
  @endif
</div>

{{-- ── TWO COLUMN: TABLE + SIDEBAR ── --}}
<div style="display:grid;grid-template-columns:1fr 296px;gap:20px;align-items:start">

  {{-- ── TABEL PENGELUARAN ── --}}
  <div>

    {{-- Filter bar --}}
    <div class="card animate-fadeUp d2" style="padding:14px 16px;margin-bottom:16px">
      <form method="GET" action="{{ $outlet->route('expenses.index') }}"
        style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <div style="flex:1;min-width:160px;position:relative">
          <i class="fa-solid fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px"></i>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari keterangan..."
            style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:7px 10px 7px 30px;font-size:13px;color:var(--text);outline:none;font-family:inherit"
            onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
        </div>
        <select name="category" onchange="this.form.submit()"
          style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:7px 30px 7px 10px;font-size:13px;color:var(--text);outline:none;cursor:pointer;font-family:inherit">
          <option value="">Semua Kategori</option>
          @foreach($categories as $slug => $label)
            @if($slug === 'utilitas') @continue @endif
            <option value="{{ $slug }}" {{ request('category') === $slug ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
          style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:7px 10px;font-size:13px;color:var(--text);outline:none;font-family:inherit"
          onchange="this.form.submit()" title="Dari tanggal">
        <span style="font-size:12px;color:var(--muted)">s/d</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
          style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:7px 10px;font-size:13px;color:var(--text);outline:none;font-family:inherit"
          onchange="this.form.submit()" title="Sampai tanggal">
        @if(request()->anyFilled(['search','category','date_from','date_to']))
        <a href="{{ $outlet->route('expenses.index') }}"
          style="display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:10px;border:1px solid var(--border);font-size:12.5px;color:var(--muted);text-decoration:none;white-space:nowrap"
          onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
          <i class="fa-solid fa-xmark" style="font-size:11px"></i> Reset
        </a>
        @endif
        <button type="submit"
          style="padding:7px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer">
          Cari
        </button>
      </form>
    </div>

    <div class="card animate-fadeUp d2">
      <div class="card-header">
        <span class="card-title">
          <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
          Riwayat Pengeluaran
          @if(request()->anyFilled(['search','category','date_from','date_to']))
          <span style="font-size:11.5px;font-weight:500;color:var(--muted);margin-left:8px">(difilter)</span>
          @endif
        </span>
        <span style="font-size:12.5px;color:var(--muted)">{{ $expenses->total() }} entri</span>
      </div>

      @if($expenses->isEmpty())
      <div style="padding:56px 24px;text-align:center">
        <div style="width:56px;height:56px;border-radius:14px;background:rgba(248,113,113,.15);display:grid;place-items:center;margin:0 auto 14px;font-size:20px;color:#f87171">
          <i class="fa-solid fa-receipt"></i>
        </div>
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px">
          {{ request()->anyFilled(['search','category','date_from','date_to']) ? 'Tidak Ada Hasil' : 'Belum Ada Pengeluaran' }}
        </h3>
        <p style="font-size:13.5px;color:var(--muted);max-width:280px;margin:0 auto">
          {{ request()->anyFilled(['search','category','date_from','date_to'])
              ? 'Coba ubah atau reset filter pencarian.'
              : 'Catat pengeluaran pertama dengan tombol di atas.' }}
        </p>
      </div>
      @else
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr>
              <th style="width:110px">Tanggal</th>
              <th style="width:160px">Kategori</th>
              <th>Keterangan</th>
              <th style="text-align:right;width:130px">Jumlah</th>
              <th style="width:110px">Dicatat oleh</th>
              <th style="text-align:center;width:90px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($expenses as $exp)
            @php $cm = \App\Models\Expense::getMeta($exp->category); @endphp
            <tr>
              <td>
                <div style="font-weight:600;font-size:13px;color:var(--text)">{{ $exp->date->translatedFormat('d M Y') }}</div>
                <div style="font-size:10.5px;color:var(--muted)">{{ $exp->created_at->format('H:i') }}</div>
              </td>
              <td>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:600;background:{{ $cm['bg'] }};color:{{ $cm['color'] }}">
                  <i class="fa-solid {{ $cm['icon'] }}" style="font-size:9px"></i>
                  {{ $categories[$exp->category] ?? $exp->category }}
                </span>
              </td>
              <td>
                <div style="font-size:13.5px;color:var(--text)">{{ $exp->description }}</div>
                @if($exp->notes)
                <div style="font-size:11.5px;color:var(--muted);margin-top:2px">{{ Str::limit($exp->notes, 60) }}</div>
                @endif
              </td>
              <td style="text-align:right;font-weight:700;font-size:14px;color:#f87171;white-space:nowrap">
                − Rp {{ number_format($exp->amount, 0, ',', '.') }}
              </td>
              <td style="font-size:11.5px;color:var(--sub)">{{ $exp->user->name }}</td>
              <td style="text-align:center">
                <div style="display:flex;align-items:center;justify-content:center;gap:6px">
                  @if($user->hasPermission('expense.edit'))
                  <button onclick="openEdit({{ $exp->id }}, '{{ $exp->date->format('Y-m-d') }}', '{{ $exp->category }}', @json($exp->description), '{{ $exp->amount }}', @json($exp->notes))"
                    title="Edit"
                    style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s"
                    onmouseover="this.style.background='var(--surface2)';this.style.color='var(--text)'"
                    onmouseout="this.style.background='transparent';this.style.color='var(--sub)'">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>
                  @endif
                  @if($user->hasPermission('expense.delete'))
                  <button onclick="confirmDelete({{ $exp->id }}, @json($exp->description))"
                    title="Hapus"
                    style="width:30px;height:30px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:transparent;color:#f87171;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s"
                    onmouseover="this.style.background='rgba(239,68,68,.1)'"
                    onmouseout="this.style.background='transparent'">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($expenses->hasPages())
      <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:center">
        {{ $expenses->links() }}
      </div>
      @endif
      @endif
    </div>
  </div>

  {{-- ── SIDEBAR KANAN ── --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Breakdown kategori bulan ini --}}
    <div class="card animate-fadeUp d2">
      <div class="card-header">
        <span class="card-title" style="font-size:14px">
          <i class="fa-solid fa-chart-pie" style="color:var(--ac);margin-right:8px;font-size:12px"></i>
          Per Kategori · Bulan Ini
        </span>
      </div>
      <div style="padding:16px">
        @if($byCategory->isEmpty())
          <p style="text-align:center;color:var(--muted);font-size:13px;padding:16px 0">Belum ada data</p>
        @else
        @php $maxCat = $byCategory->max(); @endphp
        @foreach($byCategory as $catSlug => $catAmount)
        @php
          $cmi      = \App\Models\Expense::getMeta($catSlug);
          $pct      = $maxCat > 0 ? round(($catAmount / $maxCat) * 100) : 0;
          $catLabel = $categories[$catSlug] ?? $catSlug;
        @endphp
        <div style="margin-bottom:14px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px">
            <div style="display:flex;align-items:center;gap:6px">
              <div style="width:22px;height:22px;border-radius:6px;background:{{ $cmi['bg'] }};display:grid;place-items:center;font-size:9px;color:{{ $cmi['color'] }}">
                <i class="fa-solid {{ $cmi['icon'] }}"></i>
              </div>
              <span style="font-size:12px;color:var(--sub)">{{ $catLabel }}</span>
            </div>
            <span style="font-size:12.5px;font-weight:700;color:var(--text)">Rp {{ number_format($catAmount, 0, ',', '.') }}</span>
          </div>
          <div class="prog-bar">
            <div class="prog-fill" style="width:{{ $pct }}%;background:{{ $cmi['color'] }}"></div>
          </div>
        </div>
        @endforeach
        @endif
      </div>
    </div>

    {{-- Kelola Kategori --}}
    <div class="card animate-fadeUp d3">
      <div class="card-header">
        <span class="card-title" style="font-size:14px">
          <i class="fa-solid fa-tags" style="color:var(--ac);margin-right:8px;font-size:12px"></i>
          Kategori
        </span>
        @if($user->hasPermission('expense.edit'))
        <button onclick="document.getElementById('modal-add-cat').classList.add('open')"
          style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:8px;background:var(--ac-lt);color:var(--ac);border:1px solid rgba(var(--ac-rgb),.2);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-plus" style="font-size:10px"></i> Tambah
        </button>
        @endif
      </div>
      <div style="padding:12px 16px">

        {{-- Sistem default --}}
        <div style="font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">Default Sistem</div>
        <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:14px">
          @foreach(\App\Models\Expense::$categories as $slug => $label)
            @if($slug === 'utilitas') @continue @endif
            @php $cmi = \App\Models\Expense::getMeta($slug); @endphp
            <div style="display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:8px">
              <div style="width:24px;height:24px;border-radius:6px;background:{{ $cmi['bg'] }};display:grid;place-items:center;font-size:10px;color:{{ $cmi['color'] }};flex-shrink:0">
                <i class="fa-solid {{ $cmi['icon'] }}"></i>
              </div>
              <span style="font-size:12.5px;color:var(--sub)">{{ $label }}</span>
            </div>
          @endforeach
        </div>

        {{-- Custom --}}
        @if($customCategories->isNotEmpty())
        <div style="font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;border-top:1px solid var(--border);padding-top:12px">
          Kategori Kustom
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
          @foreach($customCategories as $custom)
          <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 8px;border-radius:8px;background:var(--surface2)">
            <div style="display:flex;align-items:center;gap:8px">
              <div style="width:24px;height:24px;border-radius:6px;background:rgba(148,163,184,.12);display:grid;place-items:center;font-size:10px;color:#94a3b8;flex-shrink:0">
                <i class="fa-solid fa-tag"></i>
              </div>
              <span style="font-size:12.5px;color:var(--sub)">{{ $custom->label }}</span>
            </div>
            @if($user->hasPermission('expense.delete'))
            <button onclick="confirmDeleteCat('{{ $custom->slug }}', @json($custom->label))"
              title="Hapus kategori"
              style="width:22px;height:22px;border-radius:6px;border:none;background:transparent;color:var(--muted);font-size:11px;cursor:pointer;display:grid;place-items:center;transition:all .15s;flex-shrink:0"
              onmouseover="this.style.background='rgba(239,68,68,.1)';this.style.color='#f87171'"
              onmouseout="this.style.background='transparent';this.style.color='var(--muted)'">
              <i class="fa-solid fa-xmark"></i>
            </button>
            @endif
          </div>
          @endforeach
        </div>
        @endif

        @if($customCategories->isEmpty())
        <div style="border-top:1px solid var(--border);padding-top:12px;text-align:center;color:var(--muted);font-size:12px">
          Belum ada kategori kustom
        </div>
        @endif
      </div>
    </div>

    {{-- Quick add --}}
    @if($user->hasPermission('expense.create'))
    <div class="card animate-fadeUp d3" style="padding:16px;text-align:center;border:1px dashed var(--border);background:transparent">
      <div style="width:40px;height:40px;border-radius:12px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 10px;color:var(--ac);font-size:15px">
        <i class="fa-solid fa-plus"></i>
      </div>
      <p style="font-size:12.5px;color:var(--muted);margin-bottom:12px;line-height:1.5">
        Catat setiap pengeluaran outlet secara rutin untuk laporan yang akurat
      </p>
      <button onclick="openAdd()"
        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-plus" style="font-size:10px"></i> Tambah Pengeluaran
      </button>
    </div>
    @endif

  </div>
</div>

{{-- ══ MODAL TAMBAH PENGELUARAN ══ --}}
<div id="modal-add" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:480px">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-plus" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tambah Pengeluaran
      </h3>
      <button onclick="document.getElementById('modal-add').classList.remove('open')"
        style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);font-size:14px;cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form action="{{ $outlet->route('expenses.store') }}" method="POST">
    @csrf
    <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label class="f-label">Tanggal <span style="color:#f87171">*</span></label>
          <input type="date" id="add-date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" class="f-input" required>
          @error('date')<p style="font-size:11px;color:#f87171;margin-top:3px">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="f-label">Kategori <span style="color:#f87171">*</span></label>
          <select name="category" class="f-input" required>
            <optgroup label="Default Sistem">
              @foreach(\App\Models\Expense::$categories as $slug => $label)
                @if($slug === 'utilitas') @continue @endif
                <option value="{{ $slug }}" {{ old('category') === $slug ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </optgroup>
            @if($customCategories->isNotEmpty())
            <optgroup label="Kategori Kustom">
              @foreach($customCategories as $custom)
              <option value="{{ $custom->slug }}" {{ old('category') === $custom->slug ? 'selected' : '' }}>{{ $custom->label }}</option>
              @endforeach
            </optgroup>
            @endif
          </select>
          @error('category')<p style="font-size:11px;color:#f87171;margin-top:3px">{{ $message }}</p>@enderror
        </div>
      </div>

      <div>
        <label class="f-label">Keterangan <span style="color:#f87171">*</span></label>
        <input type="text" id="add-description" name="description" value="{{ old('description') }}"
          placeholder="cth: Tagihan listrik bulan ini" class="f-input" required maxlength="200">
        @error('description')<p style="font-size:11px;color:#f87171;margin-top:3px">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="f-label">Jumlah (Rp) <span style="color:#f87171">*</span></label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
          <input type="text" id="add-amount-display" inputmode="numeric"
            value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}"
            placeholder="0" class="f-input" style="padding-left:34px" required>
          <input type="hidden" id="add-amount-raw" name="amount" value="{{ old('amount') }}">
        </div>
        @error('amount')<p style="font-size:11px;color:#f87171;margin-top:3px">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400;font-size:10px">(opsional)</span></label>
        <textarea name="notes" rows="2" placeholder="Catatan tambahan..." class="f-input" style="resize:vertical">{{ old('notes') }}</textarea>
      </div>

    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('modal-add').classList.remove('open')"
        style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-floppy-disk" style="margin-right:6px;font-size:12px"></i>Simpan
      </button>
    </div>
    </form>
  </div>
</div>

{{-- ══ MODAL EDIT PENGELUARAN ══ --}}
<div id="modal-edit" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:480px">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-pen-to-square" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Edit Pengeluaran
      </h3>
      <button onclick="document.getElementById('modal-edit').classList.remove('open')"
        style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);font-size:14px;cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="edit-form" method="POST">
    @csrf @method('PATCH')
    <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label class="f-label">Tanggal <span style="color:#f87171">*</span></label>
          <input type="date" id="edit-date" name="date" class="f-input" required>
        </div>
        <div>
          <label class="f-label">Kategori <span style="color:#f87171">*</span></label>
          <select id="edit-category" name="category" class="f-input" required>
            <optgroup label="Default Sistem">
              @foreach(\App\Models\Expense::$categories as $slug => $label)
                @if($slug === 'utilitas') @continue @endif
                <option value="{{ $slug }}">{{ $label }}</option>
              @endforeach
            </optgroup>
            @if($customCategories->isNotEmpty())
            <optgroup label="Kategori Kustom">
              @foreach($customCategories as $custom)
              <option value="{{ $custom->slug }}">{{ $custom->label }}</option>
              @endforeach
            </optgroup>
            @endif
          </select>
        </div>
      </div>

      <div>
        <label class="f-label">Keterangan <span style="color:#f87171">*</span></label>
        <input type="text" id="edit-description" name="description" class="f-input" required maxlength="200">
      </div>

      <div>
        <label class="f-label">Jumlah (Rp) <span style="color:#f87171">*</span></label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
          <input type="text" id="edit-amount-display" inputmode="numeric"
            placeholder="0" class="f-input" style="padding-left:34px" required>
          <input type="hidden" id="edit-amount-raw" name="amount">
        </div>
      </div>

      <div>
        <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400;font-size:10px">(opsional)</span></label>
        <textarea id="edit-notes" name="notes" rows="2" placeholder="Catatan tambahan..." class="f-input" style="resize:vertical"></textarea>
      </div>

    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('modal-edit').classList.remove('open')"
        style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-floppy-disk" style="margin-right:6px;font-size:12px"></i>Simpan Perubahan
      </button>
    </div>
    </form>
  </div>
</div>

{{-- ══ MODAL HAPUS PENGELUARAN ══ --}}
<div id="modal-delete" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:28px 24px;text-align:center">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 14px;font-size:20px;color:#f87171">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Hapus Pengeluaran?</h3>
      <p style="font-size:13.5px;color:var(--muted);max-width:280px;margin:0 auto">
        "<span id="delete-desc" style="color:var(--sub);font-weight:600"></span>" akan dihapus permanen.
      </p>
    </div>
    <form id="delete-form" method="POST">
    @csrf @method('DELETE')
    <div style="padding:0 20px 20px;display:flex;gap:10px">
      <button type="button" onclick="document.getElementById('modal-delete').classList.remove('open')"
        style="flex:1;padding:10px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="flex:1;padding:10px;border-radius:12px;border:none;background:#ef4444;color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-trash-can" style="margin-right:6px;font-size:12px"></i>Hapus
      </button>
    </div>
    </form>
  </div>
</div>

{{-- ══ MODAL TAMBAH KATEGORI ══ --}}
<div id="modal-add-cat" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-tag" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tambah Kategori
      </h3>
      <button onclick="document.getElementById('modal-add-cat').classList.remove('open')"
        style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);font-size:14px;cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form action="{{ $outlet->route('expense-categories.store') }}" method="POST">
    @csrf
    <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
      <div>
        <label class="f-label">Nama Kategori <span style="color:#f87171">*</span></label>
        <input type="text" name="label" value="{{ old('label') }}"
          placeholder="cth: Biaya Kebersihan" class="f-input" required maxlength="100" autofocus>
        @error('label')<p style="font-size:11px;color:#f87171;margin-top:3px">{{ $message }}</p>@enderror
        <p style="font-size:11.5px;color:var(--muted);margin-top:5px">
          Kategori baru hanya berlaku untuk outlet ini. Default sistem tidak bisa diubah.
        </p>
      </div>
    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('modal-add-cat').classList.remove('open')"
        style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-plus" style="margin-right:6px;font-size:12px"></i>Tambahkan
      </button>
    </div>
    </form>
  </div>
</div>

{{-- ══ MODAL HAPUS KATEGORI ══ --}}
<div id="modal-del-cat" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:380px">
    <div style="padding:28px 24px;text-align:center">
      <div style="width:48px;height:48px;border-radius:12px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 12px;font-size:18px;color:#f87171">
        <i class="fa-solid fa-tag"></i>
      </div>
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px">Hapus Kategori?</h3>
      <p style="font-size:13px;color:var(--muted);max-width:280px;margin:0 auto;line-height:1.6">
        Kategori "<strong id="del-cat-label" style="color:var(--text)"></strong>" akan dihapus.
        Data pengeluaran yang sudah dicatat tidak terpengaruh.
      </p>
    </div>
    <form id="del-cat-form" method="POST">
    @csrf @method('DELETE')
    <div style="padding:0 20px 20px;display:flex;gap:10px">
      <button type="button" onclick="document.getElementById('modal-del-cat').classList.remove('open')"
        style="flex:1;padding:10px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="flex:1;padding:10px;border-radius:12px;border:none;background:#ef4444;color:#fff;font-size:13px;font-weight:700;cursor:pointer">
        Hapus
      </button>
    </div>
    </form>
  </div>
</div>

@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',()=>{ document.getElementById('modal-add').classList.add('open') })</script>
@endif

</x-outlet-layout>
