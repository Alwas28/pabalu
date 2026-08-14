<x-app-layout>
<x-slot name="pageTitle">Kelola Outlet</x-slot>

@php
  $canCreate = Auth::user()->hasPermission('outlet.create');
  $canEdit   = Auth::user()->hasPermission('outlet.edit');
  $canDelete = Auth::user()->hasPermission('outlet.delete');
  $isAdmin   = Auth::user()->role === 'admin';

  $total    = $outlets->count();
  $aktif    = $outlets->where('is_active', true)->count();
  $nonaktif = $outlets->where('is_active', false)->count();
@endphp

{{-- Stats --}}
<div class="stat-grid animate-fadeUp" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-shop"></i>
    </div>
    <div>
      <div class="stat-num">{{ $total }}</div>
      <div class="stat-label">Total Outlet</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.14);color:#34d399">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div>
      <div class="stat-num">{{ $aktif }}</div>
      <div class="stat-label">Outlet Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(148,163,184,.12);color:var(--muted)">
      <i class="fa-solid fa-circle-xmark"></i>
    </div>
    <div>
      <div class="stat-num">{{ $nonaktif }}</div>
      <div class="stat-label">Tidak Aktif</div>
    </div>
  </div>
</div>

{{-- Table card --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-shop a-text" style="margin-right:8px"></i>Daftar Outlet</div>
    @if($canCreate)
    <a href="{{ route('outlets.create') }}"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;text-decoration:none">
      <i class="fa-solid fa-plus"></i> Tambah Outlet
    </a>
    @endif
  </div>

  @if($outlets->isEmpty())
  <div style="padding:60px 24px;text-align:center">
    <div style="width:64px;height:64px;border-radius:18px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:26px;color:var(--muted)">
      <i class="fa-solid fa-shop"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum ada outlet</div>
    <p style="font-size:13px;color:var(--muted)">
      @if($canCreate)
        Klik <strong>Tambah Outlet</strong> untuk menambahkan outlet pertama Anda.
      @else
        Belum ada outlet yang terdaftar.
      @endif
    </p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama Outlet</th>
          <th style="width:90px">Kode</th>
          <th>Jenis Usaha</th>
          @if($isAdmin)<th>Pemilik</th>@endif
          <th>Telepon</th>
          <th>Alamat</th>
          <th>Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($outlets as $i => $outlet)
        <tr>
          <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:36px;height:36px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;flex-shrink:0;font-size:14px">
                <i class="fa-solid {{ $outlet->outletType?->icon ?? 'fa-shop' }}"></i>
              </div>
              <div>
                <a href="{{ $outlet->route('show') }}"
                   class="td-main"
                   style="color:var(--text);text-decoration:none;font-weight:500"
                   onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--text)'">
                  {{ $outlet->name }}
                </a>
                @if($outlet->outletType)
                <div style="font-size:11px;color:var(--muted)">{{ $outlet->outletType->name }}</div>
                @endif
              </div>
            </div>
          </td>
          <td>
            @php $typeCode = $outlet->outletType?->type_code ?? '??'; $outletCode = $outlet->code ?? '????'; @endphp
            <span style="font-size:12px;font-family:monospace;font-weight:700;letter-spacing:.04em;padding:3px 10px;border-radius:8px;background:var(--surface2);color:var(--text)">
              {{ $typeCode }}{{ $outletCode }}
            </span>
          </td>
          <td>
            @if($outlet->outletType)
            <span style="font-size:12px;padding:3px 10px;border-radius:99px;background:var(--surface2);color:var(--sub);font-weight:500">
              {{ $outlet->outletType->name }}
            </span>
            @else
            <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
          @if($isAdmin)
          <td>
            <div style="font-size:12.5px;font-weight:500;color:var(--text)">{{ $outlet->owner?->name ?? '—' }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $outlet->owner?->email }}</div>
          </td>
          @endif
          <td style="font-size:12.5px">{{ $outlet->phone ?: '—' }}</td>
          <td style="font-size:12px;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            {{ $outlet->address ?: '—' }}
          </td>
          <td>
            @if($outlet->is_active)
            <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:7px"></i>Aktif</span>
            @else
            <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:7px"></i>Nonaktif</span>
            @endif
          </td>
          <td>
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
              @if($canEdit)
              {{-- Toggle Active --}}
              <form method="POST" action="{{ route('outlets.toggle-active', $outlet) }}" style="display:inline">
                @csrf
                <button type="submit" title="{{ $outlet->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:13px;color:{{ $outlet->is_active ? '#fbbf24' : '#34d399' }};transition:color .15s">
                  <i class="fa-solid {{ $outlet->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              {{-- Edit --}}
              <button onclick="openEdit({{ $outlet->id }}, {{ json_encode($outlet->only(['name','address','phone'])) }}, {{ $outlet->outlet_type_id ?? 'null' }}, '{{ $outlet->order_mode }}', {{ json_encode(['province_id'=>$outlet->province_id,'regency_id'=>$outlet->regency_id,'district_id'=>$outlet->district_id,'kelurahan'=>$outlet->kelurahan,'latitude'=>$outlet->latitude,'longitude'=>$outlet->longitude]) }})"
                style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:13px;color:var(--sub);transition:color .15s"
                onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'" title="Edit">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              @endif
              @if($canDelete)
              <button onclick="confirmDelete({{ $outlet->id }}, '{{ addslashes($outlet->name) }}')"
                style="width:32px;height:32px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);cursor:pointer;font-size:13px;color:#f87171;transition:background .15s"
                onmouseover="this.style.background='rgba(239,68,68,.18)'" onmouseout="this.style.background='rgba(239,68,68,.08)'" title="Hapus">
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
  @endif
