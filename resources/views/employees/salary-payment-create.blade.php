<x-app-layout>
<x-slot name="header">Catat Pembayaran Gaji</x-slot>

<div style="max-width:680px;margin:0 auto;display:flex;flex-direction:column;gap:20px">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);flex-wrap:wrap">
    <a href="{{ route('employees.index') }}"
      style="color:var(--muted);text-decoration:none"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-users"></i> Karyawan
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <a href="{{ route('employees.show', $employee) }}"
      style="color:var(--muted);text-decoration:none"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      {{ $employee->name }}
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">Catat Pembayaran Gaji</span>
  </div>

  {{-- Employee summary card --}}
  <div style="background:linear-gradient(135deg,var(--ac),var(--ac2));border-radius:18px;padding:22px 26px;color:#fff;display:flex;align-items:center;gap:16px" class="animate-fadeUp">
    @if($employee->photo)
      <img src="{{ asset('storage/'.$employee->photo) }}"
        style="width:56px;height:56px;border-radius:14px;object-fit:cover;flex-shrink:0;border:2px solid rgba(255,255,255,.3)">
    @else
      <div style="width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:22px;font-weight:800;flex-shrink:0">
        {{ strtoupper(substr($employee->name, 0, 1)) }}
      </div>
    @endif
    <div style="flex:1">
      <div style="font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;opacity:.75;margin-bottom:4px">
        Karyawan
      </div>
      <div class="font-display" style="font-size:20px;font-weight:700">{{ $employee->name }}</div>
      @if($employee->position)
      <div style="font-size:13px;opacity:.8;margin-top:2px">{{ $employee->position }}</div>
      @endif
    </div>
    @if($activeSalary)
    <div style="text-align:right">
      <div style="font-size:11px;opacity:.7;margin-bottom:3px">Gaji Aktif</div>
      <div class="font-display" style="font-size:22px;font-weight:800">
        Rp {{ number_format($activeSalary->amount, 0, ',', '.') }}
      </div>
    </div>
    @endif
  </div>

  {{-- Flash error --}}
  @if($errors->any())
  <div style="padding:12px 16px;border-radius:12px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;font-size:13.5px">
    <i class="fa-solid fa-circle-xmark" style="margin-right:8px"></i>
    {{ $errors->first() }}
  </div>
  @endif

  {{-- Form --}}
  <form method="POST" action="{{ route('employees.salary-payments.store', $employee) }}" class="animate-fadeUp d1">
    @csrf

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:18px;overflow:hidden">

      {{-- Header form --}}
      <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(52,211,153,.15);display:grid;place-items:center;color:#34d399;font-size:14px;flex-shrink:0">
          <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
        <div>
          <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Detail Pembayaran</div>
          <div style="font-size:12px;color:var(--muted)">Pembayaran akan otomatis dicatat sebagai pengeluaran outlet</div>
        </div>
      </div>

      <div style="padding:24px;display:flex;flex-direction:column;gap:18px">

        {{-- Outlet --}}
        <div>
          <label class="f-label">
            Outlet <span style="color:#f87171">*</span>
            <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--muted);margin-left:4px">— pengeluaran akan masuk ke outlet ini</span>
          </label>
          @if($outlets->isEmpty())
            <div style="padding:12px 14px;border-radius:10px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);font-size:13px;color:#fbbf24">
              <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>Tidak ada outlet aktif yang tersedia.
            </div>
          @else
            <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden">
              @foreach($outlets as $outlet)
              <label style="display:flex;align-items:center;gap:12px;padding:12px 16px;cursor:pointer;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}transition:background .15s"
                onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                <input type="radio" name="outlet_id" value="{{ $outlet->id }}" required
                  style="width:17px;height:17px;accent-color:var(--ac);flex-shrink:0;cursor:pointer"
                  @php
                    $selectedOutlet = old('outlet_id', $defaultOutletId);
                    $isChecked = $selectedOutlet
                      ? $selectedOutlet == $outlet->id
                      : $loop->first;
                  @endphp
                  {{ $isChecked ? 'checked' : '' }}>
                <div style="flex:1;min-width:0">
                  <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $outlet->name }}</div>
                  @if($outlet->address)
                  <div style="font-size:11.5px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    <i class="fa-solid fa-location-dot" style="font-size:9px;margin-right:3px"></i>{{ $outlet->address }}
                  </div>
                  @endif
                </div>
                <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:var(--surface2);color:var(--muted);flex-shrink:0">
                  {{ $outlet->outletType?->name ?? '—' }}
                </span>
              </label>
              @endforeach
            </div>
          @endif
        </div>

        {{-- Periode --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div>
            <label class="f-label">Bulan Periode <span style="color:#f87171">*</span></label>
            <select name="_period_month" id="pm-month" class="f-input" onchange="updatePeriod()">
              @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $mi => $ml)
                <option value="{{ str_pad($mi+1, 2, '0', STR_PAD_LEFT) }}"
                  {{ old('_period_month', str_pad(now()->month, 2, '0', STR_PAD_LEFT)) === str_pad($mi+1, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                  {{ $ml }}
                </option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="f-label">Tahun Periode <span style="color:#f87171">*</span></label>
            <select name="_period_year" id="pm-year" class="f-input" onchange="updatePeriod()">
              @for($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ old('_period_year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
              @endfor
            </select>
          </div>
        </div>
        <input type="hidden" name="period_month" id="pm-hidden" value="{{ old('period_month', now()->format('Y-m')) }}">

        {{-- Jumlah + Tanggal bayar --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div>
            <label class="f-label">Jumlah Dibayar (Rp) <span style="color:#f87171">*</span></label>
            <input name="amount" type="number" required min="1" class="f-input"
              placeholder="cth: 3000000"
              value="{{ old('amount', $activeSalary?->amount) }}"
              oninput="updatePreview()">
            @if($activeSalary)
            <div style="font-size:11.5px;color:var(--muted);margin-top:5px">
              <i class="fa-solid fa-circle-info" style="font-size:9px;color:var(--ac);margin-right:3px"></i>
              Gaji aktif: Rp {{ number_format($activeSalary->amount, 0, ',', '.') }}
              <button type="button" onclick="setActiveSalary()"
                style="margin-left:6px;font-size:11px;color:var(--ac);background:none;border:none;cursor:pointer;padding:0;text-decoration:underline">
                Isi otomatis
              </button>
            </div>
            @endif
          </div>
          <div>
            <label class="f-label">Tanggal Bayar <span style="color:#f87171">*</span></label>
            <input name="paid_at" type="date" required class="f-input"
              value="{{ old('paid_at', date('Y-m-d')) }}">
          </div>
        </div>

        {{-- Catatan --}}
        <div>
          <label class="f-label">Catatan <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--muted)">(opsional)</span></label>
          <input name="notes" type="text" class="f-input"
            placeholder="cth: Gaji bulan Juni + uang makan"
            value="{{ old('notes') }}">
        </div>

        {{-- Preview pengeluaran --}}
        <div id="expense-preview" style="padding:14px 16px;border-radius:12px;background:rgba(52,211,153,.06);border:1px solid rgba(52,211,153,.2)">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#34d399;margin-bottom:8px">
            <i class="fa-solid fa-receipt" style="margin-right:5px"></i>Pengeluaran yang akan dibuat
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)" id="prev-desc">
                Gaji {{ $employee->name }} - {{ now()->isoFormat('MMMM YYYY') }}
              </div>
              <div style="font-size:12px;color:var(--muted);margin-top:2px">
                Kategori: <span style="color:#a78bfa;font-weight:600">Gaji Karyawan</span>
              </div>
            </div>
            <div class="font-display" style="font-size:18px;font-weight:800;color:#34d399" id="prev-amount">
              Rp {{ number_format($activeSalary?->amount ?? 0, 0, ',', '.') }}
            </div>
          </div>
        </div>

      </div>

      {{-- Footer --}}
      <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;align-items:center">
        <a href="{{ route('employees.show', [$employee, 'tab' => 'payments']) }}"
          style="padding:11px 20px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:7px">
          <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Batal
        </a>
        <button type="submit" @if($outlets->isEmpty()) disabled @endif
          style="flex:1;padding:12px;border-radius:12px;border:none;
            background:{{ $outlets->isEmpty() ? 'var(--surface2)' : 'linear-gradient(135deg,#34d399,#10b981)' }};
            color:{{ $outlets->isEmpty() ? 'var(--muted)' : '#fff' }};
            font-size:14px;font-weight:800;cursor:{{ $outlets->isEmpty() ? 'not-allowed' : 'pointer' }};
            font-family:'Clash Display',sans-serif;
            display:flex;align-items:center;justify-content:center;gap:9px">
          <i class="fa-solid fa-hand-holding-dollar" style="font-size:13px"></i>
          Simpan Pembayaran &amp; Buat Pengeluaran
        </button>
      </div>
    </div>

  </form>

</div>

@push('scripts')
<script>
const employeeName = @json($employee->name);
const monthNames   = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const activeSalary = {{ $activeSalary?->amount ?? 0 }};

function updatePeriod() {
  const y = document.getElementById('pm-year').value;
  const m = document.getElementById('pm-month').value;
  document.getElementById('pm-hidden').value = y + '-' + m;
  const label = monthNames[parseInt(m) - 1] + ' ' + y;
  document.getElementById('prev-desc').textContent = 'Gaji ' + employeeName + ' - ' + label;
}

function updatePreview() {
  const val = parseInt(document.querySelector('[name="amount"]').value) || 0;
  document.getElementById('prev-amount').textContent = 'Rp ' + val.toLocaleString('id-ID');
}

function setActiveSalary() {
  document.querySelector('[name="amount"]').value = activeSalary;
  updatePreview();
}

document.addEventListener('DOMContentLoaded', () => {
  updatePeriod();
  updatePreview();
});
</script>
@endpush

</x-app-layout>
