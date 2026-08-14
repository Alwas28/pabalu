<x-outlet-layout :outlet="$outlet" pageTitle="Maintenance">

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Maintenance</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Riwayat perawatan/perbaikan unit barang sewa outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong>
    </p>
  </div>
  @if($units->isNotEmpty())
  <button onclick="openModal('modal-add')"
    style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
    <i class="fa-solid fa-plus" style="font-size:12px"></i> Tambah Maintenance
  </button>
  @endif
</div>

@if($units->isEmpty())
<div class="card animate-fadeUp" style="text-align:center;padding:52px 24px">
  <div style="width:64px;height:64px;border-radius:50%;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;margin:0 auto 18px;font-size:24px">
    <i class="fa-solid fa-cubes"></i>
  </div>
  <div style="font-size:16px;font-weight:800;color:var(--text);margin-bottom:8px;font-family:'Clash Display',sans-serif">Belum Ada Unit</div>
  <div style="font-size:13px;color:var(--muted);line-height:1.7;max-width:380px;margin:0 auto">
    Tambahkan Barang Sewa dan Unit terlebih dahulu sebelum mencatat riwayat maintenance.
  </div>
  <div style="margin-top:20px">
    <a href="{{ $outlet->route('units.index') }}"
      style="padding:10px 20px;border-radius:10px;background:var(--ac);color:#fff;text-decoration:none;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:7px">
      <i class="fa-solid fa-cubes"></i> Ke Unit
    </a>
  </div>
</div>
@else

{{-- ── STATS ── --}}
@php
  $total    = $maintenances->count();
  $ongoing  = $maintenances->filter->isOngoing()->count();
  $done     = $total - $ongoing;
  $totalCost = $maintenances->sum('cost');
@endphp
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-screwdriver-wrench"></i></div>
    <div><div class="stat-num">{{ $ongoing }}</div><div class="stat-label">Sedang Berlangsung</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">{{ $done }}</div><div class="stat-label">Selesai</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-money-bill-wave"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalCost, 0, ',', '.') }}</div><div class="stat-label">Total Biaya</div></div>
  </div>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-screwdriver-wrench" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Riwayat Maintenance</span>
  </div>

  @if($maintenances->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-screwdriver-wrench" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Belum ada riwayat maintenance.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>Unit</th>
        <th>Alasan</th>
        <th>Mulai</th>
        <th>Selesai</th>
        <th style="text-align:right">Biaya</th>
        <th style="text-align:center">Status</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($maintenances as $m)
      <tr>
        <td class="td-main">
          {{ $m->rentalUnit->rentalItem->name }}
          <div style="font-size:11px;color:var(--muted);font-weight:400">{{ $m->rentalUnit->code }}</div>
        </td>
        <td>{{ $m->reason }}</td>
        <td>{{ $m->started_at->translatedFormat('d M Y') }}</td>
        <td>{{ $m->finished_at?->translatedFormat('d M Y') ?? '—' }}</td>
        <td style="text-align:right">{{ $m->cost !== null ? 'Rp '.number_format($m->cost, 0, ',', '.') : '—' }}</td>
        <td style="text-align:center">
          @if($m->isOngoing())
          <span class="badge badge-amber">Berlangsung</span>
          @else
          <span class="badge badge-green">Selesai</span>
          @endif
        </td>
        <td style="text-align:right;white-space:nowrap">
          @if($m->isOngoing())
          <form method="POST" action="{{ $outlet->route('maintenance.finish', [$m]) }}" style="display:inline;margin:0">
            @csrf @method('PATCH')
            <button type="submit" title="Tandai Selesai"
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:#34d399;font-size:12px">
              <i class="fa-solid fa-check"></i>
            </button>
          </form>
          @endif
          <button title="Edit"
            onclick='openEdit({{ $m->id }}, {{ json_encode([
              "rental_unit_id" => $m->rental_unit_id,
              "reason"         => $m->reason,
              "cost"           => $m->cost,
              "started_at"     => $m->started_at->format("Y-m-d"),
              "finished_at"    => $m->finished_at?->format("Y-m-d"),
              "notes"          => $m->notes,
            ]) }})'
            style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px;margin-left:4px">
            <i class="fa-solid fa-pen-to-square"></i>
          </button>
          <button title="Hapus" onclick="openDelete({{ $m->id }}, {{ json_encode($m->reason) }})"
            style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:#f87171;font-size:12px;margin-left:4px">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>
@endif

