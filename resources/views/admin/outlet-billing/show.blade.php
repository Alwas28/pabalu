<x-app-layout>
<x-slot name="pageTitle">Tagihan Pro Plan — {{ $outlet->name }}</x-slot>

@php
  $bulanLabel = ['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
@endphp

@if(session('success'))
<div style="padding:12px 16px;border-radius:12px;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);
     display:flex;align-items:center;gap:10px;font-size:12.5px;color:#34d399;font-weight:600">
  <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:12px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);
     display:flex;align-items:center;gap:10px;font-size:12.5px;color:#f87171;font-weight:600">
  <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
</div>
@endif

<a href="{{ route('admin.tagihan.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--sub);text-decoration:none;margin-top:14px">
  <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Outlet
</a>

<div class="stat-grid" style="margin-top:14px">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(129,140,248,.15);color:#818cf8"><i class="fa-solid fa-shop"></i></div>
    <div>
      <div class="stat-num" style="font-size:17px">{{ $outlet->name }}</div>
      <div class="stat-label">Outlet</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(236,72,153,.15);color:#ec4899"><i class="fa-solid fa-user"></i></div>
    <div>
      <div class="stat-num" style="font-size:17px">{{ $outlet->owner?->name ?? '(tanpa owner)' }}</div>
      <div class="stat-label">Owner</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-tag"></i></div>
    <div>
      <div class="stat-num" style="font-size:17px">{{ $outlet->outletType?->name ?? '—' }}</div>
      <div class="stat-label">Jenis Outlet</div>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-file-invoice-dollar a-text" style="margin-right:8px"></i>Riwayat Tagihan</div>
    <button onclick="openModal('modal-create-invoice')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Input Tagihan
    </button>
  </div>

  @if($invoices->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-file-invoice-dollar"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Tagihan</div>
    <p style="font-size:13px;color:var(--muted)">Buat tagihan manual berdasarkan perhitungan transaksi bulanan/tahunan outlet ini.</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Periode</th>
          <th style="text-align:right">Total Transaksi</th>
          <th style="text-align:right">Nominal Tagihan</th>
          <th>Catatan</th>
          <th style="text-align:center;width:100px">Status</th>
          <th style="text-align:center;width:90px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoices as $inv)
        <tr>
          <td class="td-main">
            {{ $inv->period_type === 'bulan' ? $bulanLabel[(string) $inv->period_start->month] . ' ' . $inv->period_start->year : 'Tahun ' . $inv->period_start->year }}
          </td>
          <td style="text-align:right;font-size:13px;color:var(--sub)">Rp {{ number_format($inv->transaction_total, 0, ',', '.') }}</td>
          <td style="text-align:right;font-size:13px;font-weight:700;color:var(--text)">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
          <td style="font-size:12px;color:var(--muted);max-width:220px">{{ $inv->note ?? '—' }}</td>
          <td style="text-align:center">
            @if($inv->status === 'lunas')
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Lunas</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Belum Lunas</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              @if($inv->status !== 'lunas')
              <button type="button" title="Tandai Lunas"
                onclick='openConfirmPaid({{ json_encode(["id" => $inv->id, "period" => ($inv->period_type === "bulan" ? $bulanLabel[(string) $inv->period_start->month] . " " . $inv->period_start->year : "Tahun " . $inv->period_start->year), "amount" => "Rp " . number_format($inv->amount, 0, ",", ".")]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#34d399;font-size:12px">
                <i class="fa-solid fa-check"></i>
              </button>
              @endif
              <button type="button" title="Hapus"
                onclick='openConfirmDeleteInvoice({{ json_encode(["id" => $inv->id, "period" => ($inv->period_type === "bulan" ? $bulanLabel[(string) $inv->period_start->month] . " " . $inv->period_start->year : "Tahun " . $inv->period_start->year)]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- Modal Input Tagihan --}}
<div class="modal-backdrop" id="modal-create-invoice" onclick="if(event.target===this)closeModal('modal-create-invoice')">
  <div class="modal-box" style="max-width:480px">
    <form method="POST" action="{{ route('admin.tagihan.store', $outlet) }}" id="form-create-invoice">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Input Tagihan — {{ $outlet->name }}</h3>
        <button type="button" onclick="closeModal('modal-create-invoice')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Jenis Periode <span style="color:var(--ac)">*</span></label>
            <select name="period_type" id="inv-period-type" class="f-input" onchange="toggleInvoiceMonth()" required>
              <option value="bulan">Bulanan</option>
              <option value="tahun">Tahunan</option>
            </select>
          </div>
          <div id="inv-month-wrap">
            <label class="f-label">Bulan <span style="color:var(--ac)">*</span></label>
            <select name="month" id="inv-month" class="f-input">
              @foreach($bulanLabel as $num => $label)
                <option value="{{ $num }}" {{ (int) $num === (int) now()->format('n') ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div>
          <label class="f-label">Tahun <span style="color:var(--ac)">*</span></label>
          <input name="year" id="inv-year" type="number" class="f-input" value="{{ now()->year }}" min="2020" max="2100" required>
        </div>

        <div>
          <button type="button" onclick="calcInvoiceTransaction()"
            style="width:100%;padding:9px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit">
            <i class="fa-solid fa-calculator" style="margin-right:6px"></i>Hitung Total Transaksi Periode Ini
          </button>
          <div id="inv-calc-result" style="display:none;margin-top:8px;padding:10px 12px;border-radius:10px;background:var(--ac-lt);font-size:12.5px;color:var(--text)"></div>
        </div>

        <div>
          <label class="f-label">Nominal Tagihan (Rp) <span style="color:var(--ac)">*</span></label>
          <div style="position:relative">
            <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:13.5px;color:var(--muted);font-weight:600">Rp</span>
            <input type="text" inputmode="numeric" id="inv-amount-display" class="f-input" style="padding-left:34px"
              placeholder="cth: 50.000" oninput="formatInvoiceAmount(this)" autocomplete="off" required>
          </div>
          <input type="hidden" name="amount" id="inv-amount-raw">
          <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Ditentukan manual sesuai tarif yang berlaku untuk rentang omset di atas.</p>
        </div>
        <div>
          <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="note" class="f-input" rows="2" maxlength="500" placeholder="cth: 5% dari omset harian di atas Rp1 juta"></textarea>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-create-invoice')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Buat Tagihan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Konfirmasi Tandai Lunas --}}
<div class="modal-backdrop" id="modal-confirm-paid" onclick="if(event.target===this)closeModal('modal-confirm-paid')">
  <div class="modal-box" style="max-width:400px">
    <form id="form-confirm-paid" method="POST" action="">
      @csrf @method('PATCH')
      <div style="padding:28px 24px 8px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(52,211,153,.15);display:grid;place-items:center;margin:0 auto 16px;color:#34d399;font-size:20px">
          <i class="fa-solid fa-check"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Tandai Tagihan Lunas?</h3>
        <p style="font-size:13px;color:var(--muted);line-height:1.6">
          Tagihan periode <strong id="confirm-paid-period" style="color:var(--text)"></strong> senilai
          <strong id="confirm-paid-amount" style="color:var(--text)"></strong> akan ditandai lunas.
        </p>
      </div>
      <div style="padding:20px 24px 24px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-confirm-paid')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Ya, Tandai Lunas
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Konfirmasi Hapus Tagihan --}}
<div class="modal-backdrop" id="modal-confirm-delete-invoice" onclick="if(event.target===this)closeModal('modal-confirm-delete-invoice')">
  <div class="modal-box" style="max-width:400px">
    <form id="form-confirm-delete-invoice" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 8px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Tagihan Ini?</h3>
        <p style="font-size:13px;color:var(--muted);line-height:1.6">
          Tagihan periode <strong id="confirm-delete-invoice-period" style="color:var(--text)"></strong> akan
          dihapus permanen dan tidak bisa dibatalkan.
        </p>
      </div>
      <div style="padding:20px 24px 24px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-confirm-delete-invoice')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Ya, Hapus
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function formatInvoiceAmount(el) {
  const raw = el.value.replace(/\D/g, '');
  document.getElementById('inv-amount-raw').value = raw;
  el.value = raw ? new Intl.NumberFormat('id-ID').format(raw) : '';
}

const markPaidUrlTemplate     = "{{ route('admin.tagihan.invoices.mark-paid', ['invoice' => '__ID__']) }}";
const deleteInvoiceUrlTemplate = "{{ route('admin.tagihan.invoices.destroy', ['invoice' => '__ID__']) }}";

function openConfirmPaid(inv) {
  document.getElementById('form-confirm-paid').action = markPaidUrlTemplate.replace('__ID__', inv.id);
  document.getElementById('confirm-paid-period').textContent = inv.period;
  document.getElementById('confirm-paid-amount').textContent = inv.amount;
  openModal('modal-confirm-paid');
}

function openConfirmDeleteInvoice(inv) {
  document.getElementById('form-confirm-delete-invoice').action = deleteInvoiceUrlTemplate.replace('__ID__', inv.id);
  document.getElementById('confirm-delete-invoice-period').textContent = inv.period;
  openModal('modal-confirm-delete-invoice');
}

function toggleInvoiceMonth() {
  const isMonthly = document.getElementById('inv-period-type').value === 'bulan';
  document.getElementById('inv-month-wrap').style.display = isMonthly ? '' : 'none';
}

function calcInvoiceTransaction() {
  const periodType  = document.getElementById('inv-period-type').value;
  const month       = document.getElementById('inv-month').value;
  const year        = document.getElementById('inv-year').value;
  const resultBox   = document.getElementById('inv-calc-result');

  if (!year) {
    resultBox.style.display = 'block';
    resultBox.textContent = 'Isi tahun terlebih dahulu.';
    return;
  }

  const params = new URLSearchParams({ period_type: periodType, year: year });
  if (periodType === 'bulan') params.set('month', month);

  resultBox.style.display = 'block';
  resultBox.textContent = 'Menghitung...';

  fetch("{{ route('admin.tagihan.calc', $outlet) }}?" + params.toString(), {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
    .then(res => { if (!res.ok) throw new Error(); return res.json(); })
    .then(data => {
      const total = new Intl.NumberFormat('id-ID').format(data.transaction_total);
      resultBox.innerHTML = 'Total Transaksi Periode Ini: <strong>Rp ' + total + '</strong> dari ' + data.transaction_count + ' transaksi';
    })
    .catch(() => {
      resultBox.textContent = 'Gagal menghitung. Pastikan periode valid.';
    });
}

document.addEventListener('DOMContentLoaded', toggleInvoiceMonth);
</script>
@endpush

</x-app-layout>
