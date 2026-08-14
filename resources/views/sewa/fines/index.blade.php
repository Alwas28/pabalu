<x-outlet-layout :outlet="$outlet" pageTitle="Denda">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Denda</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">Denda keterlambatan atau kerusakan barang pada outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong></p>
</div>

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalFines, 0, ',', '.') }}</div><div class="stat-label">Total Denda Tercatat</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div><div class="stat-label">Sudah Dibayar</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-hourglass-half"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalRemaining, 0, ',', '.') }}</div><div class="stat-label">Belum Dibayar</div></div>
  </div>
</div>

{{-- ── FILTER TANGGAL ── --}}
<div class="card animate-fadeUp d2" style="padding:14px 18px">
  <form method="GET" action="{{ $outlet->route('fines.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <label class="f-label">Dari Tanggal</label>
      <input type="date" name="from" class="f-input" value="{{ $from }}" max="{{ now()->toDateString() }}">
    </div>
    <div>
      <label class="f-label">Sampai Tanggal</label>
      <input type="date" name="to" class="f-input" value="{{ $to }}" max="{{ now()->toDateString() }}">
    </div>
    <button type="submit" class="btn-save" style="padding:9px 18px">
      <i class="fa-solid fa-filter" style="margin-right:6px"></i>Terapkan
    </button>
    @if($from || $to)
    <a href="{{ $outlet->route('fines.index') }}"
      style="padding:9px 16px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center">
      Reset
    </a>
    @endif
  </form>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Daftar Denda</span>
  </div>

  @if($rentals->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-triangle-exclamation" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Belum ada denda tercatat.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>No. Transaksi</th>
        <th>Pelanggan</th>
        <th>Barang / Unit</th>
        <th style="text-align:right">Total Denda</th>
        <th style="text-align:right">Sudah Dibayar</th>
        <th style="text-align:right">Sisa</th>
        <th style="text-align:center">Status</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rentals as $r)
      @php
        $paid      = $r->finePaid();
        $remaining = $r->fineRemaining();
        $tanggal   = $r->returned_at ?? $r->updated_at;
      @endphp
      <tr>
        <td style="font-size:12.5px;color:var(--sub)">{{ $tanggal->translatedFormat('d M Y, H:i') }}</td>
        <td class="td-main" style="font-family:monospace;font-size:12px">{{ $r->order_number }}</td>
        <td>
          <a href="{{ $outlet->route('customers.show', [$r->customer]) }}" style="color:var(--ac);font-weight:600;text-decoration:none">
            {{ $r->customer->name }}
          </a>
        </td>
        <td>
          {{ $r->rentalUnit->rentalItem->name }}
          <div style="font-size:11px;color:var(--muted)">{{ $r->rentalUnit->code }}</div>
        </td>
        <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($r->fine_amount, 0, ',', '.') }}</td>
        <td style="text-align:right;color:#34d399">Rp {{ number_format($paid, 0, ',', '.') }}</td>
        <td style="text-align:right;color:{{ $remaining > 0 ? '#f87171' : 'var(--muted)' }}">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
        <td style="text-align:center">
          @if($remaining <= 0)
          <span class="badge badge-green">Lunas</span>
          @elseif($paid > 0)
          <span class="badge badge-amber">Sebagian</span>
          @else
          <span class="badge badge-red">Belum Dibayar</span>
          @endif
        </td>
        <td style="text-align:right;white-space:nowrap">
          <a href="{{ $outlet->route('rentals.show', [$r]) }}"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;text-decoration:none;display:inline-block">
            Detail
          </a>
          @if($remaining > 0)
          <button type="button" onclick="openFinePay({{ $r->id }}, {{ json_encode($r->order_number) }}, {{ $remaining }}, {{ $r->depositAvailable() }})"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px;font-weight:600;margin-left:4px">
            Bayar Denda
          </button>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <div style="padding:16px 20px">{{ $rentals->links() }}</div>
  @endif
</div>

