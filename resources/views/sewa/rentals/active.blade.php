<x-outlet-layout :outlet="$outlet" pageTitle="Sewa Aktif">

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Sewa Aktif</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Sewa yang barangnya sedang berada di tangan pelanggan
    </p>
  </div>
  <a href="{{ $outlet->route('rentals.create') }}"
    style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;text-decoration:none">
    <i class="fa-solid fa-plus" style="font-size:12px"></i> Sewa Baru
  </a>
</div>

{{-- ── STATS ── --}}
@php
  $total    = $rentals->count();
  $overdue  = $rentals->filter->isOverdue()->count();
  $dueToday = $rentals->filter(fn($r) => $r->end_at->isToday())->count();
@endphp
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-key"></i></div>
    <div><div class="stat-num">{{ $total }}</div><div class="stat-label">Total Sewa Aktif</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-clock"></i></div>
    <div><div class="stat-num">{{ $dueToday }}</div><div class="stat-label">Jatuh Tempo Hari Ini</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div><div class="stat-num">{{ $overdue }}</div><div class="stat-label">Terlambat</div></div>
  </div>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-hourglass-half" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Daftar Sewa Aktif</span>
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
        <th>Durasi</th>
        <th style="text-align:center">Bayar</th>
        <th style="text-align:center">Status</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rentals as $r)
      <tr>
        <td class="td-main" style="font-family:monospace;font-size:12px">{{ $r->order_number }}</td>
        <td>
          <a href="{{ $outlet->route('customers.show', [$r->customer]) }}" style="color:var(--ac);font-weight:600;text-decoration:none">
            {{ $r->customer->name }}
          </a>
        </td>
        <td>
          {{ $r->rentalUnit->rentalItem->name }}
          <div style="font-size:11px;color:var(--muted)">{{ $r->rentalUnit->code }} · {{ $r->rentalTypeLabel() }}</div>
        </td>
        <td class="countdown-cell" data-end="{{ $r->end_at->toIso8601String() }}" style="font-size:12.5px;font-weight:700">
          <span class="countdown-text">…</span>
        </td>
        <td style="text-align:center">
          @php $payStatus = $r->paymentStatusLabel(); @endphp
          @if($payStatus === 'Lunas')
          <span class="badge badge-green">Lunas</span>
          @elseif($payStatus === 'DP')
          <span class="badge badge-amber">DP</span>
          @else
          <span class="badge badge-red">Belum Bayar</span>
          @endif
        </td>
        <td style="text-align:center">
          @if($r->isOverdue())
          <span class="badge badge-red">Terlambat</span>
          @elseif($r->end_at->isToday())
          <span class="badge badge-amber">Jatuh Tempo</span>
          @else
          <span class="badge badge-blue">Aktif</span>
          @endif
        </td>
        <td style="text-align:right;white-space:nowrap">
          <a href="{{ $outlet->route('rentals.show', [$r]) }}"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;text-decoration:none;display:inline-block">
            Detail
          </a>
          <a href="{{ $outlet->route('rentals.edit', [$r]) }}"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;text-decoration:none;display:inline-block;margin-left:4px">
            <i class="fa-solid fa-pen" style="margin-right:4px;font-size:11px"></i>Edit
          </a>
          @if($r->isFullyPaid())
          <button type="button" onclick="openReturn({{ $r->id }}, {{ json_encode($r->order_number) }}, {{ $r->depositAvailable() }}, {{ $r->remainingAmount() }})"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px;font-weight:600;margin-left:4px">
            <i class="fa-solid fa-right-left" style="margin-right:4px"></i>Proses Pengembalian
          </button>
          @else
          <button type="button" onclick="openPay({{ $r->id }}, {{ json_encode($r->order_number) }}, {{ $r->remainingAmount() }}, {{ $r->depositAvailable() }})"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px;font-weight:600;margin-left:4px">
            Proses Bayar
          </button>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>

<div style="font-size:11.5px;color:var(--muted);display:flex;align-items:center;gap:6px" class="animate-fadeUp d3">
  <i class="fa-solid fa-circle-info"></i>
  Untuk memperpanjang atau memproses pengembalian, gunakan halaman
  <a href="{{ $outlet->route('rentals.extend') }}" style="color:var(--ac);font-weight:600">Perpanjangan</a> atau
  <a href="{{ $outlet->route('rentals.returns') }}" style="color:var(--ac);font-weight:600">Pengembalian</a>.