{{-- ══ MODAL TAMBAH ══ --}}
<div class="modal-backdrop" id="modal-add" onclick="if(event.target===this)closeModal('modal-add')">
  <div class="modal-box" style="max-width:460px">
    <form method="POST" action="{{ $outlet->route('maintenance.store') }}">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Maintenance</h3>
        <button type="button" onclick="closeModal('modal-add')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:68vh;overflow-y:auto">
        <div>
          <label class="f-label">Unit <span style="color:var(--ac)">*</span></label>
          <select name="rental_unit_id" class="f-input" required>
            <option value="">— Pilih unit —</option>
            @foreach($units->groupBy('rentalItem.name') as $itemName => $group)
            <optgroup label="{{ $itemName }}">
              @foreach($group as $u)
              <option value="{{ $u->id }}">{{ $u->code }}</option>
              @endforeach
            </optgroup>
            @endforeach
          </select>
        </div>
        <div>
          <label class="f-label">Alasan / Kerusakan <span style="color:var(--ac)">*</span></label>
          <input name="reason" class="f-input" required maxlength="255" placeholder="cth: Lensa buram, servis rutin">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Tanggal Mulai <span style="color:var(--ac)">*</span></label>
            <input type="date" name="started_at" class="f-input" required value="{{ now()->toDateString() }}">
          </div>
          <div>
            <label class="f-label">Biaya <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <input type="number" name="cost" class="f-input" min="0" placeholder="0">
          </div>
        </div>
        <div>
          <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="notes" class="f-input" rows="2" maxlength="1000"></textarea>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-add')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit" class="btn-save">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- ══ MODAL EDIT ══ --}}
<div class="modal-backdrop" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal-box" style="max-width:460px">
    <form id="form-edit" method="POST" action="">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Maintenance</h3>
        <button type="button" onclick="closeModal('modal-edit')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:68vh;overflow-y:auto">
        <div>
          <label class="f-label">Unit <span style="color:var(--ac)">*</span></label>
          <select name="rental_unit_id" id="e-unit" class="f-input" required>
            @foreach($units->groupBy('rentalItem.name') as $itemName => $group)
            <optgroup label="{{ $itemName }}">
              @foreach($group as $u)
              <option value="{{ $u->id }}">{{ $u->code }}</option>
              @endforeach
            </optgroup>
            @endforeach
          </select>
        </div>
        <div>
          <label class="f-label">Alasan / Kerusakan <span style="color:var(--ac)">*</span></label>
          <input name="reason" id="e-reason" class="f-input" required maxlength="255">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Tanggal Mulai <span style="color:var(--ac)">*</span></label>
            <input type="date" name="started_at" id="e-started" class="f-input" required>
          </div>
          <div>
            <label class="f-label">Tanggal Selesai <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <input type="date" name="finished_at" id="e-finished" class="f-input">
          </div>
        </div>
        <div>
          <label class="f-label">Biaya <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input type="number" name="cost" id="e-cost" class="f-input" min="0">
        </div>
        <div>
          <label class="f-label">Catatan</label>
          <textarea name="notes" id="e-notes" class="f-input" rows="2" maxlength="1000"></textarea>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit" class="btn-save">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

{{-- ══ MODAL HAPUS ══ --}}
<div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Hapus Riwayat?</h3>
        <p style="font-size:13.5px;color:var(--muted)">
          Riwayat <strong id="delete-name" style="color:var(--text)"></strong> akan dihapus permanen.
        </p>
      </div>
      <div style="padding:0 24px 24px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13.5px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">Hapus</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';

function openEdit(id, d) {
  document.getElementById('form-edit').action = `/${outletRp}/${outletId}/maintenance/${id}`;
  document.getElementById('e-unit').value     = d.rental_unit_id ?? '';
  document.getElementById('e-reason').value   = d.reason || '';
  document.getElementById('e-started').value  = d.started_at || '';
  document.getElementById('e-finished').value = d.finished_at || '';
  document.getElementById('e-cost').value     = d.cost ?? '';
  document.getElementById('e-notes').value    = d.notes || '';
  openModal('modal-edit');
}

function openDelete(id, name) {
  document.getElementById('form-delete').action = `/${outletRp}/${outletId}/maintenance/${id}`;
  document.getElementById('delete-name').textContent = name;
  openModal('modal-delete');
}

@if($errors->any())
  @if(old('_method') === 'PUT') openModal('modal-edit');
  @else openModal('modal-add');
  @endif
@endif
</script>
@endpush

</x-outlet-layout>