</div>

{{-- ── CSS Searchable Select ── --}}
@if($canEdit)
<style>
.ss-wrap{position:relative}
.ss-box{position:relative}
.ss-input{padding-right:32px!important;cursor:pointer}
.ss-input.typing{cursor:text}
.ss-caret{position:absolute;right:11px;top:50%;transform:translateY(-50%);font-size:10px;color:var(--muted);pointer-events:none;transition:transform .2s}
.ss-wrap.open .ss-caret{transform:translateY(-50%) rotate(180deg)}
.ss-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:9999;background:var(--surface);border:1px solid var(--border);border-radius:12px;max-height:190px;overflow-y:auto;display:none;box-shadow:0 8px 28px rgba(0,0,0,.5);scrollbar-width:thin;scrollbar-color:var(--border) transparent}
.ss-wrap.open .ss-dropdown{display:block}
.ss-option{padding:9px 13px;font-size:13px;color:var(--sub);cursor:pointer;transition:background .1s}
.ss-option:hover{background:var(--surface2);color:var(--text)}
.ss-option.selected{color:var(--ac);background:var(--ac-lt);font-weight:600}
.ss-empty{padding:12px 13px;font-size:12px;color:var(--muted);text-align:center}
.ss-disabled .ss-input{opacity:.5;pointer-events:none}
.leaflet-pane,.leaflet-control{z-index:5}
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endif

