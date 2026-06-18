<x-app-layout>
<x-slot name="header">Riwayat Gaji Saya</x-slot>

@php
  $activeSalary = $employee?->salaries->firstWhere('is_active', true);
@endphp

<div style="max-width:860px;margin:0 auto;display:flex;flex-direction:column;gap:20px">

  {{-- Header --}}
  <div>
    <h1 class="font-display" style="font-size:22px;font-weight:700;color:var(--text)">Riwayat Gaji Saya</h1>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">Informasi master gaji dan riwayat standar penggajian</p>
  </div>

  @if(!$employee)
  {{-- No employee record --}}
  <div style="padding:60px;text-align:center;background:var(--surface);border:1px solid var(--border);border-radius:16px">
    <i class="fa-solid fa-id-card" style="font-size:40px;color:var(--muted);opacity:.25;display:block;margin-bottom:16px"></i>
    <h3 class="font-display" style="font-size:16px;color:var(--text);margin-bottom:6px">Data Karyawan Tidak Ditemukan</h3>
    <p style="font-size:13px;color:var(--muted);max-width:380px;margin:0 auto">
      Akun Anda belum terhubung ke data karyawan. Hubungi pemilik toko atau administrator untuk menghubungkan akun Anda.
    </p>
  </div>

  @else

  {{-- Active salary banner --}}
  @if($activeSalary)
  <div style="background:linear-gradient(135deg,var(--ac),var(--ac2));border-radius:18px;padding:28px 32px;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px" class="animate-fadeUp">
    <div>
      <div style="font-size:11px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;opacity:.8;margin-bottom:6px">
        <i class="fa-solid fa-circle" style="font-size:7px;margin-right:5px"></i>Gaji Aktif Saat Ini
      </div>
      <div class="font-display" style="font-size:36px;font-weight:700;line-height:1">
        Rp {{ number_format($activeSalary->amount, 0, ',', '.') }}
      </div>
      @if($activeSalary->notes)
      <div style="font-size:13px;opacity:.8;margin-top:8px">{{ $activeSalary->notes }}</div>
      @endif
    </div>
    <div style="text-align:right">
      <div style="font-size:11px;opacity:.7;margin-bottom:4px">Berlaku sejak</div>
      <div style="font-size:16px;font-weight:700">{{ $activeSalary->effective_date->format('d M Y') }}</div>
      <div style="font-size:11px;opacity:.6;margin-top:6px">
        <i class="fa-solid fa-user" style="margin-right:4px"></i>{{ $employee->name }}
      </div>
    </div>
  </div>
  @else
  <div style="padding:20px 24px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);border-radius:14px;display:flex;align-items:center;gap:14px" class="animate-fadeUp">
    <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;font-size:20px;flex-shrink:0"></i>
    <div>
      <div style="font-size:14px;font-weight:600;color:var(--text)">Belum Ada Gaji Aktif</div>
      <div style="font-size:13px;color:var(--muted);margin-top:2px">Belum ada standar gaji yang diaktifkan. Hubungi pemilik toko.</div>
    </div>
  </div>
  @endif

  {{-- Employee info card --}}
  <div class="card animate-fadeUp d1">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-id-badge a-text" style="margin-right:8px"></i>Informasi Karyawan</div>
    </div>
    <div style="padding:20px 24px">
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        @if($employee->photo)
          <img src="{{ asset('storage/'.$employee->photo) }}" alt="{{ $employee->name }}"
            style="width:64px;height:64px;border-radius:14px;object-fit:cover;flex-shrink:0;border:2px solid var(--border)">
        @else
          <div style="width:64px;height:64px;border-radius:14px;background:var(--ac-lt);display:grid;place-items:center;font-size:26px;font-weight:700;color:var(--ac);flex-shrink:0">
            {{ strtoupper(substr($employee->name, 0, 1)) }}
          </div>
        @endif
        <div style="flex:1;min-width:180px">
          <div style="font-size:18px;font-weight:700;color:var(--text);margin-bottom:4px">{{ $employee->name }}</div>
          @if($employee->position)
          <div style="font-size:13px;color:var(--muted);margin-bottom:6px">
            <i class="fa-solid fa-briefcase" style="font-size:10px;margin-right:5px"></i>{{ $employee->position }}
          </div>
          @endif
          <div style="display:flex;flex-wrap:wrap;gap:8px">
            @if($employee->join_date)
            <span style="font-size:12px;color:var(--sub);background:var(--surface2);padding:3px 10px;border-radius:8px;border:1px solid var(--border)">
              <i class="fa-solid fa-calendar-check" style="font-size:9px;margin-right:4px;color:var(--ac)"></i>
              Bergabung {{ $employee->join_date->format('d M Y') }}
            </span>
            @endif
            @if($employee->education_level)
            <span style="font-size:12px;color:#818cf8;background:rgba(99,102,241,.1);padding:3px 10px;border-radius:8px">
              {{ $employee->education_level }}
            </span>
            @endif
            <span style="font-size:12px;padding:3px 10px;border-radius:8px;
              background:{{ $employee->is_active ? 'rgba(16,185,129,.12)' : 'rgba(239,68,68,.1)' }};
              color:{{ $employee->is_active ? '#34d399' : '#f87171' }}">
              <i class="fa-solid fa-circle" style="font-size:7px;margin-right:4px"></i>
              {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Tab bar --}}
  <div style="display:flex;gap:4px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:4px;width:fit-content" class="animate-fadeUp d2">
    <button id="stab-master" onclick="switchTab('master')"
      style="padding:8px 20px;border-radius:9px;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:all .18s">
      <i class="fa-solid fa-money-bill-wave" style="margin-right:6px;font-size:11px"></i>Master Gaji
    </button>
    <button id="stab-payments" onclick="switchTab('payments')"
      style="padding:8px 20px;border-radius:9px;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:all .18s">
      <i class="fa-solid fa-hand-holding-dollar" style="margin-right:6px;font-size:11px"></i>Gaji Diterima
      @if($payments->count())
      <span style="margin-left:4px;background:var(--ac);color:#fff;font-size:10px;font-weight:800;padding:1px 6px;border-radius:99px">{{ $payments->count() }}</span>
      @endif
    </button>
  </div>

  {{-- ── TAB: Master Gaji ── --}}
  <div id="tab-master" class="card animate-fadeUp d3">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-clock-rotate-left a-text" style="margin-right:8px"></i>Riwayat Master Gaji
      </div>
      <span style="font-size:12px;color:var(--muted)">{{ $employee->salaries->count() }} entri</span>
    </div>

    @if($employee->salaries->isEmpty())
    <div style="padding:48px;text-align:center">
      <i class="fa-solid fa-hand-holding-dollar" style="font-size:32px;color:var(--muted);opacity:.25;display:block;margin-bottom:12px"></i>
      <p style="font-size:13px;color:var(--muted)">Belum ada riwayat gaji yang tercatat.</p>
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Berlaku Sejak</th>
            <th style="text-align:right">Jumlah Gaji</th>
            <th>Catatan</th>
            <th style="text-align:center">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($employee->salaries as $salary)
          <tr style="{{ $salary->is_active ? 'background:rgba(var(--ac-rgb),.04)' : '' }}">
            <td class="td-main">
              <div style="display:flex;align-items:center;gap:8px">
                @if($salary->is_active)
                  <span style="width:7px;height:7px;border-radius:50%;background:var(--ac);flex-shrink:0;display:inline-block"></span>
                @else
                  <span style="width:7px;height:7px;border-radius:50%;background:var(--border);flex-shrink:0;display:inline-block"></span>
                @endif
                {{ $salary->effective_date->format('d M Y') }}
              </div>
            </td>
            <td style="text-align:right;font-weight:700;color:{{ $salary->is_active ? 'var(--ac)' : 'var(--text)' }};font-size:14px">
              Rp {{ number_format($salary->amount, 0, ',', '.') }}
            </td>
            <td style="font-size:12.5px">{{ $salary->notes ?: '—' }}</td>
            <td style="text-align:center">
              @if($salary->is_active)
                <span class="badge badge-green">
                  <i class="fa-solid fa-circle-check" style="font-size:9px"></i> Aktif
                </span>
              @else
                <span class="badge badge-gray">Tidak Aktif</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>

  {{-- ── TAB: Riwayat Gaji Diterima ── --}}
  <div id="tab-payments" class="card animate-fadeUp d3" style="display:none">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-hand-holding-dollar" style="color:#34d399;margin-right:8px"></i>Gaji Diterima
      </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('my-salary') }}"
      style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;background:var(--surface2)">
      <input type="hidden" name="tab" value="payments">
      <div>
        <label class="f-label" style="font-size:11px">Tahun</label>
        <select name="pay_year" class="f-input" style="padding:7px 10px;font-size:13px;min-width:90px">
          @for($y = now()->year; $y >= now()->year - 5; $y--)
            <option value="{{ $y }}" @selected($payYear == $y)>{{ $y }}</option>
          @endfor
        </select>
      </div>
      <div>
        <label class="f-label" style="font-size:11px">Bulan</label>
        <select name="pay_month" class="f-input" style="padding:7px 10px;font-size:13px;min-width:110px">
          <option value="">Semua Bulan</option>
          @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $mi => $ml)
            <option value="{{ $mi + 1 }}" @selected($payMonth == $mi + 1)>{{ $ml }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" style="padding:7px 14px;border-radius:10px;border:none;background:var(--ac);color:#fff;font-size:12.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-filter" style="font-size:10px;margin-right:4px"></i>Filter
      </button>
      @if($payMonth)
      <a href="{{ route('my-salary') }}?tab=payments&pay_year={{ $payYear }}"
        style="padding:7px 12px;border-radius:10px;border:1px solid var(--border);color:var(--sub);font-size:12.5px;font-weight:600;text-decoration:none">Reset</a>
      @endif
      @if($payments->count())
      <span style="margin-left:auto;font-size:13px;font-weight:700;color:#34d399">
        Total: Rp {{ number_format($payments->sum('amount'),0,',','.') }}
      </span>
      @endif
    </form>

    @if($payments->isEmpty())
    <div style="padding:48px;text-align:center">
      <i class="fa-solid fa-calendar-xmark" style="font-size:32px;color:var(--muted);opacity:.25;display:block;margin-bottom:12px"></i>
      <p style="font-size:13px;color:var(--muted)">Belum ada catatan pembayaran gaji{{ $payMonth ? ' pada bulan ini' : ' di tahun '.$payYear }}.</p>
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tgl Bayar</th>
            <th>Periode</th>
            <th style="text-align:right">Jumlah</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payments as $pay)
          <tr>
            <td class="td-main">{{ $pay->paid_at->translatedFormat('d M Y') }}</td>
            <td>
              <span style="display:inline-flex;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:600;background:rgba(52,211,153,.1);color:#34d399">
                {{ $pay->period_label }}
              </span>
            </td>
            <td style="text-align:right;font-weight:800;font-size:15px;color:var(--text)">
              Rp {{ number_format($pay->amount,0,',','.') }}
            </td>
            <td style="font-size:12.5px;color:var(--muted)">{{ $pay->notes ?: '—' }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--border);background:var(--surface2)">
            <td colspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase">Total</td>
            <td style="padding:10px 14px;text-align:right;font-size:16px;font-weight:800;color:#34d399">
              Rp {{ number_format($payments->sum('amount'),0,',','.') }}
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
    @endif
  </div>

  @endif

@push('scripts')
<script>
function switchTab(tab) {
  ['master','payments'].forEach(t => {
    document.getElementById('tab-' + t).style.display = t === tab ? '' : 'none';
    const btn = document.getElementById('stab-' + t);
    if (t === tab) {
      btn.style.background = 'var(--surface2)';
      btn.style.color      = 'var(--ac)';
      btn.style.boxShadow  = '0 1px 4px rgba(0,0,0,.18)';
    } else {
      btn.style.background = 'transparent';
      btn.style.color      = 'var(--sub)';
      btn.style.boxShadow  = 'none';
    }
  });
}
document.addEventListener('DOMContentLoaded', () => switchTab('{{ $activeTab }}'));
</script>
@endpush

</div>
</x-app-layout>
