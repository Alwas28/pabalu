<x-outlet-layout :outlet="$outlet" pageTitle="Opening Shift">

@php
  $fmt      = fn(int $v) => 'Rp ' . number_format($v, 0, ',', '.');
  $user     = Auth::user();
  $p        = fn(string $s) => $user->hasPermission($s);
  $canClose = $activeShift && (
    $activeShift->user_id === $user->id
    || $user->hasPermission('shift.manage')
  );
  $canManage = $p('shift.manage');
  $isManager = in_array($user->role, ['admin', 'owner', 'admin_outlet']);
@endphp

{{-- ── KASIR SEDANG BERTUGAS (hanya terlihat admin/owner/admin_outlet) ── --}}
@if($isManager && $allActiveShifts && $allActiveShifts->isNotEmpty())
<div class="card animate-fadeUp" style="margin-bottom:20px;border-color:rgba(99,102,241,.3);background:rgba(99,102,241,.03)">
  <div class="card-header" style="border-color:rgba(99,102,241,.15)">
    <div style="display:flex;align-items:center;gap:8px">
      <i class="fa-solid fa-users" style="color:#818cf8;font-size:14px"></i>
      <div class="card-title" style="color:#818cf8">Kasir Sedang Bertugas</div>
    </div>
    <span style="font-size:12px;padding:3px 10px;border-radius:99px;background:rgba(99,102,241,.15);color:#818cf8;font-weight:600">
      {{ $allActiveShifts->count() }} aktif
    </span>
  </div>
  <div style="padding:0 6px 6px">
    <div style="display:flex;flex-wrap:wrap;gap:8px;padding:10px 10px 4px">
      @foreach($allActiveShifts as $as)
      <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:12px;background:var(--surface2);border:1px solid var(--border);min-width:200px">
        <div style="width:8px;height:8px;border-radius:50%;background:#34d399;flex-shrink:0;box-shadow:0 0 0 2px rgba(52,211,153,.25)"></div>
        <div>
          <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $as->openedBy->name }}</div>
          <div style="font-size:11px;color:var(--muted)">
            Buka {{ $as->opened_at->format('H:i') }}
            &nbsp;·&nbsp;
            Kas Awal {{ $fmt((int)$as->opening_cash) }}
            &nbsp;·&nbsp;
            {{ $as->duration() }}
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endif