{{-- ── Modal Edit Outlet ── --}}
@if($canEdit)
<div class="modal-backdrop" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal-box" style="max-width:620px;max-height:90vh;overflow-y:auto">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Outlet</div>
        <div style="font-size:12px;color:var(--muted);margin-top:1px" id="edit-subtitle">Perbarui data outlet</div>
      </div>
      <button onclick="closeModal('modal-edit')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:14px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" id="edit-form" action="">
      @csrf
      @method('PUT')
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

        <div>
          <label class="f-label">Nama Outlet <span style="color:#f87171">*</span></label>
          <input id="edit-name" name="name" type="text" class="f-input" placeholder="Nama outlet" required>
        </div>

        <div>
          <label class="f-label">Jenis Usaha <span style="color:#f87171">*</span></label>
          <select id="edit-type" name="outlet_type_id" class="f-input" required>
            <option value="">— Pilih jenis usaha —</option>
            @foreach($outletTypes as $type)
            <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="f-label">Mode Transaksi</label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <label id="edit-mode-quick-label"
              style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:2px solid var(--border);cursor:pointer;transition:all .15s">
              <input type="radio" id="edit-mode-quick" name="order_mode" value="quick" style="accent-color:var(--ac)">
              <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Quick Pay</div>
                <div style="font-size:11px;color:var(--muted)">Langsung bayar</div>
              </div>
            </label>
            <label id="edit-mode-kitchen-label"
              style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:2px solid var(--border);cursor:pointer;transition:all .15s">
              <input type="radio" id="edit-mode-kitchen" name="order_mode" value="kitchen" style="accent-color:var(--ac)">
              <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Kitchen Order</div>
                <div style="font-size:11px;color:var(--muted)">Pesan dulu, bayar nanti</div>
              </div>
            </label>
          </div>
        </div>

        <div>
          <label class="f-label">No. Telepon</label>
          <input id="edit-phone" name="phone" type="tel" class="f-input" placeholder="08xx-xxxx-xxxx">
        </div>

        {{-- ── Lokasi Outlet ── --}}
        <div style="border-top:1px solid var(--border);padding-top:16px;display:flex;flex-direction:column;gap:12px">
          <div style="display:flex;align-items:center;gap:7px">
            <div style="width:26px;height:26px;border-radius:7px;background:rgba(16,185,129,.14);color:#34d399;display:grid;place-items:center;font-size:11px;flex-shrink:0">
              <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <span style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.7px">Lokasi Outlet</span>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div>
              <label class="f-label">Provinsi</label>
              <div class="ss-wrap" id="eprov-wrap">
                <div class="ss-box">
                  <input type="text" class="ss-input f-input" id="eprov-input" placeholder="— Cari provinsi —" autocomplete="off">
                  <i class="fa-solid fa-chevron-down ss-caret"></i>
                </div>
                <input type="hidden" name="province_id" id="eprovince_id">
                <div class="ss-dropdown" id="eprov-dropdown"></div>
              </div>
            </div>
            <div>
              <label class="f-label">Kabupaten / Kota</label>
              <div class="ss-wrap ss-disabled" id="ereg-wrap">
                <div class="ss-box">
                  <input type="text" class="ss-input f-input" id="ereg-input" placeholder="— Pilih provinsi dulu —" autocomplete="off">
                  <i class="fa-solid fa-chevron-down ss-caret"></i>
                </div>
                <input type="hidden" name="regency_id" id="eregency_id">
                <div class="ss-dropdown" id="ereg-dropdown"></div>
              </div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div>
              <label class="f-label">Kecamatan</label>
              <div class="ss-wrap ss-disabled" id="edist-wrap">
                <div class="ss-box">
                  <input type="text" class="ss-input f-input" id="edist-input" placeholder="— Pilih kab/kota dulu —" autocomplete="off">
                  <i class="fa-solid fa-chevron-down ss-caret"></i>
                </div>
                <input type="hidden" name="district_id" id="edistrict_id">
                <div class="ss-dropdown" id="edist-dropdown"></div>
              </div>
            </div>
            <div>
              <label class="f-label">Desa / Kelurahan</label>
              <input type="text" id="edit-kelurahan" name="kelurahan" class="f-input" placeholder="Nama desa atau kelurahan">
            </div>
          </div>

          <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
              <label class="f-label" style="margin:0">Titik Lokasi di Peta <span style="font-weight:400;color:var(--muted)">(opsional)</span></label>
              <button type="button" onclick="useMyLocationEdit()"
                style="padding:5px 11px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:11px;font-weight:600;cursor:pointer;font-family:inherit">
                <i class="fa-solid fa-location-crosshairs"></i> Lokasi Saya
              </button>
            </div>
            <div id="edit-map" style="height:220px;border-radius:12px;overflow:hidden;border:1px solid var(--border)"></div>
            <input type="hidden" name="latitude" id="edit-latitude">
            <input type="hidden" name="longitude" id="edit-longitude">
          </div>
        </div>

        <div>
          <label class="f-label">Alamat</label>
          <textarea id="edit-address" name="address" class="f-input" rows="2" style="resize:vertical" placeholder="Alamat lengkap outlet..."></textarea>
        </div>

      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- ── Modal Konfirmasi Hapus ── --}}