</div>

{{-- ══ MODAL PROSES BAYAR ══ --}}
@php
  $payButtons = [['cash', 'Tunai', 'fa-money-bill-wave']];
  $pmIcons    = ['qris_transfer' => 'fa-qrcode', 'qris_pay' => 'fa-bolt', 'transfer' => 'fa-building-columns', 'card' => 'fa-credit-card'];
  foreach ($outlet->activePaymentMethods() as $pmCode => $pmInfo) {
    $payButtons[] = [$pmCode, $pmInfo[0], $pmIcons[$pmCode] ?? 'fa-credit-card'];
  }
  $payColCount = min(count($payButtons), 4);
@endphp
<div class="modal-backdrop" id="modal-pay" onclick="if(event.target===this)closeModal('modal-pay')">
  <div class="modal-box" style="max-width:420px">

    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
          <i class="fa-solid fa-cash-register" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Proses Bayar
        </div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">Transaksi <span id="pay-order" style="font-weight:600"></span></div>
      </div>
      <button onclick="closeModal('modal-pay')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer;color:var(--sub);font-size:13px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="padding:18px 22px;display:flex;flex-direction:column;gap:16px">

      <div style="text-align:center;padding:16px;background:var(--ac-lt);border-radius:14px">
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px">Sisa Tagihan Sewa</div>
        <div class="font-display" id="pay-total-display" style="font-size:32px;font-weight:800;color:var(--ac)">Rp 0</div>
      </div>

      <div>
        <label class="f-label">Denda <span style="color:var(--muted);font-weight:400">(opsional — menambah nominal yang harus dibayar)</span></label>
        <div style="position:relative">
          <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
          <input type="text" inputmode="numeric" id="pay-fine" class="f-input" placeholder="0" style="padding-left:30px" oninput="formatFineInput()">
        </div>
      </div>

      <div id="pay-deposit-box" style="display:none;padding:14px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
        <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;gap:10px">
          <span style="font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-piggy-bank" style="color:var(--ac)"></i>
            Potong dari Deposit
            <span id="pay-deposit-available" style="font-weight:400;color:var(--muted);font-size:11.5px"></span>
          </span>
          <input type="checkbox" id="pay-use-deposit" onchange="updatePayLayout()" style="width:18px;height:18px;accent-color:var(--ac);cursor:pointer;flex-shrink:0">
        </label>
        <div id="pay-deposit-breakdown" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid var(--border);font-size:12.5px;color:var(--sub);flex-direction:column;gap:4px">
          <div style="display:flex;justify-content:space-between"><span>Dipotong dari deposit</span><strong id="pay-deposit-cut-display" style="color:var(--text)">Rp 0</strong></div>
          <div style="display:flex;justify-content:space-between"><span>Sisa biaya sewa setelah deposit</span><strong id="pay-leftover-display" style="color:var(--ac)">Rp 0</strong></div>
        </div>
      </div>

      <div id="pay-total-due-box" style="display:none;padding:12px 14px;border-radius:10px;background:var(--surface2);border:1px solid var(--border);flex-direction:column;gap:4px">
        <div id="pay-fine-line" style="display:none;justify-content:space-between;font-size:12.5px;color:#f87171"><span>Denda ditambahkan</span><strong id="pay-fine-display">Rp 0</strong></div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:12.5px;color:var(--sub);font-weight:600">Total Dibayar Sekarang</span>
          <span id="pay-total-due-display" class="font-display" style="font-size:18px;font-weight:800;color:var(--ac)">Rp 0</span>
        </div>
      </div>

      <div id="pay-fully-covered" style="display:none;text-align:center;padding:16px;border-radius:12px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3)">
        <i class="fa-solid fa-circle-check" style="font-size:26px;color:#34d399;display:block;margin-bottom:8px"></i>
        <div style="font-size:13px;color:var(--sub)">Seluruh sisa tagihan tertutup oleh deposit. Tidak perlu pembayaran tambahan.</div>
      </div>

      <div id="pay-method-section">
        <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">Metode Pembayaran</div>
        <div style="display:grid;grid-template-columns:repeat({{ $payColCount }},1fr);gap:8px">
          @foreach($payButtons as [$pmCode, $pmLabel, $pmIcon])
          <button type="button" class="pay-method-btn" data-method="{{ $pmCode }}" onclick="selectPayMethod('{{ $pmCode }}')"
            style="padding:10px 6px;border-radius:12px;border:2px solid var(--border);background:var(--surface2);cursor:pointer;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:6px">
            <i class="fa-solid {{ $pmIcon }}" style="font-size:17px;color:var(--muted)"></i>
            <span style="font-size:10.5px;font-weight:700;color:var(--sub);white-space:nowrap">{{ $pmLabel }}</span>
          </button>
          @endforeach
        </div>
      </div>

      <div id="pay-cash-section" style="display:flex;flex-direction:column;gap:10px">
        <div>
          <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">Uang Diterima</div>
          <div style="position:relative">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--muted);font-weight:600">Rp</span>
            <input id="pay-amount" type="text" inputmode="numeric" placeholder="0"
              style="width:100%;background:var(--surface2);border:2px solid var(--ac);border-radius:12px;padding:10px 10px 10px 34px;font-size:20px;font-weight:700;color:var(--text);outline:none;font-family:inherit"
              oninput="formatPayAmountInput()">
          </div>
        </div>
        <div id="pay-quick-amounts" style="display:flex;gap:6px;flex-wrap:wrap"></div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 14px;border-radius:10px;background:var(--surface2)">
          <span style="font-size:13px;color:var(--sub);font-weight:600">Kembalian</span>
          <span id="pay-change" class="font-display" style="font-size:17px;font-weight:800;color:#34d399">Rp 0</span>
        </div>
      </div>

      <div id="pay-noncash-section" style="display:none;flex-direction:column;gap:12px">
        <div style="text-align:center;padding:16px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
          <i class="fa-solid fa-circle-check" style="font-size:30px;color:#34d399;margin-bottom:10px;display:block"></i>
          <div style="font-size:13.5px;color:var(--sub)">Konfirmasi pembayaran <span id="pay-method-label" style="font-weight:700;color:var(--text)"></span></div>
          <div class="font-display" id="pay-noncash-total" style="font-size:20px;font-weight:800;color:var(--text);margin-top:6px">Rp 0</div>
        </div>
        <div>
          <label class="f-label">No. Referensi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input type="text" id="pay-reference" class="f-input" maxlength="100" placeholder="cth: ID transaksi/transfer">
        </div>
        <div>
          <label class="f-label">Foto Bukti <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <label id="pay-photo-label" for="pay-photo"
            style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px;border:2px dashed var(--border);border-radius:12px;padding:16px;cursor:pointer;transition:border-color .15s;min-height:84px;position:relative;overflow:hidden"
            onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
            <div id="pay-photo-placeholder" style="display:flex;flex-direction:column;align-items:center;gap:6px;pointer-events:none">
              <i class="fa-solid fa-camera" style="font-size:20px;color:var(--muted)"></i>
              <span style="font-size:11.5px;color:var(--muted)">Klik untuk unggah foto bukti</span>
              <span style="font-size:10px;color:var(--muted);opacity:.7">JPG, PNG, WebP</span>
            </div>
            <img id="pay-photo-preview" src="" alt="" style="display:none;max-height:120px;max-width:100%;border-radius:8px;object-fit:contain">
          </label>
          <input type="file" id="pay-photo" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewPayPhoto(this)">
        </div>
      </div>

    </div>

    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
      <button type="button" onclick="closeModal('modal-pay')"
        style="flex:0 0 auto;padding:11px 18px;border-radius:12px;border:1px solid var(--border);background:none;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">
        Batal
      </button>
      <button type="button" id="btn-confirm-pay" onclick="confirmPay()"
        style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
        <i class="fa-solid fa-circle-check"></i> Konfirmasi Pembayaran
      </button>
    </div>

  </div>
