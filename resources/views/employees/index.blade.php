<x-app-layout>
<x-slot name="header">Kelola Karyawan</x-slot>

<div style="max-width:1100px;margin:0 auto;display:flex;flex-direction:column;gap:20px">

  {{-- Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
      <h1 class="font-display" style="font-size:22px;font-weight:700;color:var(--text)">Kelola Karyawan</h1>
      <p style="font-size:13px;color:var(--muted);margin-top:2px">Data identitas karyawan dan riwayat penggajian</p>
    </div>
    <button onclick="document.getElementById('modal-create').classList.add('open')"
      style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
      <i class="fa-solid fa-plus" style="font-size:11px"></i> Tambah Karyawan
    </button>
  </div>

  {{-- Tabel --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden">

    @if($employees->isEmpty())
    <div style="padding:60px;text-align:center">
      <i class="fa-solid fa-users" style="font-size:36px;color:var(--muted);opacity:.3;display:block;margin-bottom:14px"></i>
      <h3 class="font-display" style="font-size:16px;color:var(--text);margin-bottom:6px">Belum Ada Karyawan</h3>
      <p style="font-size:13px;color:var(--muted)">Klik "Tambah Karyawan" untuk mendaftarkan karyawan baru.</p>
    </div>
    @else
    <table class="tbl">
      <thead>
        <tr>
          <th style="text-align:left">Karyawan</th>
          <th style="text-align:left">Jabatan</th>
          <th style="text-align:left">Outlet</th>
          <th style="text-align:left">WhatsApp</th>
          <th style="text-align:left">Tgl Masuk</th>
          <th style="text-align:center">Akun</th>
          <th style="text-align:center">Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($employees as $emp)
        <tr>
          <td class="td-main">
            <a href="{{ route('employees.show', $emp) }}"
              style="display:flex;align-items:center;gap:10px;text-decoration:none">
              @if($emp->photo)
                <img src="{{ asset('storage/'.$emp->photo) }}" alt="{{ $emp->name }}"
                  style="width:36px;height:36px;border-radius:10px;object-fit:cover;flex-shrink:0">
              @else
                <div style="width:36px;height:36px;border-radius:10px;background:var(--ac-lt);display:grid;place-items:center;font-size:14px;font-weight:700;color:var(--ac);flex-shrink:0">
                  {{ strtoupper(substr($emp->name, 0, 1)) }}
                </div>
              @endif
              <span style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $emp->name }}</span>
            </a>
          </td>
          <td>{{ $emp->position ?: '—' }}</td>
          <td>
            @if($emp->outlet)
              <span style="display:inline-flex;align-items:center;gap:4px;font-size:12.5px;color:var(--sub);font-weight:500">
                <i class="fa-solid fa-store" style="font-size:10px;color:var(--ac)"></i>
                {{ $emp->outlet->name }}
              </span>
            @else
              <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
          <td>
            @if($emp->whatsapp)
              <a href="https://wa.me/{{ preg_replace('/\D/', '', $emp->whatsapp) }}" target="_blank"
                style="color:#34d399;text-decoration:none;font-size:13px;font-weight:500">
                <i class="fa-brands fa-whatsapp" style="margin-right:4px"></i>{{ $emp->whatsapp }}
              </a>
            @else
              —
            @endif
          </td>
          <td>{{ $emp->join_date ? $emp->join_date->format('d/m/Y') : '—' }}</td>
          <td style="text-align:center">
            @if($emp->user_id)
              <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;background:rgba(var(--ac-rgb),.12);color:var(--ac);font-size:11px;font-weight:700">
                <i class="fa-solid fa-circle-check" style="font-size:9px"></i> Ada
              </span>
            @else
              <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
          <td style="text-align:center">
            <form method="POST" action="{{ route('employees.toggle-active', $emp) }}" style="display:inline">
              @csrf
              <button type="submit"
                style="display:inline-flex;align-items:center;gap:4px;padding:3px 11px;border-radius:99px;border:none;cursor:pointer;font-size:11.5px;font-weight:700;
                  background:{{ $emp->is_active ? 'rgba(52,211,153,.12)' : 'rgba(239,68,68,.1)' }};
                  color:{{ $emp->is_active ? '#34d399' : '#f87171' }}"
                title="{{ $emp->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                <i class="fa-solid fa-circle" style="font-size:6px"></i>
                {{ $emp->is_active ? 'Aktif' : 'Nonaktif' }}
              </button>
            </form>
          </td>
          <td style="text-align:right">
            <div style="display:flex;align-items:center;gap:6px;justify-content:flex-end">
              <a href="{{ route('employees.show', $emp) }}"
                style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px"
                onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                <i class="fa-solid fa-eye" style="font-size:10px"></i> Detail
              </a>
              <form method="POST" action="{{ route('employees.destroy', $emp) }}" style="display:inline"
                onsubmit="return confirm('Hapus karyawan {{ addslashes($emp->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                  style="padding:6px 10px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:transparent;color:#f87171;font-size:12px;cursor:pointer"
                  onmouseover="this.style.background='rgba(239,68,68,.08)'" onmouseout="this.style.background='transparent'">
                  <i class="fa-solid fa-trash" style="font-size:10px"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

  </div>

</div>

{{-- ════ MODAL CREATE ════ --}}
<div id="modal-create" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:560px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-user-plus" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tambah Karyawan
      </h3>
      <button onclick="document.getElementById('modal-create').classList.remove('open')"
        style="width:28px;height:28px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div style="grid-column:1/-1">
            <label class="f-label">Nama Lengkap <span style="color:#f87171">*</span></label>
            <input name="name" type="text" required class="f-input" placeholder="Nama lengkap karyawan" value="{{ old('name') }}">
          </div>
          <div style="grid-column:1/-1">
            <label class="f-label">
              Outlet Tempat Bertugas
              <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--muted);margin-left:4px">— tempat keluarnya gaji karyawan ini</span>
            </label>
            <select name="outlet_id" class="f-input">
              <option value="">— Pilih Outlet —</option>
              @foreach($outlets as $ol)
              <option value="{{ $ol->id }}" {{ old('outlet_id') == $ol->id ? 'selected' : '' }}>
                {{ $ol->name }}
              </option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="f-label">Jabatan / Posisi</label>
            <input name="position" type="text" class="f-input" placeholder="cth: Kasir, Waiter" value="{{ old('position') }}">
          </div>
          <div>
            <label class="f-label">No. WhatsApp</label>
            <input name="whatsapp" type="text" class="f-input" placeholder="08xxxxxxxxxx" value="{{ old('whatsapp') }}">
          </div>
          <div>
            <label class="f-label">Pendidikan Terakhir</label>
            <select name="education_level" class="f-input">
              <option value="">-- Pilih --</option>
              @foreach(['SD','SMP','SMA','D3','S1','S2','S3'] as $edu)
              <option value="{{ $edu }}" {{ old('education_level') === $edu ? 'selected' : '' }}>{{ $edu }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="f-label">Tanggal Masuk</label>
            <input name="join_date" type="date" class="f-input" value="{{ old('join_date') }}">
          </div>
          <div style="grid-column:1/-1">
            <label class="f-label">Alamat</label>
            <textarea name="address" class="f-input" rows="2" placeholder="Alamat tinggal karyawan" style="resize:vertical">{{ old('address') }}</textarea>
          </div>
          <div style="grid-column:1/-1">
            <label class="f-label">Catatan</label>
            <textarea name="notes" class="f-input" rows="2" placeholder="Catatan tambahan..." style="resize:vertical">{{ old('notes') }}</textarea>
          </div>
          <div style="grid-column:1/-1">
            <label class="f-label">Foto Karyawan</label>
            <input name="photo" type="file" accept="image/*" class="f-input" style="padding:6px">
            <p style="font-size:11px;color:var(--muted);margin-top:4px">Maks. 2 MB, format JPG/PNG</p>
          </div>
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
        <button type="button" onclick="document.getElementById('modal-create').classList.remove('open')"
          style="flex:0 0 auto;padding:10px 18px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
          <i class="fa-solid fa-user-plus" style="margin-right:6px;font-size:12px"></i>Simpan Karyawan
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
@if($errors->any())
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('modal-create').classList.add('open');
});
@endif
</script>
@endpush

</x-app-layout>