@if($canDelete)
<div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px 24px 20px;text-align:center">
      <div style="width:54px;height:54px;border-radius:14px;background:rgba(239,68,68,.12);color:#f87171;display:grid;place-items:center;font-size:22px;margin:0 auto 14px">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Outlet?</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.65">
        Outlet <strong id="delete-name" style="color:var(--text)"></strong> akan dihapus permanen.<br>
        Tindakan ini tidak dapat dibatalkan.
      </p>
    </div>
    <form method="POST" id="delete-form" action="">
      @csrf
      @method('DELETE')
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-trash-can" style="margin-right:6px"></i>Ya, Hapus
        </button>
      </div>
    </form>
  </div>
</div>
@endif

@push('scripts')
<script>
// ── SearchableSelect class (modal edit) ──────────────────
class SearchableSelect {
  constructor({ wrap, input, hidden, dropdown, placeholder, onSelect }) {
    this.wrap       = document.getElementById(wrap);
    this.input      = document.getElementById(input);
    this.hidden     = document.getElementById(hidden);
    this.dropdown   = document.getElementById(dropdown);
    this.placeholder = placeholder;
    this.onSelect   = onSelect;
    this.options    = [];
    this.value      = '';
    this.label      = '';
    this._bind();
  }
  _bind() {
    this.input.addEventListener('click',   () => this._open());
    this.input.addEventListener('focus',   () => this._open());
    this.input.addEventListener('input',   () => this._filter(this.input.value));
    this.input.addEventListener('keydown', e  => { if (e.key === 'Escape') this._close(); });
    document.addEventListener('click', e => {
      if (!this.wrap.contains(e.target)) this._close();
    });
  }
  setOptions(opts) {
    this.options = opts;
    this.value = ''; this.label = '';
    this.hidden.value = '';
    this.input.value  = '';
    this.input.placeholder = this.placeholder;
    this.wrap.classList.remove('ss-disabled');
    this._render(opts);
  }
  setValue(val, lbl) {
    this.value = String(val); this.label = lbl;
    this.hidden.value = val;
    this.input.value  = lbl;
    this._render(this.options);
  }
  disable(ph) {
    this.wrap.classList.add('ss-disabled');
    this.value = ''; this.label = '';
    this.hidden.value = '';
    this.input.value  = '';
    this.input.placeholder = ph || this.placeholder;
    this.options = [];
    this.dropdown.innerHTML = '';
    this._close();
  }
  _open() {
    if (this.wrap.classList.contains('ss-disabled')) return;
    this.wrap.classList.add('open');
    this.input.classList.add('typing');
    this._filter(this.input.value);
  }
  _close() {
    this.wrap.classList.remove('open');
    this.input.classList.remove('typing');
    this.input.value = this.label;
  }
  _filter(q) {
    const filtered = q.trim()
      ? this.options.filter(o => o.label.toLowerCase().includes(q.toLowerCase()))
      : this.options;
    this._render(filtered);
  }
  _render(opts) {
    if (!opts.length) {
      this.dropdown.innerHTML = '<div class="ss-empty"><i class="fa-solid fa-magnifying-glass" style="margin-right:5px;opacity:.4"></i>Tidak ditemukan</div>';
      return;
    }
    const cur = this.value;
    this.dropdown.innerHTML = opts.map(o =>
      `<div class="ss-option${String(o.value) === cur ? ' selected' : ''}" data-v="${o.value}" data-l="${o.label.replace(/"/g,'&quot;')}">${o.label}</div>`
    ).join('');
    this.dropdown.querySelectorAll('.ss-option').forEach(el => {
      el.addEventListener('mousedown', e => {
        e.preventDefault();
        this.setValue(el.dataset.v, el.dataset.l);
        this._close();
        if (this.onSelect) this.onSelect(el.dataset.v);
      });
    });
  }
}