</div>

@include('sewa.rentals._return-modal')

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';
let currentPayMethod  = 'cash';
let currentTotal      = 0;
let depositAvailable  = 0;
let currentRentalId   = null;

const PAY_METHOD_LABELS = @json(array_merge(['cash' => 'Tunai'], array_map(fn($pm) => $pm[0], $outlet->activePaymentMethods())));

function fmtRp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

function fineAmount() {
  const raw = document.getElementById('pay-fine').value.replace(/\D/g, '');
  return Math.max(0, parseInt(raw || '0', 10) || 0);
}

function formatFineInput() {
  const el = document.getElementById('pay-fine');
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
  updatePayLayout();
}

/** Sisa yang benar-benar harus dibayar lewat metode pembayaran: biaya sewa setelah potongan deposit (jika dipilih), ditambah denda (deposit tidak menutup denda). */
function activeAmount() {
  const useDeposit = document.getElementById('pay-use-deposit').checked;
  const cut = useDeposit ? Math.min(depositAvailable, currentTotal) : 0;
  return (currentTotal - cut) + fineAmount();
}

function previewPayPhoto(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('pay-photo-preview').src = e.target.result;
    document.getElementById('pay-photo-preview').style.display = 'block';
    document.getElementById('pay-photo-placeholder').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

function resetPayPhotoPreview() {
  document.getElementById('pay-photo-preview').style.display = 'none';
  document.getElementById('pay-photo-preview').src = '';
  document.getElementById('pay-photo-placeholder').style.display = 'flex';
}

function openPay(id, orderNumber, remaining, depositAvail) {
  currentRentalId  = id;
  currentTotal     = remaining;
  depositAvailable = depositAvail || 0;

  document.getElementById('pay-order').textContent = orderNumber;
  document.getElementById('pay-total-display').textContent = fmtRp(remaining);
  document.getElementById('pay-reference').value = '';
  document.getElementById('pay-photo').value = '';
  document.getElementById('pay-fine').value = '';
  resetPayPhotoPreview();

  document.getElementById('pay-use-deposit').checked = false;
  if (depositAvailable > 0) {
    document.getElementById('pay-deposit-box').style.display = 'block';
    document.getElementById('pay-deposit-available').textContent = '(tersedia ' + fmtRp(depositAvailable) + ')';
  } else {
    document.getElementById('pay-deposit-box').style.display = 'none';
  }
  document.getElementById('pay-deposit-breakdown').style.display = 'none';

  selectPayMethod('cash');
  updatePayLayout();
  openModal('modal-pay');
}

/** Sesuaikan tampilan (metode/kas/non-kas/tertutup-deposit) berdasarkan toggle deposit & input denda. */
function updatePayLayout() {
  const useDeposit = document.getElementById('pay-use-deposit').checked;
  const cut        = useDeposit ? Math.min(depositAvailable, currentTotal) : 0;
  const leftover   = currentTotal - cut;
  const fine       = fineAmount();
  const totalDue   = leftover + fine;

  const breakdown = document.getElementById('pay-deposit-breakdown');
  if (useDeposit) {
    breakdown.style.display = 'flex';
    document.getElementById('pay-deposit-cut-display').textContent = fmtRp(cut);
    document.getElementById('pay-leftover-display').textContent = fmtRp(leftover);
  } else {
    breakdown.style.display = 'none';
  }

  const totalDueBox = document.getElementById('pay-total-due-box');
  const fineLine     = document.getElementById('pay-fine-line');
  if (totalDue > 0) {
    totalDueBox.style.display = 'flex';
    document.getElementById('pay-total-due-display').textContent = fmtRp(totalDue);
    if (fine > 0) {
      fineLine.style.display = 'flex';
      document.getElementById('pay-fine-display').textContent = fmtRp(fine);
    } else {
      fineLine.style.display = 'none';
    }
  } else {
    totalDueBox.style.display = 'none';
  }

  const methodSection = document.getElementById('pay-method-section');
  const fullyCovered  = document.getElementById('pay-fully-covered');

  if (totalDue <= 0) {
    methodSection.style.display = 'none';
    document.getElementById('pay-cash-section').style.display = 'none';
    document.getElementById('pay-noncash-section').style.display = 'none';
    fullyCovered.style.display = 'block';
  } else {
    methodSection.style.display = 'block';
    fullyCovered.style.display = 'none';
    selectPayMethod(currentPayMethod);
  }
}

function selectPayMethod(m) {
  currentPayMethod = m;
  const isCash = m === 'cash';

  document.querySelectorAll('.pay-method-btn').forEach(btn => {
    const sel = btn.dataset.method === m;
    btn.style.borderColor = sel ? 'var(--ac)' : 'var(--border)';
    btn.style.background  = sel ? 'var(--ac-lt)' : 'var(--surface2)';
    btn.querySelector('i').style.color    = sel ? 'var(--ac)' : 'var(--muted)';
    btn.querySelector('span').style.color = sel ? 'var(--ac)' : 'var(--sub)';
  });

  document.getElementById('pay-cash-section').style.display    = isCash ? 'flex' : 'none';
  document.getElementById('pay-noncash-section').style.display = isCash ? 'none' : 'flex';

  if (!isCash) {
    document.getElementById('pay-method-label').textContent = PAY_METHOD_LABELS[m] || m;
    document.getElementById('pay-noncash-total').textContent = fmtRp(activeAmount());
  } else {
    buildQuickAmounts();
    document.getElementById('pay-amount').value = '';
    calcChange();
  }
}

function buildQuickAmounts() {
  const amt       = activeAmount();
  const container = document.getElementById('pay-quick-amounts');
  const rounds    = [amt, ...([5000, 10000, 20000, 50000, 100000].map(r => Math.ceil(amt / r) * r).filter(v => v >= amt))];
  const unique    = [...new Set(rounds)].slice(0, 5);
  container.innerHTML = unique.map(a =>
    `<button type="button" onclick="setQuickAmount(${a})"
      style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .12s"
      onmouseover="this.style.borderColor='var(--ac)';this.style.color='var(--ac)'"
      onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--sub)'">
      ${fmtRp(a)}
    </button>`
  ).join('');
}

function setQuickAmount(amt) {
  document.getElementById('pay-amount').value = amt.toLocaleString('id-ID');
  calcChange();
}

function calcChange() {
  const raw    = document.getElementById('pay-amount').value.replace(/\D/g, '');
  const paid   = parseInt(raw) || 0;
  const change = Math.max(0, paid - activeAmount());
  document.getElementById('pay-change').textContent = fmtRp(change);
  document.getElementById('pay-change').style.color = change >= 0 ? '#34d399' : '#f87171';
}

function formatPayAmountInput() {
  const el = document.getElementById('pay-amount');
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
  calcChange();
}

async function confirmPay() {
  const btn        = document.getElementById('btn-confirm-pay');
  const useDeposit = document.getElementById('pay-use-deposit').checked;
  const leftover   = activeAmount();
  const method     = currentPayMethod;
  const raw        = document.getElementById('pay-amount').value.replace(/\D/g, '');
  const cashReceived = parseInt(raw) || 0;

  if (leftover > 0 && method === 'cash' && cashReceived < leftover) {
    showToast('error', 'Uang diterima kurang dari sisa yang harus dibayar.');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

  const formData = new FormData();
  formData.append('use_deposit', useDeposit ? 1 : 0);
  formData.append('fine_amount', fineAmount());
  if (leftover > 0) {
    formData.append('payment_method', method);
    if (method === 'cash') formData.append('cash_received', cashReceived);
    const reference = document.getElementById('pay-reference').value.trim();
    if (reference) formData.append('reference_number', reference);
    const photoFile = document.getElementById('pay-photo').files[0];
    if (photoFile) formData.append('photo', photoFile);
  }

  try {
    const res = await fetch(`/${outletRp}/${outletId}/rentals/${currentRentalId}/pay`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: formData,
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Gagal memproses pembayaran.');

    closeModal('modal-pay');
    showToast('success', data.message || 'Pembayaran berhasil dicatat.');
    setTimeout(() => location.reload(), 700);
  } catch (err) {
    showToast('error', err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Konfirmasi Pembayaran';
  }
}

/* ── Countdown durasi sewa ── */
function updateCountdowns() {
  const now = new Date();
  document.querySelectorAll('.countdown-cell').forEach(cell => {
    const end   = new Date(cell.dataset.end);
    const span  = cell.querySelector('.countdown-text');
    let diff    = (end - now) / 1000;
    const overdue = diff < 0;
    diff = Math.abs(diff);

    const days    = Math.floor(diff / 86400);
    const hours   = Math.floor((diff % 86400) / 3600);
    const minutes = Math.floor((diff % 3600) / 60);

    let text;
    if (days > 0) text = `${days}h ${hours}j`;
    else if (hours > 0) text = `${hours}j ${minutes}m`;
    else text = `${minutes}m`;

    span.textContent = (overdue ? '-' : '') + text + (overdue ? ' lewat' : ' lagi');
    span.style.color = overdue ? '#f87171' : (days === 0 && hours < 2 ? '#fbbf24' : 'var(--text)');
  });
}
updateCountdowns();
setInterval(updateCountdowns, 30000);

@if($errors->any())
  {{-- Satu-satunya form full-page (bukan AJAX) di halaman ini adalah form Pengembalian. --}}
  openModal('modal-return');
@endif
</script>
@endpush

</x-outlet-layout>