{{-- ── STATUS SHIFT AKTIF MILIK SAYA ── --}}
@if($activeShift)
{{-- SHIFT SEDANG BERJALAN --}}
<div class="card animate-fadeUp" style="margin-bottom:20px;border-color:rgba(16,185,129,.4);background:rgba(16,185,129,.04)">
  <div class="card-header" style="border-color:rgba(16,185,129,.2)">
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:10px;height:10px;border-radius:50%;background:#34d399;box-shadow:0 0 0 3px rgba(52,211,153,.3);animation:pulse 2s infinite"></div>
      <div class="card-title" style="color:#34d399">Shift Saya Sedang Berjalan</div>
    </div>
    <span style="font-size:12px;padding:4px 12px;border-radius:99px;background:rgba(16,185,129,.15);color:#34d399;font-weight:600">
      <i class="fa-solid fa-clock" style="margin-right:4px"></i>{{ $activeShift->duration() }}
    </span>
  </div>
  <div class="card-body">

    {{-- Info shift --}}
    <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid rgba(16,185,129,.15)">
      <div>
        <div style="font-size:11px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:var(--muted)">Jam Buka</div>
        <div style="font-size:14px;font-weight:700;color:var(--text);margin-top:2px">{{ $activeShift->opened_at->format('d M Y, H:i') }}</div>
      </div>
      <div>
        <div style="font-size:11px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:var(--muted)">Kas Awal</div>
        <div style="font-size:14px;font-weight:700;color:var(--text);margin-top:2px">{{ $fmt((int)$activeShift->opening_cash) }}</div>
      </div>
      @if($activeShift->notes)
      <div>
        <div style="font-size:11px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:var(--muted)">Catatan</div>
        <div style="font-size:13px;color:var(--sub);margin-top:2px">{{ $activeShift->notes }}</div>
      </div>
      @endif
    </div>

    {{-- Stats sejak shift dibuka (per-kasir) --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
      <div style="padding:12px 14px;border-radius:12px;background:var(--surface2);text-align:center">
        <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.6px">Transaksi</div>
        <div style="font-size:22px;font-weight:800;color:var(--text);margin:4px 0">{{ $stats['total_transactions'] }}</div>
        <div style="font-size:11px;color:var(--muted)">oleh saya</div>
      </div>
      <div style="padding:12px 14px;border-radius:12px;background:var(--surface2);text-align:center">
        <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.6px">Pendapatan</div>
        <div style="font-size:18px;font-weight:800;color:var(--ac);margin:4px 0">{{ $fmt($stats['total_revenue']) }}</div>
        <div style="font-size:11px;color:var(--muted)">semua metode</div>
      </div>
      <div style="padding:12px 14px;border-radius:12px;background:var(--surface2);text-align:center">
        <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.6px">Tunai Masuk</div>
        <div style="font-size:18px;font-weight:800;color:#34d399;margin:4px 0">{{ $fmt($stats['cash_in']) }}</div>
        <div style="font-size:11px;color:var(--muted)">transaksi tunai</div>
      </div>
      <div style="padding:12px 14px;border-radius:12px;background:var(--surface2);text-align:center">
        <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.6px">Pengeluaran</div>
        <div style="font-size:18px;font-weight:800;color:#f87171;margin:4px 0">{{ $fmt($stats['total_expense']) }}</div>
        <div style="font-size:11px;color:var(--muted)">dicatat saya</div>
      </div>
    </div>

    {{-- Kalkulasi ekspektasi --}}
    <div style="padding:14px 16px;border-radius:12px;background:rgba(var(--ac-rgb),.07);border:1px solid rgba(var(--ac-rgb),.2);margin-bottom:20px;display:flex;gap:24px;flex-wrap:wrap;align-items:center">
      <div style="font-size:12.5px;color:var(--muted)">
        <i class="fa-solid fa-calculator" style="margin-right:6px;color:var(--ac)"></i>
        Ekspektasi Kas Akhir
      </div>
      <div style="font-size:13px;color:var(--sub)">
        Kas Awal <strong style="color:var(--text)">{{ $fmt((int)$activeShift->opening_cash) }}</strong>
        + Tunai Masuk <strong style="color:#34d399">{{ $fmt($stats['cash_in']) }}</strong>
        − Pengeluaran <strong style="color:#f87171">{{ $fmt($stats['total_expense']) }}</strong>
        <span style="margin:0 6px;color:var(--border)">=</span>
        <strong style="font-size:15px;color:var(--ac)">{{ $fmt($stats['expected_cash']) }}</strong>
      </div>
    </div>

    {{-- Form tutup shift --}}
    @if($canClose)
    <div style="border-top:1px solid var(--border);padding-top:20px">
      <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px">
        <i class="fa-solid fa-door-closed" style="margin-right:8px;color:var(--ac)"></i>Tutup Shift
      </div>
      <form method="POST" action="{{ $outlet->route('shifts.close', [$activeShift]) }}">
        @csrf
        @method('PATCH')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;max-width:560px">
          <div>
            <label class="f-label">Kas Akhir Aktual <span style="color:#f87171">*</span></label>
            <input id="closing-cash" name="closing_cash" type="number" class="f-input"
              placeholder="0" min="0" required
              oninput="updateSelisih(this.value)"
              style="font-size:16px;font-weight:700">
          </div>
          <div>
            <label class="f-label">Selisih Kas</label>
            <div id="selisih-box" style="height:40px;display:flex;align-items:center;padding:0 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);font-size:15px;font-weight:700;color:var(--muted)">
              —
            </div>
          </div>
        </div>
        <div style="margin-top:12px;max-width:560px">
          <label class="f-label">Catatan</label>
          <input name="notes" type="text" class="f-input" placeholder="Catatan opsional untuk shift ini"
            value="{{ old('notes', $activeShift->notes) }}">
        </div>
        <div style="margin-top:16px">
          <button type="submit"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
            <i class="fa-solid fa-door-closed"></i>Tutup Shift Sekarang
          </button>
        </div>
      </form>
    </div>
    @endif

  </div>
</div>

@else
{{-- TIDAK ADA SHIFT AKTIF MILIK SAYA --}}
<div class="two-col animate-fadeUp" style="margin-bottom:20px;align-items:start">

  {{-- Empty state --}}
  <div class="card">
    <div class="card-body" style="text-align:center;padding:40px 24px">
      <div style="width:64px;height:64px;border-radius:18px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 14px;font-size:26px;color:var(--muted)">
        <i class="fa-solid fa-clock"></i>
      </div>
      <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px">Tidak Ada Shift Aktif</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.6">
        Buka shift terlebih dahulu sebelum mulai beroperasi.<br>
        Input jumlah uang kas yang ada di laci kasir.
      </p>
    </div>
  </div>

  {{-- Form buka shift --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-door-open a-text" style="margin-right:8px"></i>Buka Shift Baru</div>
    </div>
    <div class="card-body">

      @if(!$hasProducts)
      {{-- Peringatan tidak ada produk --}}
      <div style="display:flex;gap:10px;padding:12px 14px;border-radius:12px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);margin-bottom:16px">
        <i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;margin-top:1px;flex-shrink:0"></i>
        <div>
          <div style="font-size:13px;font-weight:700;color:#f59e0b;margin-bottom:3px">Produk belum tersedia</div>
          <div style="font-size:12px;color:var(--muted);line-height:1.5">
            Outlet ini belum memiliki produk aktif. Tambahkan produk terlebih dahulu sebelum membuka shift.
          </div>
          <a href="{{ $outlet->route('products.index') }}"
            style="display:inline-flex;align-items:center;gap:5px;margin-top:8px;font-size:12px;font-weight:600;color:#f59e0b;text-decoration:none">
            <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>Kelola Produk
          </a>
        </div>
      </div>
      @endif

      <form method="POST" action="{{ $outlet->route('shifts.store') }}">
        @csrf
        <div style="display:flex;flex-direction:column;gap:14px">
          <div>
            <label class="f-label">Kas Awal <span style="color:#f87171">*</span></label>
            <input name="opening_cash" type="number" class="f-input" placeholder="0" min="0"
              required {{ !$hasProducts ? 'disabled' : '' }}
              style="font-size:18px;font-weight:700" value="{{ old('opening_cash', 0) }}">
            <div style="font-size:11.5px;color:var(--muted);margin-top:5px">
              <i class="fa-solid fa-circle-info" style="margin-right:4px"></i>
              Hitung uang yang ada di laci kasir saat ini.
            </div>
          </div>
          <div>
            <label class="f-label">Catatan</label>
            <input name="notes" type="text" class="f-input" placeholder="Catatan opsional..."
              {{ !$hasProducts ? 'disabled' : '' }}
              value="{{ old('notes') }}">
          </div>
          <button type="submit" {{ !$hasProducts ? 'disabled' : '' }}
            style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;border-radius:11px;border:none;background:{{ $hasProducts ? 'linear-gradient(135deg,var(--ac),var(--ac2))' : 'var(--surface2)' }};color:{{ $hasProducts ? '#fff' : 'var(--muted)' }};font-size:14px;font-weight:700;cursor:{{ $hasProducts ? 'pointer' : 'not-allowed' }};font-family:inherit;box-shadow:{{ $hasProducts ? '0 2px 12px rgba(var(--ac-rgb),.35)' : 'none' }}">
            <i class="fa-solid fa-door-open"></i>Buka Shift
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endif

{{-- ── RIWAYAT SHIFT ── --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-clock-rotate-left a-text" style="margin-right:8px"></i>Riwayat Shift</div>
  </div>

  @if($shifts->isEmpty())
  <div style="padding:40px 24px;text-align:center">
    <div style="font-size:13px;color:var(--muted)">Belum ada riwayat shift.</div>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Kasir</th>
          <th>Jam Buka</th>
          <th>Jam Tutup</th>
          <th>Durasi</th>
          <th style="text-align:right">Kas Awal</th>
          <th style="text-align:right">Tunai Masuk</th>
          <th style="text-align:right">Pengeluaran</th>
          <th style="text-align:right">Ekspektasi</th>
          <th style="text-align:right">Kas Akhir</th>
          <th style="text-align:right">Selisih</th>
          <th>Status</th>
          @if($canManage)<th style="text-align:right">Aksi</th>@endif
        </tr>
      </thead>
      <tbody>
        @foreach($shifts as $shift)
        @php $sel = $shift->selisih(); @endphp
        <tr>
          <td>
            <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $shift->openedBy->name }}</div>
            @if($shift->closedBy && $shift->closedBy->id !== $shift->openedBy->id)
            <div style="font-size:11px;color:var(--muted)">Ditutup: {{ $shift->closedBy->name }}</div>
            @endif
          </td>
          <td style="font-size:12.5px;white-space:nowrap">{{ $shift->opened_at->format('d M, H:i') }}</td>
          <td style="font-size:12.5px;white-space:nowrap">
            {{ $shift->closed_at ? $shift->closed_at->format('d M, H:i') : '—' }}
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $shift->duration() }}</td>
          <td style="text-align:right;font-size:12.5px">{{ $fmt((int)$shift->opening_cash) }}</td>
          <td style="text-align:right;font-size:12.5px;color:#34d399">
            {{ $shift->cash_in !== null ? $fmt((int)$shift->cash_in) : '—' }}
          </td>
          <td style="text-align:right;font-size:12.5px;color:#f87171">
            {{ $shift->total_expense !== null ? $fmt((int)$shift->total_expense) : '—' }}
          </td>
          <td style="text-align:right;font-size:12.5px;font-weight:600;color:var(--ac)">
            {{ $shift->expected_cash !== null ? $fmt((int)$shift->expected_cash) : '—' }}
          </td>
          <td style="text-align:right;font-size:12.5px;font-weight:600;color:var(--text)">
            {{ $shift->closing_cash !== null ? $fmt((int)$shift->closing_cash) : '—' }}
          </td>
          <td style="text-align:right;white-space:nowrap">
            @if($sel === null)
              <span style="color:var(--muted);font-size:12px">—</span>
            @elseif($sel === 0)
              <span class="badge badge-green"><i class="fa-solid fa-check" style="font-size:9px"></i>Sesuai</span>
            @elseif($sel > 0)
              <span class="badge badge-blue" style="font-size:11px">+{{ $fmt($sel) }}</span>
            @else
              <span class="badge badge-red" style="font-size:11px">{{ $fmt($sel) }}</span>
            @endif
          </td>
          <td>
            @if($shift->isActive())
            <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:7px"></i>Aktif</span>
            @else
            <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:7px"></i>Selesai</span>
            @endif
          </td>
          @if($canManage)
          <td>
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:5px">
              <button
                data-id="{{ $shift->id }}"
                data-opening-cash="{{ $shift->opening_cash }}"
                data-notes="{{ $shift->notes }}"
                data-active="{{ $shift->isActive() ? '1' : '0' }}"
                onclick="openEditShift(this)"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:12px;color:var(--sub)"
                onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'" title="Edit">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button
                data-id="{{ $shift->id }}"
                data-label="{{ $shift->opened_at->format('d M Y H:i') }} — {{ $shift->openedBy->name }}"
                onclick="openDeleteShift(this)"
                style="width:30px;height:30px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);cursor:pointer;font-size:12px;color:#f87171"
                onmouseover="this.style.background='rgba(239,68,68,.18)'" onmouseout="this.style.background='rgba(239,68,68,.08)'" title="Hapus">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </td>
          @endif
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($shifts->hasPages())
  <div style="padding:12px 16px;border-top:1px solid var(--border)">
    {{ $shifts->links() }}
  </div>
  @endif
  @endif
</div>

{{-- ── Modal Edit Shift ── --}}
@if($canManage)
<div class="modal-backdrop" id="modal-edit-shift" onclick="if(event.target===this)closeModal('modal-edit-shift')">
  <div class="modal-box" style="max-width:440px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Data Shift</div>
      <button onclick="closeModal('modal-edit-shift')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:14px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" id="edit-shift-form" action="">
      @csrf
      @method('PATCH')
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
        <div id="ed-opening-cash-wrap">
          <label class="f-label">Kas Awal <span style="color:#f87171">*</span></label>
          <input id="ed-opening-cash" name="opening_cash" type="number" class="f-input" min="0" placeholder="0">
          <div style="font-size:11px;color:var(--muted);margin-top:4px">Hanya bisa diubah saat shift masih aktif.</div>
        </div>
        <div>
          <label class="f-label">Catatan</label>
          <input id="ed-notes" name="notes" type="text" class="f-input" placeholder="Catatan opsional" maxlength="500">
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit-shift')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── Modal Hapus Shift ── --}}
<div class="modal-backdrop" id="modal-delete-shift" onclick="if(event.target===this)closeModal('modal-delete-shift')">
  <div class="modal-box" style="max-width:380px">
    <div style="padding:24px 24px 20px;text-align:center">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.12);color:#f87171;display:grid;place-items:center;font-size:22px;margin:0 auto 14px">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Data Shift?</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.65">
        Shift <strong id="del-shift-label" style="color:var(--text)"></strong> akan dihapus permanen.<br>
        Tindakan ini tidak dapat dibatalkan.
      </p>
    </div>
    <form method="POST" id="delete-shift-form" action="">
      @csrf
      @method('DELETE')
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete-shift')"
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
const expectedCash = {{ $stats ? $stats['expected_cash'] : 0 }};

function updateSelisih(val) {
  const box     = document.getElementById('selisih-box');
  if (!box) return;
  const closing = parseInt(val) || 0;
  const sel     = closing - expectedCash;
  const abs     = Math.abs(sel);
  const rupiah  = 'Rp ' + abs.toLocaleString('id-ID');

  if (val === '' || val === null) {
    box.textContent = '—';
    box.style.color = 'var(--muted)';
    box.style.background = 'var(--surface2)';
    box.style.borderColor = 'var(--border)';
  } else if (sel === 0) {
    box.textContent = 'Sesuai ✓';
    box.style.color = '#34d399';
    box.style.background = 'rgba(16,185,129,.08)';
    box.style.borderColor = 'rgba(16,185,129,.3)';
  } else if (sel > 0) {
    box.textContent = '+' + rupiah;
    box.style.color = '#818cf8';
    box.style.background = 'rgba(99,102,241,.08)';
    box.style.borderColor = 'rgba(99,102,241,.3)';
  } else {
    box.textContent = '−' + rupiah;
    box.style.color = '#f87171';
    box.style.background = 'rgba(239,68,68,.08)';
    box.style.borderColor = 'rgba(239,68,68,.3)';
  }
}

function openEditShift(btn) {
  const d      = btn.dataset;
  const base   = '{{ $outlet->route('shifts.index') }}';
  document.getElementById('edit-shift-form').action = base.replace('/shifts', '/shifts/' + d.id);

  document.getElementById('ed-opening-cash').value = d.openingCash || 0;
  document.getElementById('ed-notes').value         = d.notes || '';

  const wrap = document.getElementById('ed-opening-cash-wrap');
  if (wrap) wrap.style.display = d.active === '1' ? '' : 'none';

  openModal('modal-edit-shift');
}

function openDeleteShift(btn) {
  const d    = btn.dataset;
  const base = '{{ $outlet->route('shifts.index') }}';
  document.getElementById('delete-shift-form').action = base.replace('/shifts', '/shifts/' + d.id);
  document.getElementById('del-shift-label').textContent = d.label;
  openModal('modal-delete-shift');
}
</script>
<style>
@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 3px rgba(52,211,153,.3); }
  50%       { box-shadow: 0 0 0 6px rgba(52,211,153,.1); }
}
</style>
@endpush

</x-outlet-layout>
