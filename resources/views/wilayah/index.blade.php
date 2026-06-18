<x-app-layout>
<x-slot name="pageTitle">Wilayah Indonesia</x-slot>

<style>
body{--bg:#0f1117;--surface:#161b27;--surface2:#1c2336;--border:#252d42;--text:#e2e8f0;--muted:#64748b;--sub:#94a3b8}
.card{background:var(--surface);border:1px solid var(--border);border-radius:16px}
.tbl{width:100%;border-collapse:collapse;font-size:13px}
.tbl thead th{padding:10px 14px;background:var(--surface2);color:var(--muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;text-align:left}
.tbl tbody tr:hover{background:var(--surface2)}
.tbl tbody td{padding:12px 14px;border-bottom:1px solid var(--border);color:var(--sub)}
.tbl tbody tr:last-child td{border-bottom:none}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600}
.badge-blue{background:rgba(99,102,241,.14);color:#818cf8}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center;padding:20px}
.modal-overlay.open{display:flex}
.modal-box{background:var(--surface);border:1px solid var(--border);border-radius:18px;width:100%;max-width:420px;overflow:hidden}
.modal-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-body{padding:22px}
.modal-footer{padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}
.f-label{font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.f-input{width:100%;padding:9px 13px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;box-sizing:border-box}
.f-input:focus{border-color:var(--ac)}
.btn-primary{padding:9px 20px;border-radius:10px;border:none;background:var(--ac);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px}
.btn-secondary{padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
.btn-danger{padding:6px 12px;border-radius:8px;border:none;background:rgba(248,113,113,.12);color:#f87171;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px}
.btn-icon{padding:6px 12px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px;text-decoration:none}
.btn-icon:hover{background:var(--border);color:var(--text)}
.btn-edit{padding:6px 12px;border-radius:8px;border:none;background:rgba(99,102,241,.1);color:#818cf8;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px}
.btn-edit:hover{background:rgba(99,102,241,.2)}
.alert-success{padding:12px 16px;border-radius:10px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);color:#34d399;font-size:13px;display:flex;align-items:center;gap:8px;margin-bottom:18px}
</style>

<div style="max-width:900px;margin:0 auto">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:22px;font-size:13px;color:var(--muted)">
    <a href="{{ route('dashboard') }}" style="color:var(--muted);text-decoration:none"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-house-chimney"></i> Dashboard
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">Wilayah Indonesia</span>
  </div>

  {{-- Flash --}}
  @if(session('success'))
  <div class="alert-success">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
  </div>
  @endif

  {{-- Header Card --}}
  <div class="card" style="margin-bottom:16px">
    <div style="padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:16px">
      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;border-radius:11px;background:rgba(16,185,129,.14);color:#34d399;display:grid;place-items:center;font-size:17px;flex-shrink:0">
          <i class="fa-solid fa-map-location-dot"></i>
        </div>
        <div>
          <div style="font-size:16px;font-weight:700;color:var(--text)">Wilayah Indonesia</div>
          <div style="font-size:12px;color:var(--muted)">{{ $provinces->count() }} provinsi terdaftar</div>
        </div>
      </div>
      <button class="btn-primary" onclick="openModal('modal-add')">
        <i class="fa-solid fa-plus"></i> Tambah Provinsi
      </button>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:44px">No</th>
            <th>Nama Provinsi</th>
            <th style="width:160px;text-align:center">Kab / Kota</th>
            <th style="width:200px;text-align:right;padding-right:18px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($provinces as $i => $prov)
          <tr>
            <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
            <td>
              <div style="font-weight:600;color:var(--text)">{{ $prov->name }}</div>
            </td>
            <td style="text-align:center">
              <span class="badge badge-blue">{{ $prov->regencies_count }}</span>
            </td>
            <td style="text-align:right;padding-right:14px">
              <div style="display:flex;justify-content:flex-end;gap:6px;flex-wrap:wrap">
                <a href="{{ route('wilayah.provinces.show', $prov) }}" class="btn-icon">
                  <i class="fa-solid fa-eye"></i> Kab/Kota
                </a>
                <button class="btn-edit" onclick="openEdit({{ $prov->id }}, '{{ addslashes($prov->name) }}')">
                  <i class="fa-solid fa-pen"></i> Edit
                </button>
                <form method="POST" action="{{ route('wilayah.provinces.destroy', $prov) }}"
                  onsubmit="return confirm('Hapus provinsi {{ addslashes($prov->name) }}? Semua kab/kota dan kecamatannya akan terhapus.')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-danger">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" style="text-align:center;padding:32px;color:var(--muted)">
              <i class="fa-solid fa-map" style="font-size:24px;margin-bottom:8px;display:block;opacity:.4"></i>
              Belum ada provinsi. Klik "Tambah Provinsi" untuk memulai.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

{{-- Modal Tambah Provinsi --}}
<div class="modal-overlay" id="modal-add" onclick="closeIfOverlay(event,'modal-add')">
  <div class="modal-box">
    <div class="modal-header">
      <div style="font-size:15px;font-weight:700;color:var(--text)">Tambah Provinsi</div>
      <button onclick="closeModal('modal-add')" style="background:none;border:none;color:var(--muted);font-size:18px;cursor:pointer;line-height:1">&times;</button>
    </div>
    <form method="POST" action="{{ route('wilayah.provinces.store') }}">
      @csrf
      <div class="modal-body">
        <label class="f-label">Nama Provinsi <span style="color:#f87171">*</span></label>
        <input type="text" name="name" class="f-input" placeholder="Contoh: Sulawesi Tenggara" autofocus value="{{ old('name') }}">
        @error('name')
        <div style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
        @enderror
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeModal('modal-add')">Batal</button>
        <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Edit Provinsi --}}
<div class="modal-overlay" id="modal-edit" onclick="closeIfOverlay(event,'modal-edit')">
  <div class="modal-box">
    <div class="modal-header">
      <div style="font-size:15px;font-weight:700;color:var(--text)">Edit Provinsi</div>
      <button onclick="closeModal('modal-edit')" style="background:none;border:none;color:var(--muted);font-size:18px;cursor:pointer;line-height:1">&times;</button>
    </div>
    <form method="POST" id="edit-form" action="">
      @csrf @method('PATCH')
      <div class="modal-body">
        <label class="f-label">Nama Provinsi <span style="color:#f87171">*</span></label>
        <input type="text" name="name" id="edit-name" class="f-input" placeholder="Nama provinsi">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeModal('modal-edit')">Batal</button>
        <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Perbarui</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openModal(id) {
  document.getElementById(id).classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}
function closeIfOverlay(e, id) {
  if (e.target === document.getElementById(id)) closeModal(id);
}

function openEdit(id, name) {
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-form').action = '/wilayah/provinces/' + id;
  openModal('modal-edit');
}

@if($errors->has('name'))
  document.addEventListener('DOMContentLoaded', () => openModal('modal-add'));
@endif
</script>
@endpush

</x-app-layout>
