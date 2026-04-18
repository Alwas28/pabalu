<x-app-layout title="Pengeluaran Harian">

  {{-- Flash errors --}}
  @if($errors->any())
  <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:12px 16px;font-size:13px;color:#f87171">
    <i class="fa-solid fa-circle-exclamation" style="margin-right:6px"></i>
    <strong>Terjadi kesalahan:</strong>
    <ul style="margin-top:6px;padding-left:18px">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
  @endif

  {{-- Filter --}}
  <form method="GET" action="{{ route('expenses.index') }}" id="filter-form">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end">
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Outlet</label>
        @if ($assignedOutletId ?? false)
          @php $assignedOutlet = $outlets->firstWhere('id', $assignedOutletId); @endphp
          <div class="f-input" style="display:flex;align-items:center;gap:8px;color:var(--ac);font-weight:600;pointer-events:none">
            <i class="fa-solid fa-store"></i>{{ $assignedOutlet?->nama ?? 'Outlet #'.$assignedOutletId }}
            <i class="fa-solid fa-lock" style="font-size:10px;opacity:.7;margin-left:auto"></i>
          </div>
          <input type="hidden" name="outlet_id" value="{{ $assignedOutletId }}">
        @else
          <select name="outlet_id" class="f-input" onchange="document.getElementById('filter-form').submit()">
            <option value="">— Pilih Outlet —</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        @endif
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Tanggal</label>
        <input type="date" name="tanggal" class="f-input" value="{{ $tanggal }}"
          onchange="document.getElementById('filter-form').submit()">
      </div>
      <button type="submit" class="btn" style="padding:9px 16px">
        <i class="fa-solid fa-rotate"></i> Muat
      </button>
    </div>
  </form>

  {{-- Layout: form (kiri) + list (kanan) --}}
  <div class="two-col">

    {{-- Form Tambah --}}
    <div class="card animate-fadeUp d1">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-plus-circle" style="color:var(--ac);margin-right:8px"></i>
          Tambah Pengeluaran
        </div>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('expenses.store') }}">
          @csrf
          <input type="hidden" name="outlet_id" value="{{ $outletId }}">

          <div class="f-group">
            <label class="f-label">Tanggal</label>
            <input type="date" name="tanggal" class="f-input" value="{{ old('tanggal', $tanggal) }}" required>
            @error('tanggal')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
          </div>

          <div class="f-group">
            <label class="f-label">Kategori</label>
            <select name="kategori" class="f-input" required>
              <option value="">— Pilih Kategori —</option>
              @foreach($kategoriList as $key => $label)
              <option value="{{ $key }}" @selected(old('kategori') === $key)>{{ $label }}</option>
              @endforeach
            </select>
            @error('kategori')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
          </div>

          <div class="f-group">
            <label class="f-label">Keterangan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <textarea name="keterangan" class="f-input" rows="2" placeholder="Detail pengeluaran...">{{ old('keterangan') }}</textarea>
            @error('keterangan')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
          </div>

          <div class="f-group">
            <label class="f-label">Jumlah (Rp)</label>
            <div style="position:relative">
              <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12.5px;font-weight:600">Rp</span>
              <input type="text" id="jumlah-display" class="f-input" inputmode="numeric" autocomplete="off"
                placeholder="0" required style="padding-left:34px"
                value="{{ old('jumlah') ? number_format((int)old('jumlah'), 0, ',', '.') : '' }}"
                oninput="maskRupiah(this, 'jumlah-raw')">
            </div>
            <input type="hidden" name="jumlah" id="jumlah-raw" value="{{ old('jumlah', '') }}">
            @error('jumlah')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
          </div>

          @if(!$outletId)
          <div style="text-align:center;padding:12px;font-size:13px;color:#f87171">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right:5px"></i>
            Pilih outlet terlebih dahulu.
          </div>
          @else
          <button type="submit" class="btn btn-primary" style="width:100%;padding:11px;font-size:14px;justify-content:center">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Pengeluaran
          </button>
          @endif
        </form>
      </div>
    </div>

    {{-- List Pengeluaran --}}
    <div style="display:flex;flex-direction:column;gap:16px">

      {{-- Stat: Total --}}
      <div class="stat-card animate-fadeUp d2">
        <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171">
          <i class="fa-solid fa-wallet"></i>
        </div>
        <div>
          <div class="stat-num">Rp {{ number_format($totalJumlah, 0, ',', '.') }}</div>
          <div class="stat-label">Total Pengeluaran Hari Ini</div>
        </div>
      </div>

      <div class="card animate-fadeUp d3">
        <div class="card-header">
          <div class="card-title">
            <i class="fa-solid fa-list" style="color:var(--ac);margin-right:8px"></i>
            Daftar Pengeluaran
          </div>
          <span class="badge badge-amber">{{ $expenses->count() }} entri</span>
        </div>

        @if($expenses->isEmpty())
        <div class="card-body" style="text-align:center;padding:40px">
          <i class="fa-solid fa-receipt" style="font-size:36px;color:var(--muted);margin-bottom:12px;display:block"></i>
          <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:5px">Belum ada pengeluaran</div>
          <div style="font-size:12px;color:var(--sub)">Tambahkan pengeluaran menggunakan form di sebelah kiri.</div>
        </div>
        @else
        <div>
          @foreach($expenses as $exp)
          <div style="display:grid;grid-template-columns:1fr auto;align-items:start;gap:10px;
                      padding:14px 20px;border-bottom:1px solid var(--border);transition:background .12s"
            onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
            <div>
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                <span class="badge badge-amber" style="font-size:10.5px">
                  {{ \App\Models\Expense::KATEGORI[$exp->kategori] ?? $exp->kategori }}
                </span>
                <span style="font-size:11px;color:var(--muted)">
                  {{ \Carbon\Carbon::parse($exp->tanggal)->format('d/m/Y') }}
                </span>
              </div>
              @if($exp->keterangan)
              <div style="font-size:12.5px;color:var(--sub)">{{ $exp->keterangan }}</div>
              @endif
              <div style="font-size:11px;color:var(--muted);margin-top:3px">
                <i class="fa-solid fa-user" style="margin-right:3px"></i>{{ $exp->user->name ?? '—' }}
              </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
              <div style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:#f87171;white-space:nowrap">
                Rp {{ number_format($exp->jumlah, 0, ',', '.') }}
              </div>
              <div style="display:flex;gap:6px">
                @can('expense.update')
                <button type="button" onclick="openEditModal({{ $exp->id }}, '{{ $exp->tanggal->toDateString() }}', '{{ $exp->kategori }}', {{ $exp->jumlah }}, @js($exp->keterangan))"
                  class="btn" style="padding:5px 10px;font-size:11px">
                  <i class="fa-solid fa-pen"></i>
                </button>
                @endcan
                @can('expense.delete')
                <form method="POST" action="{{ route('expenses.destroy', $exp) }}"
                  onsubmit="return confirm('Hapus pengeluaran ini?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger" style="padding:5px 10px;font-size:11px">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
                @endcan
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @endif
      </div>

    </div>
  </div>

  {{-- Edit Modal --}}
  <div id="edit-modal" class="modal-backdrop">
    <div class="modal-box">
      <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="font-family:'Clash Display',sans-serif;font-size:16px;font-weight:700;color:var(--text)">
          <i class="fa-solid fa-pen" style="color:var(--ac);margin-right:8px"></i>Edit Pengeluaran
        </div>
        <button onclick="closeModal('edit-modal')" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:12px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="edit-form" method="POST" style="padding:20px">
        @csrf @method('PUT')
        <div class="f-group">
          <label class="f-label">Tanggal</label>
          <input type="date" name="tanggal" id="edit-tanggal" class="f-input" required>
        </div>
        <div class="f-group">
          <label class="f-label">Kategori</label>
          <select name="kategori" id="edit-kategori" class="f-input" required>
            @foreach($kategoriList as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="f-group">
          <label class="f-label">Keterangan</label>
          <textarea name="keterangan" id="edit-keterangan" class="f-input" rows="2"></textarea>
        </div>
        <div class="f-group" style="margin-bottom:0">
          <label class="f-label">Jumlah (Rp)</label>
          <div style="position:relative">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12.5px;font-weight:600">Rp</span>
            <input type="text" id="edit-jumlah-display" class="f-input" inputmode="numeric" autocomplete="off"
              required style="padding-left:34px" placeholder="0"
              oninput="maskRupiah(this, 'edit-jumlah-raw')">
          </div>
          <input type="hidden" name="jumlah" id="edit-jumlah-raw">
        </div>
        <div style="display:flex;gap:10px;margin-top:20px">
          <button type="button" onclick="closeModal('edit-modal')" class="btn" style="flex:1;justify-content:center">Batal</button>
          <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  @push('scripts')
  <script>
  function maskRupiah(input, hiddenId) {
    var raw = input.value.replace(/\D/g, '');
    var num = parseInt(raw) || 0;
    input.value = num > 0 ? num.toLocaleString('id-ID') : '';
    document.getElementById(hiddenId).value = num || '';
  }

  function openEditModal(id, tanggal, kategori, jumlah, keterangan) {
    var baseUrl = '{{ url("expenses") }}';
    document.getElementById('edit-form').action          = baseUrl + '/' + id;
    document.getElementById('edit-tanggal').value        = tanggal;
    document.getElementById('edit-kategori').value       = kategori;
    document.getElementById('edit-jumlah-raw').value     = jumlah;
    document.getElementById('edit-jumlah-display').value = jumlah > 0 ? jumlah.toLocaleString('id-ID') : '';
    document.getElementById('edit-keterangan').value     = keterangan || '';
    openModal('edit-modal');
  }
  </script>
  @endpush

</x-app-layout>
