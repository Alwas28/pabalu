<x-app-layout title="Edit Outlet">

  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted)">
    <a href="{{ route('outlets.index') }}" style="color:var(--muted);text-decoration:none;transition:color .15s"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-shop"></i> Kelola Outlet
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
    <span style="color:var(--text)">Edit: {{ $outlet->nama }}</span>
  </div>

  <form method="POST" action="{{ route('outlets.update', $outlet) }}">
    @csrf
    @method('PUT')
    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

      <div class="card animate-fadeUp">
        <div class="card-header">
          <div class="card-title"><i class="fa-solid fa-store" style="color:var(--ac);margin-right:8px"></i>Informasi Outlet</div>
        </div>
        <div class="card-body">

          <div class="f-group">
            <label for="nama" class="f-label">Nama Outlet <span style="color:#f87171">*</span></label>
            <input id="nama" name="nama" type="text" class="f-input"
              value="{{ old('nama', $outlet->nama) }}" required placeholder="cth. Pabalu Kendari Pusat">
            @error('nama')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
          </div>

          <div class="f-row">
            <div class="f-group">
              <label for="telepon" class="f-label">No. Telepon</label>
              <input id="telepon" name="telepon" type="text" class="f-input"
                value="{{ old('telepon', $outlet->telepon) }}" placeholder="0401-xxxxxxx">
              @error('telepon')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
            </div>
            <div class="f-group">
              <label for="email" class="f-label">Email Outlet</label>
              <input id="email" name="email" type="email" class="f-input"
                value="{{ old('email', $outlet->email) }}" placeholder="outlet@email.com">
              @error('email')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="f-group">
            <label for="alamat" class="f-label">Alamat</label>
            <textarea id="alamat" name="alamat" class="f-input" rows="2"
              placeholder="Alamat lengkap outlet">{{ old('alamat', $outlet->alamat) }}</textarea>
            @error('alamat')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
          </div>

          <div class="f-group" style="margin-bottom:0">
            <label for="keterangan" class="f-label">Keterangan</label>
            <textarea id="keterangan" name="keterangan" class="f-input" rows="2"
              placeholder="Catatan tambahan…">{{ old('keterangan', $outlet->keterangan) }}</textarea>
          </div>

        </div>
      </div>

      @if(auth()->user()->isAdmin() && $owners->isNotEmpty())
      {{-- Admin-only: assign/change owner --}}
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
              <option value="{{ $o->id }}" @selected(old('owner_id', $outlet->owner_id) == $o->id)>
                {{ $o->name }} ({{ $o->email }})
              </option>
              @endforeach
            </select>
            @if($outlet->owner_id)
            <div style="font-size:11.5px;color:var(--muted);margin-top:5px">
              <i class="fa-solid fa-circle-info"></i> Mengubah owner akan memindahkan outlet ini ke akun owner yang dipilih.
            </div>
            @else
            <div style="font-size:11.5px;color:#f59e0b;margin-top:5px">
              <i class="fa-solid fa-triangle-exclamation"></i> Outlet ini belum memiliki owner. Assign agar owner bisa mengaksesnya.
            </div>
            @endif
          </div>
        </div>
      </div>
      @endif

      <div style="display:flex;flex-direction:column;gap:16px">
        {{-- Info stat --}}
        <div class="card animate-fadeUp" style="border-color:var(--ac)44">
          <div class="card-body" style="padding:20px;text-align:center">
            <div style="width:52px;height:52px;border-radius:14px;display:grid;place-items:center;
                        margin:0 auto 10px;background:var(--ac-lt);color:var(--ac);font-size:20px">
              <i class="fa-solid fa-store"></i>
            </div>
            <div style="font-family:'Clash Display',sans-serif;font-size:14px;font-weight:700;color:var(--text)">{{ $outlet->nama }}</div>
            <div style="font-size:11px;color:var(--muted);margin-top:4px">
              Dibuat: {{ $outlet->created_at->format('d M Y') }}
            </div>
          </div>
        </div>

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
                  @checked(old('is_active', $outlet->is_active))
                  style="appearance:none;width:42px;height:24px;border-radius:99px;background:var(--surface2);
                         border:1px solid var(--border);cursor:pointer;transition:background .2s"
                  onchange="syncThumb()">
                <div style="position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;
                            background:#fff;pointer-events:none;transition:transform .2s"
                  id="toggle-thumb"></div>
              </div>
            </label>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px">
          <button type="submit" class="btn btn-primary" style="justify-content:center;padding:11px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
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
  var cb    = document.getElementById('is_active');
  var thumb = document.getElementById('toggle-thumb');
  function syncThumb() {
    thumb.style.transform = cb.checked ? 'translateX(18px)' : 'translateX(0)';
    cb.style.background   = cb.checked ? 'var(--ac)' : 'var(--surface2)';
  }
  syncThumb();
  </script>
  @endpush

</x-app-layout>
