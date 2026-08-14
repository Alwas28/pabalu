<x-outlet-layout :outlet="$outlet" pageTitle="Perpanjangan">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Perpanjangan</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">Perpanjang masa sewa transaksi yang sedang berjalan</p>
</div>

{{-- ── TABLE SEWA AKTIF ── --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-rotate" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Sewa yang Bisa Diperpanjang</span>
  </div>

  @if($rentals->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-key" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Tidak ada sewa yang sedang aktif.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>No. Transaksi</th>
        <th>Pelanggan</th>
        <th>Barang / Unit</th>
        <th>Selesai Saat Ini</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rentals as $r)
      <tr>
        <td class="td-main" style="font-family:monospace;font-size:12px">{{ $r->order_number }}</td>
        <td>{{ $r->customer->name }}</td>
        <td>
          {{ $r->rentalUnit->rentalItem->name }}
          <div style="font-size:11px;color:var(--muted)">{{ $r->rentalUnit->code }}</div>
        </td>
        <td>
          {{ $r->end_at->translatedFormat('d M Y, H:i') }}
          @if($r->isOverdue())
          <span class="badge badge-red" style="margin-left:6px">Terlambat</span>
          @endif
        </td>
        <td style="text-align:right">
          <button type="button"
            onclick="openExtend({{ $r->id }}, {{ json_encode($r->order_number) }}, '{{ $r->end_at->format('Y-m-d') }}', '{{ $r->end_at->format('H') }}', '{{ $r->end_at->format('i') }}')"
            style="padding:7px 14px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px;font-weight:600">
            <i class="fa-solid fa-rotate" style="margin-right:5px"></i>Perpanjang
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>

{{-- ── RIWAYAT PERPANJANGAN ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Riwayat Perpanjangan Terbaru</span>
  </div>
  @if($history->isEmpty())
  <div style="padding:40px 24px;text-align:center">
    <p style="font-size:13px;color:var(--muted)">Belum ada riwayat perpanjangan.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>No. Transaksi</th>
        <th>Pelanggan</th>
        <th>Dari</th>
        <th>Menjadi</th>
        <th style="text-align:right">Biaya Tambahan</th>
        <th>Tanggal Perpanjangan</th>
      </tr>
    </thead>
    <tbody>
      @foreach($history as $h)
      <tr>
        <td class="td-main" style="font-family:monospace;font-size:12px">{{ $h->rentalTransaction->order_number }}</td>
        <td>{{ $h->rentalTransaction->customer->name }}</td>
        <td>{{ $h->previous_end_at->translatedFormat('d M Y, H:i') }}</td>
        <td>{{ $h->new_end_at->translatedFormat('d M Y, H:i') }}</td>
        <td style="text-align:right">{{ $h->additional_amount > 0 ? 'Rp '.number_format($h->additional_amount, 0, ',', '.') : '—' }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ $h->created_at->translatedFormat('d M Y, H:i') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>

{{-- ══ MODAL PERPANJANG ══ --}}
<div class="modal-backdrop" id="modal-extend" onclick="if(event.target===this)closeModal('modal-extend')">
  <div class="modal-box" style="max-width:440px">
    <form id="form-extend" method="POST" action="">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Perpanjang Sewa</h3>
        <button type="button" onclick="closeModal('modal-extend')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div style="font-size:13px;color:var(--sub)">Transaksi: <strong id="ext-order" style="color:var(--text)"></strong></div>
        @php
          $oldNewEnd = old('new_end_at');
          $newEndDate = $newEndHour = $newEndMinute = null;
          if ($oldNewEnd && preg_match('/^(\d{4}-\d{2}-\d{2}) (\d{2}):(\d{2})$/', $oldNewEnd, $mm)) {
              [$newEndDate, $newEndHour, $newEndMinute] = [$mm[1], $mm[2], $mm[3]];
          }
        @endphp
        @include('sewa.rentals._datetime-fields', ['name' => 'new_end', 'label' => 'Selesai Baru', 'date' => $newEndDate, 'hour' => $newEndHour, 'minute' => $newEndMinute])
        @error('new_end_at')<div class="f-err" style="font-size:12px;color:#f87171">{{ $message }}</div>@enderror
        <div>
          <label class="f-label">Biaya Tambahan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <div style="position:relative">
            <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
            <input type="text" id="ext-amount-display" inputmode="numeric" class="f-input" placeholder="0"
              style="padding-left:30px" oninput="syncPrice(this,'ext-amount-raw')" onblur="formatOnBlur(this,'ext-amount-raw')">
            <input type="hidden" name="additional_amount" id="ext-amount-raw" value="{{ old('additional_amount') }}">
          </div>
          @error('additional_amount')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
        </div>
        <div>
          <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="notes" class="f-input" rows="2" maxlength="1000"></textarea>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-extend')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit" class="btn-save">Perpanjang</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';

function syncDatetime(name) {
  const d = document.getElementById(name + '_date').value;
  const h = document.getElementById(name + '_hour').value;
  const m = document.getElementById(name + '_minute').value;
  document.getElementById(name + '_at').value = d ? `${d} ${h}:${m}` : '';
}

function openExtend(id, orderNumber, currentDate, currentHour, currentMinute) {
  document.getElementById('form-extend').action = `/${outletRp}/${outletId}/rentals/${id}/extend`;
  document.getElementById('ext-order').textContent = orderNumber;
  document.getElementById('new_end_date').min = currentDate;
  document.getElementById('new_end_date').value = currentDate;
  document.getElementById('new_end_hour').value = currentHour;
  document.getElementById('new_end_minute').value = currentMinute;
  document.getElementById('ext-amount-display').value = '';
  document.getElementById('ext-amount-raw').value = '';
  syncDatetime('new_end');
  openModal('modal-extend');
}

function toFormatted(raw) {
  if (!raw) return '';
  return new Intl.NumberFormat('id-ID').format(parseInt(raw, 10));
}
function syncPrice(displayEl, rawId) {
  const before = displayEl.value.substring(0, displayEl.selectionStart);
  const digitsBeforeCursor = (before.match(/\d/g) || []).length;
  const raw = displayEl.value.replace(/\D/g, '');
  document.getElementById(rawId).value = raw;
  const formatted = raw ? toFormatted(raw) : '';
  displayEl.value = formatted;
  let seen = 0, newPos = formatted.length;
  for (let i = 0; i < formatted.length; i++) {
    if (/\d/.test(formatted[i])) seen++;
    if (seen === digitsBeforeCursor) { newPos = i + 1; break; }
  }
  try { displayEl.setSelectionRange(newPos, newPos); } catch (_) {}
}
function formatOnBlur(displayEl, rawId) {
  const raw = displayEl.value.replace(/\D/g, '');
  document.getElementById(rawId).value = raw;
  displayEl.value = raw ? toFormatted(raw) : '';
}

syncDatetime('new_end');

// restore formatted display on validation error
(function() {
  const amountRaw = document.getElementById('ext-amount-raw')?.value;
  if (amountRaw) document.getElementById('ext-amount-display').value = toFormatted(amountRaw);
})();

@if($errors->any())
  openModal('modal-extend');
@endif
</script>
@endpush

</x-outlet-layout>
