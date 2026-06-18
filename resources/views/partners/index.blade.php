<x-app-layout>
<x-slot name="pageTitle">Mitra UMKM</x-slot>

@php
  $total    = $partners->count();
  $aktif    = $partners->where('is_active', true)->count();
  $nonaktif = $partners->where('is_active', false)->count();
@endphp

{{-- Stats --}}
<div class="stat-grid animate-fadeUp" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-handshake"></i>
    </div>
    <div>
      <div class="stat-num">{{ $total }}</div>
      <div class="stat-label">Total Mitra</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.14);color:#34d399">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div>
      <div class="stat-num">{{ $aktif }}</div>
      <div class="stat-label">Aktif Tampil</div>
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

{{-- Grid card --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-handshake a-text" style="margin-right:8px"></i>Logo UMKM Mitra/Kerjasama</div>
    <button onclick="openModal('modal-create')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Tambah Mitra
    </button>
  </div>

  @if($partners->isEmpty())
  <div style="padding:60px 24px;text-align:center">
    <div style="width:64px;height:64px;border-radius:18px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:26px;color:var(--muted)">
      <i class="fa-solid fa-handshake"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum ada mitra UMKM</div>
    <p style="font-size:13px;color:var(--muted)">Klik <strong>Tambah Mitra</strong> untuk mengunggah logo UMKM yang sudah bekerja sama.</p>
  </div>
  @else
  <div style="padding:20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:16px">
    @foreach($partners as $partner)
    <div style="border:1px solid var(--border);border-radius:14px;background:var(--surface2);overflow:hidden;display:flex;flex-direction:column">
      <div style="height:100px;display:grid;place-items:center;background:#fff;padding:12px;position:relative">
        <img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name }}"
          style="max-width:100%;max-height:100%;object-fit:contain">
        @if(!$partner->is_active)
        <div style="position:absolute;inset:0;background:rgba(15,17,23,.55);display:grid;place-items:center">
          <span class="badge badge-gray">Nonaktif</span>
        </div>
        @endif
      </div>
      <div style="padding:12px;display:flex;flex-direction:column;gap:8px;flex:1">
        <div style="font-size:13px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $partner->name }}">
          {{ $partner->name }}
        </div>
        @if($partner->website_url)
        <a href="{{ $partner->website_url }}" target="_blank" rel="noopener"
          style="font-size:11px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:flex;align-items:center;gap:4px">
          <i class="fa-solid fa-link" style="font-size:9px"></i>{{ $partner->website_url }}
        </a>
        @endif
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:6px">
          <span style="font-size:11px;color:var(--muted)">Urutan: {{ $partner->sort_order }}</span>
          <div style="display:flex;gap:5px">
            <form method="POST" action="{{ route('partners.toggle-active', $partner) }}" style="display:inline">
              @csrf
              <button type="submit" title="{{ $partner->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                style="width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:12px;color:{{ $partner->is_active ? '#fbbf24' : '#34d399' }}">
                <i class="fa-solid {{ $partner->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
              </button>
            </form>
            <button
              data-id="{{ $partner->id }}"
              data-name="{{ $partner->name }}"
              data-website="{{ $partner->website_url }}"
              data-sort="{{ $partner->sort_order }}"
              data-logo="{{ asset('storage/' . $partner->logo) }}"
              onclick="openEdit(this)"
              style="width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:12px;color:var(--sub)" title="Edit">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button
              data-id="{{ $partner->id }}"
              data-name="{{ $partner->name }}"
              onclick="confirmDelete(this)"
              style="width:28px;height:28px;border-radius:7px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);cursor:pointer;font-size:12px;color:#f87171" title="Hapus">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @endif
</div>

