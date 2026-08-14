<x-outlet-layout :outlet="$outlet" pageTitle="Sewa Baru">

<div style="max-width:640px">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-size:13px;color:var(--muted)">
    <a href="{{ $outlet->route('rentals.active') }}" style="color:var(--muted);text-decoration:none">
      <i class="fa-solid fa-hourglass-half"></i> Sewa Aktif
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">Sewa Baru</span>
  </div>

  @if($hasRequiredDocs)
  <div class="card animate-fadeUp" style="margin-bottom:16px;padding:14px 18px;background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.3)">
    <div style="display:flex;gap:10px;align-items:flex-start">
      <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;margin-top:2px"></i>
      <div style="font-size:12.5px;color:var(--sub);line-height:1.7">
        <strong style="color:var(--text)">Dokumen wajib untuk outlet ini:</strong> {{ $requiredDocs->implode(', ') }}.
        Hanya pelanggan yang dokumennya sudah <strong style="color:var(--text)">Terverifikasi</strong> semua yang muncul di daftar pelanggan di bawah ({{ $customers->count() }} dari {{ $allCustomersCount }} pelanggan).
        Verifikasi dokumen lewat menu <a href="{{ $outlet->route('documents.index') }}" style="color:var(--ac);font-weight:600">Dokumen</a>.
      </div>
    </div>
  </div>
  @endif

  @if($customers->isEmpty())
  <div class="card animate-fadeUp" style="text-align:center;padding:40px 24px">
    <i class="fa-solid fa-users" style="font-size:24px;color:var(--muted);opacity:.5;display:block;margin-bottom:12px"></i>
    @if($hasRequiredDocs && $allCustomersCount > 0)
    <p style="font-size:13.5px;color:var(--muted);margin-bottom:16px">Belum ada pelanggan yang dokumen wajibnya sudah lengkap &amp; terverifikasi.</p>
    <a href="{{ $outlet->route('documents.index') }}"
      style="padding:9px 18px;border-radius:10px;background:var(--ac);color:#fff;text-decoration:none;font-size:13px;font-weight:700">
      <i class="fa-solid fa-id-card" style="margin-right:6px"></i>Verifikasi Dokumen
    </a>
    @else
    <p style="font-size:13.5px;color:var(--muted);margin-bottom:16px">Belum ada pelanggan terdaftar.</p>
    <a href="{{ $outlet->route('customers.create') }}"
      style="padding:9px 18px;border-radius:10px;background:var(--ac);color:#fff;text-decoration:none;font-size:13px;font-weight:700">
      <i class="fa-solid fa-user-plus" style="margin-right:6px"></i>Tambah Pelanggan
    </a>
    @endif
  </div>
  @elseif($availableUnits->isEmpty())
  <div class="card animate-fadeUp" style="text-align:center;padding:40px 24px">
    <i class="fa-solid fa-cubes" style="font-size:24px;color:var(--muted);opacity:.5;display:block;margin-bottom:12px"></i>
    <p style="font-size:13.5px;color:var(--muted);margin-bottom:16px">Tidak ada unit yang berstatus "Tersedia" saat ini.</p>
    <a href="{{ $outlet->route('units.index') }}"
      style="padding:9px 18px;border-radius:10px;background:var(--ac);color:#fff;text-decoration:none;font-size:13px;font-weight:700">
      <i class="fa-solid fa-cubes" style="margin-right:6px"></i>Ke Unit
    </a>
  </div>
  @else
  <div class="card animate-fadeUp">
    <div class="card-header">
      <span class="card-title">Data Sewa</span>
    </div>
    <form method="POST" action="{{ $outlet->route('rentals.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

        <div>
          <label class="f-label">Pelanggan <span style="color:var(--ac)">*</span></label>
          <div style="display:flex;gap:8px">
            <select name="customer_id" id="customer-select" class="f-input" required>
              <option value="">— Pilih pelanggan —</option>
              @foreach($customers as $c)
              <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                {{ $c->name }}{{ $c->phone ? ' — '.$c->phone : '' }}
              </option>
              @endforeach
            </select>
            @unless($hasRequiredDocs)
            <button type="button" onclick="toggleQuickAdd()" title="Tambah Pelanggan Cepat"
              style="flex-shrink:0;width:42px;display:flex;align-items:center;justify-content:center;border-radius:12px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);cursor:pointer">
              <i class="fa-solid fa-user-plus"></i>
            </button>
            @endunless
          </div>
          @error('customer_id')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror

          @if($hasRequiredDocs)
          <div style="font-size:11.5px;color:var(--muted);margin-top:8px">
            <i class="fa-solid fa-circle-info" style="margin-right:4px"></i>
            Pelanggan baru harus didaftarkan lewat
            <a href="{{ $outlet->route('customers.create') }}" style="color:var(--ac);font-weight:600">form lengkap</a>
            dan diverifikasi dokumennya dulu — Tambah Pelanggan Cepat dinonaktifkan karena outlet ini punya persyaratan dokumen wajib.
          </div>
          @else
          {{-- Tambah pelanggan cepat: tanpa pindah halaman --}}
          <div id="quick-add-box" style="display:none;margin-top:10px;padding:14px;border-radius:12px;border:1px dashed var(--border);background:var(--surface2)">
            <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:10px">
              <i class="fa-solid fa-bolt" style="color:var(--ac);margin-right:5px"></i>Tambah Pelanggan Cepat
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
              <input type="text" id="qa-name" class="f-input" placeholder="Nama pelanggan" maxlength="120">
              <input type="text" id="qa-phone" class="f-input" placeholder="No. HP (opsional)" maxlength="30">
            </div>
            <div id="qa-error" style="display:none;font-size:11.5px;color:#f87171;margin-top:6px"></div>
            <div style="display:flex;gap:8px;margin-top:10px">
              <button type="button" id="qa-submit" onclick="submitQuickAdd()"
                style="padding:7px 14px;border-radius:9px;border:none;background:var(--ac);color:#fff;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit">
                Simpan &amp; Pilih
              </button>
              <button type="button" onclick="toggleQuickAdd()"
                style="padding:7px 14px;border-radius:9px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit">
                Batal
              </button>
            </div>
            <div style="font-size:11px;color:var(--muted);margin-top:9px">
              Butuh data lengkap (alamat, tanggal lahir, dll)?
              <a href="{{ $outlet->route('customers.create') }}" style="color:var(--ac);font-weight:600">Buka form lengkap</a>.
            </div>
          </div>
          @endif
        </div>

        <div>
          <label class="f-label">Barang &amp; Unit <span style="color:var(--ac)">*</span></label>
          <select name="rental_unit_id" class="f-input" required>
            <option value="">— Pilih unit —</option>
            @foreach($availableUnits->groupBy('rentalItem.name') as $itemName => $group)
            <optgroup label="{{ $itemName }}">
              @foreach($group as $u)
              <option value="{{ $u->id }}" {{ old('rental_unit_id') == $u->id ? 'selected' : '' }}>
                {{ $u->code }}{{ $u->condition ? ' ('.$u->condition.')' : '' }}
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
              <input type="radio" name="rental_type" value="{{ $typeCode }}" {{ old('rental_type', 'per_hari') === $typeCode ? 'checked' : '' }}> {{ $typeLabel }}
            </label>
            @endforeach
          </div>
          @error('rental_type')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
        </div>

        @php
          $splitDt = function (?string $val, string $defaultDate = '', string $defaultHour = '00', string $defaultMinute = '00') {
              if ($val && preg_match('/^(\d{4}-\d{2}-\d{2}) (\d{2}):(\d{2})$/', $val, $m)) {
                  return [$m[1], $m[2], $m[3]];
              }
              return [$defaultDate, $defaultHour, $defaultMinute];
          };
          [$startDate, $startHour, $startMinute] = $splitDt(
              old('start_at'), now()->toDateString(), now()->format('H'), now()->format('i')
          );
          [$endDate, $endHour, $endMinute] = $splitDt(old('end_at'));
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
                style="padding-left:30px" oninput="syncPrice(this,'total-raw');updateSisa()" onblur="formatOnBlur(this,'total-raw');updateSisa()">
              <input type="hidden" name="total_amount" id="total-raw" value="{{ old('total_amount') }}">
            </div>
          </div>
          <div>
            <label class="f-label">Deposit <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <div style="position:relative">
              <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
              <input type="text" id="deposit-display" inputmode="numeric" class="f-input" placeholder="0"
                style="padding-left:30px" oninput="syncPrice(this,'deposit-raw');updatePaymentVisibility()" onblur="formatOnBlur(this,'deposit-raw');updatePaymentVisibility()">
              <input type="hidden" name="deposit_amount" id="deposit-raw" value="{{ old('deposit_amount') }}">
            </div>
          </div>
        </div>

        <div>
          <label class="f-label">Dibayar Sekarang <span style="color:var(--muted);font-weight:400">(opsional — kosongkan jika pembayaran dilakukan saat pengembalian)</span></label>
          <div style="position:relative">
            <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
            <input type="text" id="paid-display" inputmode="numeric" class="f-input" placeholder="0"
              style="padding-left:30px" oninput="syncPrice(this,'paid-raw');updateSisa();updatePaymentVisibility()" onblur="formatOnBlur(this,'paid-raw');updateSisa();updatePaymentVisibility()">
            <input type="hidden" name="paid_amount" id="paid-raw" value="{{ old('paid_amount') }}">
          </div>
          @error('paid_amount')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
          <div id="sisa-info" style="font-size:11.5px;color:var(--muted);margin-top:6px"></div>
        </div>

        <div id="payment-section" style="display:none;border-top:1px solid var(--border);padding-top:14px;flex-direction:column;gap:14px">
          <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px">
            <i class="fa-solid fa-money-bill-wave" style="margin-right:5px"></i>Pembayaran
          </div>

          <div>
            <label class="f-label">Metode Pembayaran <span style="color:var(--ac)">*</span></label>
            <select name="payment_method" id="payment-method-select" class="f-input" onchange="toggleNonCashFields()">
              @foreach($paymentMethods as $code => $label)
              <option value="{{ $code }}" {{ old('payment_method', 'cash') === $code ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('payment_method')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
          </div>

          <div id="noncash-box" style="display:none">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div>
                <label class="f-label">No. Referensi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
                <input type="text" name="payment_reference" class="f-input" maxlength="100" placeholder="cth: ID transaksi/transfer" value="{{ old('payment_reference') }}">
              </div>
              <div>
                <label class="f-label">Foto Bukti <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
                <label id="payment-photo-label" for="payment_photo"
                  style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px;border:2px dashed var(--border);border-radius:12px;padding:16px;cursor:pointer;transition:border-color .15s;min-height:84px;position:relative;overflow:hidden"
                  onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
                  <div id="payment-photo-placeholder" style="display:flex;flex-direction:column;align-items:center;gap:6px;pointer-events:none">
                    <i class="fa-solid fa-camera" style="font-size:20px;color:var(--muted)"></i>
                    <span style="font-size:11.5px;color:var(--muted)">Klik untuk unggah foto bukti</span>
                    <span style="font-size:10px;color:var(--muted);opacity:.7">JPG, PNG, WebP</span>
                  </div>
                  <img id="payment-photo-preview" src="" alt="" style="display:none;max-height:120px;max-width:100%;border-radius:8px;object-fit:contain">
                </label>
                <input type="file" id="payment_photo" name="payment_photo" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewPaymentPhoto(this)">
              </div>
            </div>
            @error('payment_photo')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
          </div>
        </div>

        <div>
          <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="notes" class="f-input" rows="2" maxlength="1000">{{ old('notes') }}</textarea>
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ $outlet->route('rentals.active') }}"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
          Batal
        </a>
        <button type="submit" class="btn-save">
          <i class="fa-solid fa-key" style="margin-right:6px"></i>Buat Sewa
        </button>
      </div>
    </form>
  </div>
  @endif