{{-- ══ MODAL BAYAR DENDA ══ --}}
@php
  $payButtons = [['cash', 'Tunai', 'fa-money-bill-wave']];
  $pmIcons    = ['qris_transfer' => 'fa-qrcode', 'qris_pay' => 'fa-bolt', 'transfer' => 'fa-building-columns', 'card' => 'fa-credit-card'];
  foreach ($outlet->activePaymentMethods() as $pmCode => $pmInfo) {
    $payButtons[] = [$pmCode, $pmInfo[0], $pmIcons[$pmCode] ?? 'fa-credit-card'];
  }
  $payColCount = min(count($payButtons), 4);
@endphp
<div class="modal-backdrop" id="modal-fine-pay" onclick="if(event.target===this)closeModal('modal-fine-pay')">
  <div class="modal-box" style="max-width:420px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
          <i class="fa-solid fa-triangle-exclamation" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Bayar Denda
        </div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">Transaksi <span id="fp-order" style="font-weight:600"></span></div>
      </div>
      <button onclick="closeModal('modal-fine-pay')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer;color:var(--sub);font-size:13px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="padding:18px 22px;display:flex;flex-direction:column;gap:16px">

      <div style="text-align:center;padding:16px;background:var(--ac-lt);border-radius:14px">
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px">Sisa Denda Belum Dibayar</div>
        <div class="font-display" id="fp-remaining-display" style="font-size:28px;font-weight:800;color:var(--ac)">Rp 0</div>
      </div>

      <div>
        <label class="f-label">Jumlah Dibayar</label>
        <div style="position:relative">
          <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
          <input type="text" inputmode="numeric" id="fp-amount" class="f-input" placeholder="0" style="padding-left:30px" oninput="formatFinePayAmount()">
        </div>
        <div id="fp-amount-err" style="display:none;font-size:11.5px;color:#f87171;margin-top:5px"></div>
      </div>

      <div id="fp-deposit-box" style="display:none;padding:14px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
        <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;gap:10px">
          <span style="font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-piggy-bank" style="color:var(--ac)"></i>
            Potong dari Deposit
            <span id="fp-deposit-available" style="font-weight:400;color:var(--muted);font-size:11.5px"></span>
          </span>
          <input type="checkbox" id="fp-use-deposit" onchange="updateFinePayLayout()" style="width:18px;height:18px;accent-color:var(--ac);cursor:pointer;flex-shrink:0">
        </label>
        <div id="fp-deposit-breakdown" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid var(--border);font-size:12.5px;color:var(--sub);flex-direction:column;gap:4px">
          <div style="display:flex;justify-content:space-between"><span>Dipotong dari deposit</span><strong id="fp-deposit-cut-display" style="color:var(--text)">Rp 0</strong></div>
          <div style="display:flex;justify-content:space-between"><span>Sisa dibayar lewat metode</span><strong id="fp-method-amount-display" style="color:var(--ac)">Rp 0</strong></div>
        </div>
      </div>

      <div id="fp-fully-covered" style="display:none;text-align:center;padding:16px;border-radius:12px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3)">
        <i class="fa-solid fa-circle-check" style="font-size:26px;color:#34d399;display:block;margin-bottom:8px"></i>
        <div style="font-size:13px;color:var(--sub)">Seluruh jumlah tertutup oleh deposit. Tidak perlu pembayaran tambahan.</div>
      </div>

      <div id="fp-method-section">
        <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">Metode Pembayaran</div>
        <div style="display:grid;grid-template-columns:repeat({{ $payColCount }},1fr);gap:8px">
          @foreach($payButtons as [$pmCode, $pmLabel, $pmIcon])
          <button type="button" class="fp-method-btn" data-method="{{ $pmCode }}" onclick="selectFineMethod('{{ $pmCode }}')"
            style="padding:10px 6px;border-radius:12px;border:2px solid var(--border);background:var(--surface2);cursor:pointer;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:6px">
            <i class="fa-solid {{ $pmIcon }}" style="font-size:17px;color:var(--muted)"></i>
            <span style="font-size:10.5px;font-weight:700;color:var(--sub);white-space:nowrap">{{ $pmLabel }}</span>
          </button>
          @endforeach
        </div>
      </div>

      <div id="fp-noncash-section" style="display:none;flex-direction:column;gap:12px">
        <div>
          <label class="f-label">No. Referensi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input type="text" id="fp-reference" class="f-input" maxlength="100" placeholder="cth: ID transaksi/transfer">
        </div>
        <div>
          <label class="f-label">Foto Bukti <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <label id="fp-photo-label" for="fp-photo"
            style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px;border:2px dashed var(--border);border-radius:12px;padding:16px;cursor:pointer;transition:border-color .15s;min-height:84px;position:relative;overflow:hidden"
            onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
            <div id="fp-photo-placeholder" style="display:flex;flex-direction:column;align-items:center;gap:6px;pointer-events:none">
              <i class="fa-solid fa-camera" style="font-size:20px;color:var(--muted)"></i>
              <span style="font-size:11.5px;color:var(--muted)">Klik untuk unggah foto bukti</span>
              <span style="font-size:10px;color:var(--muted);opacity:.7">JPG, PNG, WebP</span>
            </div>
            <img id="fp-photo-preview" src="" alt="" style="display:none;max-height:120px;max-width:100%;border-radius:8px;object-fit:contain">
          </label>
          <input type="file" id="fp-photo" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewFinePhoto(this)">
        </div>
      </div>

    </div>

    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
      <button type="button" onclick="closeModal('modal-fine-pay')"
        style="flex:0 0 auto;padding:11px 18px;border-radius:12px;border:1px solid var(--border);background:none;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">
        Batal
      </button>
      <button type="button" id="btn-confirm-fine-pay" onclick="confirmFinePay()"
        style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
        <i class="fa-solid fa-circle-check"></i> Konfirmasi Pembayaran
      </button>
    </div>
  </div>
