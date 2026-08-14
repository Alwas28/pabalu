<x-outlet-layout :outlet="$outlet" pageTitle="Edit Sewa">

<div style="max-width:640px">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-size:13px;color:var(--muted)">
    <a href="{{ $outlet->route('rentals.active') }}" style="color:var(--muted);text-decoration:none">
      <i class="fa-solid fa-hourglass-half"></i> Sewa Aktif
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">Edit Sewa</span>
  </div>

  <div class="card animate-fadeUp">
    <div class="card-header">
      <span class="card-title" style="font-family:monospace">{{ $rental->order_number }}</span>
    </div>
    <form method="POST" action="{{ $outlet->route('rentals.update', [$rental]) }}">
      @csrf
      @method('PUT')
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

        <div>
          <label class="f-label">Pelanggan <span style="color:var(--ac)">*</span></label>
          <select name="customer_id" class="f-input" required>
            @foreach($customers as $c)
            <option value="{{ $c->id }}" {{ old('customer_id', $rental->customer_id) == $c->id ? 'selected' : '' }}>
              {{ $c->name }}{{ $c->phone ? ' — '.$c->phone : '' }}
            </option>
            @endforeach
          </select>
          @error('customer_id')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
        </div>

        <div>
          <label class="f-label">Barang &amp; Unit <span style="color:var(--ac)">*</span></label>
          <select name="rental_unit_id" class="f-input" required>
            @foreach($availableUnits->groupBy('rentalItem.name') as $itemName => $group)
            <optgroup label="{{ $itemName }}">
              @foreach($group as $u)
              <option value="{{ $u->id }}" {{ old('rental_unit_id', $rental->rental_unit_id) == $u->id ? 'selected' : '' }}>
                {{ $u->code }}{{ $u->condition ? ' ('.$u->condition.')' : '' }}{{ (int) $u->id === (int) $rental->rental_unit_id ? ' (unit saat ini)' : '' }}
              </option>
              @endforeach
            </optgroup>
            @endforeach
          </select>
          @error('rental_unit_id')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
        </div>

        <div>
          <label class="f-label">Jenis Sewa <span style="color:var(--ac)">*</span></label>
          <div style="display:flex;gap:8px">
            @foreach(\App\Models\RentalTransaction::TYPES as $typeCode => $typeLabel)
            <label class="req-chip" style="flex:1;text-align:center;padding:9px">
              <input type="radio" name="rental_type" value="{{ $typeCode }}" {{ old('rental_type', $rental->rental_type) === $typeCode ? 'checked' : '' }}> {{ $typeLabel }}
            </label>
            @endforeach
          </div>
          @error('rental_type')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
        </div>

        @php
          $splitDt = function (?string $val) {
              if ($val && preg_match('/^(\d{4}-\d{2}-\d{2}) (\d{2}):(\d{2})$/', $val, $m)) {
                  return [$m[1], $m[2], $m[3]];
              }
              return ['', '00', '00'];
          };
          [$startDate, $startHour, $startMinute] = $splitDt(old('start_at', $rental->start_at->format('Y-m-d H:i')));
          [$endDate, $endHour, $endMinute] = $splitDt(old('end_at', $rental->end_at->format('Y-m-d H:i')));
        @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          @include('sewa.rentals._datetime-fields', [
            'name' => 'start', 'label' => 'Mulai',
            'date' => $startDate, 'hour' => $startHour, 'minute' => $startMinute,
          ])
          <div>
            @include('sewa.rentals._datetime-fields', [
              'name' => 'end', 'label' => 'Selesai',
              'date' => $endDate, 'hour' => $endHour, 'minute' => $endMinute,
            ])
            @error('end_at')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Biaya Sewa <span style="color:var(--ac)">*</span></label>
            <div style="position:relative">
              <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
              <input type="text" id="total-display" inputmode="numeric" class="f-input" required placeholder="0"
                style="padding-left:30px" oninput="syncPrice(this,'total-raw')" onblur="formatOnBlur(this,'total-raw')">
              <input type="hidden" name="total_amount" id="total-raw" value="{{ old('total_amount', $rental->total_amount) }}">
            </div>
            @error('total_amount')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
          </div>
          <div>
            <label class="f-label">Deposit <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <div style="position:relative">
              <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
              <input type="text" id="deposit-display" inputmode="numeric" class="f-input" placeholder="0"
                style="padding-left:30px" oninput="syncPrice(this,'deposit-raw')" onblur="formatOnBlur(this,'deposit-raw')">
              <input type="hidden" name="deposit_amount" id="deposit-raw" value="{{ old('deposit_amount', $rental->deposit_amount) }}">
            </div>
            @error('deposit_amount')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
          </div>
        </div>

        <div>
          <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="notes" class="f-input" rows="2" maxlength="1000">{{ old('notes', $rental->notes) }}</textarea>
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ $outlet->route('rentals.active') }}"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
          Batal
        </a>
        <button type="submit" class="btn-save">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

</div>

@push('scripts')
<script>
function syncDatetime(name) {
  const d = document.getElementById(name + '_date').value;
  const h = document.getElementById(name + '_hour').value;
  const m = document.getElementById(name + '_minute').value;
  document.getElementById(name + '_at').value = d ? `${d} ${h}:${m}` : '';
  if (name === 'start') {
    document.getElementById('end_date').min = d;
  }
}
syncDatetime('start');
syncDatetime('end');

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

// tampilkan format Rupiah saat halaman dimuat (nilai awal dari database / old input)
(function() {
  const totalRaw = document.getElementById('total-raw')?.value;
  if (totalRaw) document.getElementById('total-display').value = toFormatted(totalRaw);
  const depositRaw = document.getElementById('deposit-raw')?.value;
  if (depositRaw) document.getElementById('deposit-display').value = toFormatted(depositRaw);
})();
</script>
@endpush

</x-outlet-layout>