</div>

@push('scripts')
<script>
const QUICK_ADD_URL = '{{ $outlet->route('customers.quick-store') }}';
const CSRF_TOKEN    = document.querySelector('meta[name="csrf-token"]').content;

function toggleQuickAdd() {
  const box = document.getElementById('quick-add-box');
  const opening = box.style.display === 'none';
  box.style.display = opening ? 'block' : 'none';
  document.getElementById('qa-error').style.display = 'none';
  if (opening) document.getElementById('qa-name').focus();
}

async function submitQuickAdd() {
  const name  = document.getElementById('qa-name').value.trim();
  const phone = document.getElementById('qa-phone').value.trim();
  const errEl = document.getElementById('qa-error');
  const btn   = document.getElementById('qa-submit');
  errEl.style.display = 'none';

  if (!name) {
    errEl.textContent = 'Nama wajib diisi.';
    errEl.style.display = 'block';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  try {
    const res = await fetch(QUICK_ADD_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN,
      },
      body: JSON.stringify({ name, phone: phone || null }),
    });
    const data = await res.json();

    if (!res.ok) {
      errEl.textContent = data.errors?.name?.[0] || data.message || 'Gagal menambahkan pelanggan.';
      errEl.style.display = 'block';
      return;
    }

    const select = document.getElementById('customer-select');
    const opt = document.createElement('option');
    opt.value = data.id;
    opt.textContent = data.name + (data.phone ? ' — ' + data.phone : '');
    select.appendChild(opt);
    select.value = data.id;

    document.getElementById('qa-name').value = '';
    document.getElementById('qa-phone').value = '';
    toggleQuickAdd();
  } catch (e) {
    errEl.textContent = 'Gagal menambahkan pelanggan. Periksa koneksi Anda.';
    errEl.style.display = 'block';
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan & Pilih';
  }
}

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