</div>

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';
let fpRentalId        = null;
let fpRemaining       = 0;
let fpDepositAvailable = 0;
let fpMethod          = 'cash';

function fmtRp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

function openFinePay(id, orderNumber, remaining, depositAvail) {
  fpRentalId        = id;
  fpRemaining       = remaining;
  fpDepositAvailable = depositAvail || 0;

  document.getElementById('fp-order').textContent = orderNumber;
  document.getElementById('fp-remaining-display').textContent = fmtRp(remaining);
  document.getElementById('fp-amount').value = Number(remaining).toLocaleString('id-ID');
  document.getElementById('fp-amount-err').style.display = 'none';
  document.getElementById('fp-reference').value = '';
  document.getElementById('fp-photo').value = '';
  document.getElementById('fp-photo-preview').style.display = 'none';
  document.getElementById('fp-photo-placeholder').style.display = 'flex';

  document.getElementById('fp-use-deposit').checked = false;
  if (fpDepositAvailable > 0) {
    document.getElementById('fp-deposit-box').style.display = 'block';
    document.getElementById('fp-deposit-available').textContent = '(tersedia ' + fmtRp(fpDepositAvailable) + ')';
  } else {
    document.getElementById('fp-deposit-box').style.display = 'none';
  }
  document.getElementById('fp-deposit-breakdown').style.display = 'none';

  selectFineMethod('cash');
  updateFinePayLayout();
  openModal('modal-fine-pay');
}

function formatFinePayAmount() {
  const el = document.getElementById('fp-amount');
  const before = el.value.substring(0, el.selectionStart);
  const digitsBeforeCursor = (before.match(/\d/g) || []).length;
  const raw = el.value.replace(/\D/g, '');
  const formatted = raw ? Number(raw).toLocaleString('id-ID') : '';
  el.value = formatted;
  let seen = 0, newPos = formatted.length;
  for (let i = 0; i < formatted.length; i++) {
    if (/\d/.test(formatted[i])) seen++;
    if (seen === digitsBeforeCursor) { newPos = i + 1; break; }
  }
  try { el.setSelectionRange(newPos, newPos); } catch (_) {}
  updateFinePayLayout();
}

