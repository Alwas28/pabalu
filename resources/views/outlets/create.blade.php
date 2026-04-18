<x-app-layout title="Tambah Outlet">

  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted)">
    <a href="{{ route('outlets.index') }}" style="color:var(--muted);text-decoration:none;transition:color .15s"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-shop"></i> Kelola Outlet
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
    <span style="color:var(--text)">Tambah Outlet Baru</span>
  </div>

  <form method="POST" action="{{ route('outlets.store') }}">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

      <div class="card animate-fadeUp">
        <div class="card-header">
          <div class="card-title"><i class="fa-solid fa-store" style="color:var(--ac);margin-right:8px"></i>Informasi Outlet</div>
        </div>
        <div class="card-body">

          <div class="f-group">
            <label for="nama" class="f-label">Nama Outlet <span style="color:#f87171">*</span></label>
            <input id="nama" name="nama" type="text" class="f-input"
              value="{{ old('nama') }}" required placeholder="cth. Pabalu Kendari Pusat">
            @error('nama')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
          </div>

          <div class="f-row">
            <div class="f-group">
              <label for="telepon" class="f-label">No. Telepon</label>
              <input id="telepon" name="telepon" type="text" class="f-input"
                value="{{ old('telepon') }}" placeholder="0401-xxxxxxx">
              @error('telepon')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
            </div>
            <div class="f-group">
              <label for="email" class="f-label">Email Outlet</label>
              <input id="email" name="email" type="email" class="f-input"
                value="{{ old('email') }}" placeholder="outlet@email.com">
              @error('email')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="f-group">
            <label for="alamat" class="f-label">Alamat</label>
            <textarea id="alamat" name="alamat" class="f-input" rows="2"
              placeholder="Alamat lengkap outlet">{{ old('alamat') }}</textarea>
            @error('alamat')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
          </div>

          <div class="f-group" style="margin-bottom:0">
            <label for="keterangan" class="f-label">Keterangan</label>
            <textarea id="keterangan" name="keterangan" class="f-input" rows="2"
              placeholder="Catatan tambahan tentang outlet ini…">{{ old('keterangan') }}</textarea>
          </div>

        </div>
      </div>

      @if(auth()->user()->isAdmin() && $owners->isNotEmpty())
      {{-- Admin-only: assign owner --}}
      <div class="card animate-fadeUp" style="grid-column:1/2">
        <div class="card-header">
          <div class="card-title"><i class="fa-solid fa-user-tie" style="color:var(--ac);margin-right:8px"></i>Assign Owner</div>
        </div>
        <div class="card-body">
          <div class="f-group" style="margin-bottom:0">
            <label for="owner_id" class="f-label">Owner Outlet</label>
            <select id="owner_id" name="owner_id" class="f-input">
              <option value="">— Tidak di-assign —</option>
              @foreach($owners as $o)
              <option value="{{ $o->id }}" @selected(old('owner_id') == $o->id)>
                {{ $o->name }} ({{ $o->email }})
              </option>
              @endforeach
            </select>
            <div style="font-size:11.5px;color:var(--muted);margin-top:5px">
              <i class="fa-solid fa-circle-info"></i> Outlet tanpa owner hanya bisa diakses oleh admin.
            </div>
          </div>
        </div>
      </div>
      @endif

      <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card animate-fadeUp d1">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-toggle-on" style="color:var(--ac);margin-right:8px"></i>Status</div>
          </div>
          <div class="card-body">
            <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;gap:12px">
              <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">Outlet Aktif</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Outlet dapat digunakan untuk operasional</div>
              </div>
              <div style="position:relative;flex-shrink:0">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                  @checked(old('is_active', true))
                  style="appearance:none;width:42px;height:24px;border-radius:99px;background:var(--surface2);
                         border:1px solid var(--border);cursor:pointer;transition:background .2s"
                  onchange="this.style.background=this.checked?'var(--ac)':'var(--surface2)'">
                <div style="position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;
                            background:#fff;pointer-events:none;transition:transform .2s"
                  id="toggle-thumb"></div>
              </div>
            </label>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px">
          <button type="submit" class="btn btn-primary" style="justify-content:center;padding:11px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Outlet
          </button>
          <a href="{{ route('outlets.index') }}" class="btn" style="justify-content:center;padding:11px;text-decoration:none">
            <i class="fa-solid fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>

    </div>
  </form>

  @push('scripts')
  <script>
  // Sync toggle thumb position with checkbox state
  var cb    = document.getElementById('is_active');
  var thumb = document.getElementById('toggle-thumb');
  function syncThumb() {
    thumb.style.transform = cb.checked ? 'translateX(18px)' : 'translateX(0)';
    cb.style.background   = cb.checked ? 'var(--ac)' : 'var(--surface2)';
  }
  cb.addEventListener('change', syncThumb);
  syncThumb();
  </script>
  @endpush

</x-app-layout>