// restore formatted display on validation error
(function() {
  const totalRaw = document.getElementById('total-raw')?.value;
  if (totalRaw) document.getElementById('total-display').value = toFormatted(totalRaw);
  const depositRaw = document.getElementById('deposit-raw')?.value;
  if (depositRaw) document.getElementById('deposit-display').value = toFormatted(depositRaw);
  const paidRaw = document.getElementById('paid-raw')?.value;
  if (paidRaw) document.getElementById('paid-display').value = toFormatted(paidRaw);
})();

function updatePaymentVisibility() {
  const deposit = parseInt(document.getElementById('deposit-raw').value || '0', 10);
  const paid    = parseInt(document.getElementById('paid-raw').value || '0', 10);
  const show    = deposit > 0 || paid > 0;
  document.getElementById('payment-section').style.display = show ? 'flex' : 'none';
  document.getElementById('payment-method-select').required = show;
}
updatePaymentVisibility();

function toggleNonCashFields() {
  const method = document.querySelector('select[name="payment_method"]').value;
  document.getElementById('noncash-box').style.display = method === 'cash' ? 'none' : 'block';
}
toggleNonCashFields();

function previewPaymentPhoto(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('payment-photo-preview').src = e.target.result;
    document.getElementById('payment-photo-preview').style.display = 'block';
    document.getElementById('payment-photo-placeholder').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

function updateSisa() {
  const info = document.getElementById('sisa-info');
  const total = parseInt(document.getElementById('total-raw')?.value || '0', 10);
  const paid  = parseInt(document.getElementById('paid-raw')?.value || '0', 10);
  if (!total) { info.textContent = ''; return; }
  const sisa = total - paid;
  info.textContent = 'Sisa yang belum dibayar: Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(sisa, 0));
}
updateSisa();
</script>
@endpush

</x-outlet-layout>