// ── Init tiga searchable select untuk modal edit ─────────
const eProvData = @json($provinces->map(fn($p) => ['value' => $p->id, 'label' => $p->name]));
const eApiReg  = '{{ route("wilayah.api.regencies") }}';
const eApiDist = '{{ route("wilayah.api.districts") }}';

const eProvSS = new SearchableSelect({
  wrap: 'eprov-wrap', input: 'eprov-input', hidden: 'eprovince_id',
  dropdown: 'eprov-dropdown', placeholder: '— Cari provinsi —',
  onSelect: val => eLoadRegencies(val),
});
const eRegSS = new SearchableSelect({
  wrap: 'ereg-wrap', input: 'ereg-input', hidden: 'eregency_id',
  dropdown: 'ereg-dropdown', placeholder: '— Pilih provinsi dulu —',
  onSelect: val => eLoadDistricts(val),
});
const eDistSS = new SearchableSelect({
  wrap: 'edist-wrap', input: 'edist-input', hidden: 'edistrict_id',
  dropdown: 'edist-dropdown', placeholder: '— Pilih kab/kota dulu —',
  onSelect: () => {},
});

eProvSS.setOptions(eProvData);

async function eLoadRegencies(provinceId) {
  eRegSS.disable('Memuat...');
  eDistSS.disable('— Pilih kab/kota dulu —');
  if (!provinceId) { eRegSS.disable('— Pilih provinsi dulu —'); return; }
  const data = await fetch(eApiReg + '?province_id=' + provinceId).then(r => r.json());
  eRegSS.setOptions(data.map(r => ({ value: r.id, label: r.name })));
  eRegSS.input.placeholder = '— Pilih kabupaten/kota —';
}

async function eLoadDistricts(regencyId) {
  eDistSS.disable('Memuat...');
  if (!regencyId) { eDistSS.disable('— Pilih kab/kota dulu —'); return; }
  const data = await fetch(eApiDist + '?regency_id=' + regencyId).then(r => r.json());
  if (data.length) {
    eDistSS.setOptions(data.map(d => ({ value: d.id, label: d.name })));
    eDistSS.input.placeholder = '— Pilih kecamatan —';
  } else {
    eDistSS.setOptions([]);
    eDistSS.input.placeholder = '(data belum tersedia)';
  }
}