{{-- ── Modal Tambah ── --}}
<div class="modal-backdrop" id="modal-create" onclick="if(event.target===this)closeModal('modal-create')">
  <div class="modal-box" style="max-width:480px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Mitra UMKM</div>
        <div style="font-size:12px;color:var(--muted);margin-top:1px">Unggah logo UMKM yang sudah bekerja sama</div>
      </div>
      <button onclick="closeModal('modal-create')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:14px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('partners.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

        <div>
          <label class="f-label">Logo <span style="color:#f87171">*</span></label>
          <input type="file" name="logo" id="cr-logo-input" accept="image/*" onchange="previewLogo(this,'cr')" style="display:none">
          <div id="cr-dropzone" onclick="document.getElementById('cr-logo-input').click()"
            style="border:1.5px dashed var(--border);border-radius:10px;padding:22px 12px;text-align:center;cursor:pointer;transition:all .15s;background:var(--surface)"
            onmouseover="this.style.borderColor='var(--ac)';this.style.background='var(--ac-lt)'"
            onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--surface)'">
            <i class="fa-solid fa-image" style="font-size:20px;color:var(--muted)"></i>
            <p style="font-size:12.5px;color:var(--sub);font-weight:600;margin-top:7px">Klik untuk unggah logo</p>
            <p style="font-size:11px;color:var(--muted);margin-top:2px">JPG/PNG/SVG/WEBP, maks 2MB</p>
          </div>
          <div id="cr-result" style="display:none;margin-top:8px;text-align:center">
            <div style="position:relative;display:inline-block">
              <img id="cr-preview" style="max-width:160px;max-height:120px;object-fit:contain;border-radius:10px;border:1px solid var(--border);background:#fff;padding:8px;display:block">
              <button type="button" onclick="clearLogo('cr')"
                style="position:absolute;top:-8px;right:-8px;width:24px;height:24px;border-radius:50%;border:none;background:rgba(0,0,0,.7);color:#fff;font-size:10px;cursor:pointer;display:grid;place-items:center">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
        </div>

        <div>
          <label class="f-label">Nama UMKM <span style="color:#f87171">*</span></label>
          <input name="name" type="text" class="f-input" placeholder="Contoh: Warung Bu Sari" required value="{{ old('name') }}">
        </div>

        <div>
          <label class="f-label">Website / Sosial Media</label>
          <input name="website_url" type="url" class="f-input" placeholder="https://instagram.com/namamitra" value="{{ old('website_url') }}">
        </div>

        <div style="width:140px">
          <label class="f-label">Urutan Tampil</label>
          <input name="sort_order" type="number" class="f-input" placeholder="0" min="0" value="{{ old('sort_order', 0) }}">
        </div>

      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-create')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-plus" style="margin-right:6px"></i>Tambah Mitra
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── Modal Edit ── --}}
<div class="modal-backdrop" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal-box" style="max-width:480px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Mitra UMKM</div>
        <div id="ed-subtitle" style="font-size:12px;color:var(--muted);margin-top:1px">Perbarui data mitra</div>
      </div>
      <button onclick="closeModal('modal-edit')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:14px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" id="ed-form" action="" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

        <div>
          <label class="f-label">Logo</label>
          <input type="file" name="logo" id="ed-logo-input" accept="image/*" onchange="previewLogo(this,'ed')" style="display:none">
          <div id="ed-dropzone" onclick="document.getElementById('ed-logo-input').click()"
            style="border:1.5px dashed var(--border);border-radius:10px;padding:10px;text-align:center;cursor:pointer;transition:all .15s;background:var(--surface)"
            onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
            <img id="ed-preview" style="max-width:140px;max-height:100px;object-fit:contain;border-radius:8px;background:#fff;padding:6px;display:block;margin:0 auto">
            <p style="font-size:11px;color:var(--muted);margin-top:6px">Klik untuk ganti logo (opsional)</p>
          </div>
        </div>

        <div>
          <label class="f-label">Nama UMKM <span style="color:#f87171">*</span></label>
          <input id="ed-name" name="name" type="text" class="f-input" placeholder="Nama UMKM" required>
        </div>

        <div>
          <label class="f-label">Website / Sosial Media</label>
          <input id="ed-website" name="website_url" type="url" class="f-input" placeholder="https://instagram.com/namamitra">
        </div>

        <div style="width:140px">
          <label class="f-label">Urutan Tampil</label>
          <input id="ed-sort" name="sort_order" type="number" class="f-input" placeholder="0" min="0">
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

{{-- ── Modal Konfirmasi Hapus ── --}}
<div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px 24px 20px;text-align:center">
      <div style="width:54px;height:54px;border-radius:14px;background:rgba(239,68,68,.12);color:#f87171;display:grid;place-items:center;font-size:22px;margin:0 auto 14px">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Mitra?</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.65">
        Mitra <strong id="del-name" style="color:var(--text)"></strong> dan logonya akan dihapus permanen.<br>
        Tindakan ini tidak dapat dibatalkan.
      </p>
    </div>
    <form method="POST" id="del-form" action="">
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

@push('scripts')
<script>
function previewLogo(input, pfx) {
  if (!input.files || !input.files[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  if (pfx === 'cr') {
    document.getElementById('cr-dropzone').style.display = 'none';
    document.getElementById('cr-result').style.display   = 'block';
    document.getElementById('cr-preview').src             = url;
  } else {
    document.getElementById('ed-preview').src = url;
  }
}

function clearLogo(pfx) {
  document.getElementById(pfx + '-logo-input').value = '';
  document.getElementById(pfx + '-dropzone').style.display = 'block';
  document.getElementById(pfx + '-result').style.display   = 'none';
}

function openEdit(btn) {
  const d = btn.dataset;
  document.getElementById('ed-form').action          = '/partners/' + d.id;
  document.getElementById('ed-subtitle').textContent = 'Edit: ' + d.name;
  document.getElementById('ed-name').value            = d.name    || '';
  document.getElementById('ed-website').value         = d.website || '';
  document.getElementById('ed-sort').value            = d.sort    || 0;
  document.getElementById('ed-preview').src           = d.logo;
  document.getElementById('ed-logo-input').value      = '';

  openModal('modal-edit');
}

function confirmDelete(btn) {
  const d = btn.dataset;
  document.getElementById('del-form').action      = '/partners/' + d.id;
  document.getElementById('del-name').textContent = d.name;
  openModal('modal-delete');
}
</script>
@endpush

</x-app-layout>