function fineAmountValue() {
  return parseInt(document.getElementById('fp-amount').value.replace(/\D/g, '') || '0', 10);
}

/** Sesuaikan tampilan (metode/tertutup-deposit) berdasarkan toggle "Potong dari Deposit" & jumlah dibayar. */
function updateFinePayLayout() {
  const amount     = fineAmountValue();
  const useDeposit = document.getElementById('fp-use-deposit').checked;
  const cut        = useDeposit ? Math.min(fpDepositAvailable, amount) : 0;
  const methodAmt  = amount - cut;

  const breakdown = document.getElementById('fp-deposit-breakdown');
  if (useDeposit) {
    breakdown.style.display = 'flex';
    document.getElementById('fp-deposit-cut-display').textContent = fmtRp(cut);
    document.getElementById('fp-method-amount-display').textContent = fmtRp(methodAmt);
  } else {
    breakdown.style.display = 'none';
  }

  const methodSection = document.getElementById('fp-method-section');
  const fullyCovered  = document.getElementById('fp-fully-covered');

  if (methodAmt <= 0 && amount > 0) {
    methodSection.style.display = 'none';
    document.getElementById('fp-noncash-section').style.display = 'none';
    fullyCovered.style.display = 'block';
  } else {
    methodSection.style.display = 'block';
    fullyCovered.style.display = 'none';
    selectFineMethod(fpMethod);
  }
}

function selectFineMethod(m) {
  fpMethod = m;
  const isCash = m === 'cash';
  document.querySelectorAll('.fp-method-btn').forEach(btn => {
    const sel = btn.dataset.method === m;
    btn.style.borderColor = sel ? 'var(--ac)' : 'var(--border)';
    btn.style.background  = sel ? 'var(--ac-lt)' : 'var(--surface2)';
    btn.querySelector('i').style.color    = sel ? 'var(--ac)' : 'var(--muted)';
    btn.querySelector('span').style.color = sel ? 'var(--ac)' : 'var(--sub)';
  });
  document.getElementById('fp-noncash-section').style.display = isCash ? 'none' : 'flex';
}

function previewFinePhoto(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('fp-photo-preview').src = e.target.result;
    document.getElementById('fp-photo-preview').style.display = 'block';
    document.getElementById('fp-photo-placeholder').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

async function confirmFinePay() {
  const btn = document.getElementById('btn-confirm-fine-pay');
  const amount = fineAmountValue();
  const useDeposit = document.getElementById('fp-use-deposit').checked;
  const cut = useDeposit ? Math.min(fpDepositAvailable, amount) : 0;
  const methodAmt = amount - cut;
  const errEl = document.getElementById('fp-amount-err');

  if (amount <= 0) {
    errEl.textContent = 'Jumlah wajib diisi.';
    errEl.style.display = 'block';
    return;
  }
  if (amount > fpRemaining) {
    errEl.textContent = 'Jumlah tidak boleh melebihi sisa denda.';
    errEl.style.display = 'block';
    return;
  }
  errEl.style.display = 'none';

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

  const formData = new FormData();
  formData.append('amount', amount);
  formData.append('use_deposit', useDeposit ? 1 : 0);
  if (methodAmt > 0) {
    formData.append('payment_method', fpMethod);
    const reference = document.getElementById('fp-reference').value.trim();
    if (reference) formData.append('reference_number', reference);
    const photoFile = document.getElementById('fp-photo').files[0];
    if (photoFile) formData.append('photo', photoFile);
  }

  try {
    const res = await fetch(`/${outletRp}/${outletId}/fines/${fpRentalId}/pay`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: formData,
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || (data.errors?.amount?.[0]) || 'Gagal mencatat pembayaran denda.');

    closeModal('modal-fine-pay');
    showToast('success', data.message || 'Pembayaran denda berhasil dicatat.');
    setTimeout(() => location.reload(), 700);
  } catch (err) {
    showToast('error', err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Konfirmasi Pembayaran';
  }
}
</script>
@endpush

</x-outlet-layout>