// ── openEdit ─────────────────────────────────────────────
async function openEdit(id, data, typeId, orderMode, loc) {
  loc = loc || {};
  document.getElementById('edit-form').action = '/outlets/' + id;
  document.getElementById('edit-subtitle').textContent = 'Edit: ' + data.name;
  document.getElementById('edit-name').value    = data.name    || '';
  document.getElementById('edit-phone').value   = data.phone   || '';
  document.getElementById('edit-address').value = data.address || '';
  document.getElementById('edit-kelurahan').value = loc.kelurahan || '';

  const sel = document.getElementById('edit-type');
  if (sel) {
    for (let i = 0; i < sel.options.length; i++) {
      sel.options[i].selected = (sel.options[i].value == typeId);
    }
  }
  const mode = orderMode || 'quick';
  document.getElementById('edit-mode-quick').checked   = (mode === 'quick');
  document.getElementById('edit-mode-kitchen').checked = (mode === 'kitchen');
  highlightMode(mode);

  // Reset lokasi
  eProvSS.setValue('', '');
  eRegSS.disable('— Pilih provinsi dulu —');
  eDistSS.disable('— Pilih kab/kota dulu —');

  setEditMapPosition(loc.latitude, loc.longitude);

  openModal('modal-edit');
  if (editMap) setTimeout(() => editMap.invalidateSize(), 50);

  // Restore lokasi async (setelah modal terbuka agar user tidak menunggu)
  if (loc.province_id) {
    const prov = eProvData.find(p => p.value == loc.province_id);
    if (prov) eProvSS.setValue(prov.value, prov.label);

    const regData = await fetch(eApiReg + '?province_id=' + loc.province_id).then(r => r.json());
    eRegSS.setOptions(regData.map(r => ({ value: r.id, label: r.name })));
    eRegSS.input.placeholder = '— Pilih kabupaten/kota —';

    if (loc.regency_id) {
      const reg = regData.find(r => r.id == loc.regency_id);
      if (reg) eRegSS.setValue(reg.id, reg.name);

      const distData = await fetch(eApiDist + '?regency_id=' + loc.regency_id).then(r => r.json());
      if (distData.length) {
        eDistSS.setOptions(distData.map(d => ({ value: d.id, label: d.name })));
        eDistSS.input.placeholder = '— Pilih kecamatan —';
        if (loc.district_id) {
          const dist = distData.find(d => d.id == loc.district_id);
          if (dist) eDistSS.setValue(dist.id, dist.name);
        }
      }
    }
  }
}

function highlightMode(mode) {
  const ac = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim();
  document.getElementById('edit-mode-quick-label').style.borderColor   = mode === 'quick'   ? ac : 'var(--border)';
  document.getElementById('edit-mode-kitchen-label').style.borderColor = mode === 'kitchen' ? ac : 'var(--border)';
}

// ── Peta lokasi outlet (modal edit) ──────────────────────
let editMap, editMarker;
const DEFAULT_MAP_CENTER = [-3.9985, 122.5127]; // Kendari

function initEditMap() {
  editMap = L.map('edit-map').setView(DEFAULT_MAP_CENTER, 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
  }).addTo(editMap);

  editMarker = L.marker(DEFAULT_MAP_CENTER, { draggable: true }).addTo(editMap);
  editMarker.on('dragend', () => syncEditLatLng(editMarker.getLatLng()));
  editMap.on('click', e => {
    editMarker.setLatLng(e.latlng);
    syncEditLatLng(e.latlng);
  });
}

function setEditMapPosition(lat, lng) {
  if (!editMap) return;
  lat = parseFloat(lat);
  lng = parseFloat(lng);
  const hasPos = !isNaN(lat) && !isNaN(lng);
  const pos = hasPos ? [lat, lng] : DEFAULT_MAP_CENTER;
  editMap.setView(pos, hasPos ? 16 : 12);
  editMarker.setLatLng(pos);
  if (hasPos) syncEditLatLng({ lat, lng });
  else {
    document.getElementById('edit-latitude').value  = '';
    document.getElementById('edit-longitude').value = '';
  }
}

function syncEditLatLng(latlng) {
  document.getElementById('edit-latitude').value  = latlng.lat.toFixed(7);
  document.getElementById('edit-longitude').value = latlng.lng.toFixed(7);
}

function useMyLocationEdit() {
  if (!navigator.geolocation) { alert('Browser tidak mendukung geolokasi.'); return; }
  navigator.geolocation.getCurrentPosition(
    pos => {
      const latlng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
      editMap.setView(latlng, 16);
      editMarker.setLatLng(latlng);
      syncEditLatLng(latlng);
    },
    () => alert('Gagal mengambil lokasi. Pastikan izin lokasi diaktifkan di browser.')
  );
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[name="order_mode"]').forEach(r => {
    r.addEventListener('change', () => highlightMode(r.value));
  });
  initEditMap();
});

function confirmDelete(id, name) {
  document.getElementById('delete-form').action = '/outlets/' + id;
  document.getElementById('delete-name').textContent = name;
  openModal('modal-delete');
}
</script>
@endpush

</x-app-layout>
