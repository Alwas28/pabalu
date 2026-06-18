<x-app-layout>
<x-slot name="header">Detail Karyawan</x-slot>

<style>
.section-card { background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden; }
.section-head { padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
</style>

<div style="max-width:900px;margin:0 auto;display:flex;flex-direction:column;gap:20px">

  {{-- Back + actions header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <a href="{{ route('employees.index') }}"
      style="display:inline-flex;align-items:center;gap:7px;font-size:13px;color:var(--sub);text-decoration:none;font-weight:600">
      <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Semua Karyawan
    </a>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <form method="POST" action="{{ route('employees.toggle-active', $employee) }}" style="display:inline">
        @csrf
        <button type="submit"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:12px;border:none;cursor:pointer;font-size:13px;font-weight:600;
            background:{{ $employee->is_active ? 'rgba(239,68,68,.1)' : 'rgba(52,211,153,.1)' }};
            color:{{ $employee->is_active ? '#f87171' : '#34d399' }}">
          <i class="fa-solid fa-power-off" style="font-size:11px"></i>
          {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
      </form>
      <form method="POST" action="{{ route('employees.destroy', $employee) }}" style="display:inline"
        onsubmit="return confirm('Hapus karyawan {{ addslashes($employee->name) }}? Data tidak bisa dipulihkan.')">
        @csrf @method('DELETE')
        <button type="submit"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:12px;border:1px solid rgba(239,68,68,.3);background:transparent;color:#f87171;font-size:13px;font-weight:600;cursor:pointer">
          <i class="fa-solid fa-trash" style="font-size:11px"></i> Hapus
        </button>
      </form>
    </div>
  </div>

  @if(session('success'))
  <div style="padding:12px 16px;border-radius:12px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);color:#34d399;font-size:13.5px;font-weight:600">
    <i class="fa-solid fa-circle-check" style="margin-right:8px"></i>{{ session('success') }}
  </div>
  @endif
  @if(session('error') || $errors->has('account'))
  <div style="padding:12px 16px;border-radius:12px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;font-size:13.5px;font-weight:600">
    <i class="fa-solid fa-circle-xmark" style="margin-right:8px"></i>{{ session('error') ?? $errors->first('account') }}
  </div>
  @endif

  <div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">

    {{-- Left: photo + status --}}
    <div style="display:flex;flex-direction:column;gap:14px">
      <div class="section-card" style="padding:24px;text-align:center">
        @if($employee->photo)
          <img src="{{ asset('storage/'.$employee->photo) }}" alt="{{ $employee->name }}"
            style="width:100px;height:100px;border-radius:16px;object-fit:cover;margin:0 auto 14px">
        @else
          <div style="width:100px;height:100px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;font-size:36px;font-weight:700;color:var(--ac);margin:0 auto 14px">
            {{ strtoupper(substr($employee->name, 0, 1)) }}
          </div>
        @endif
        <h2 class="font-display" style="font-size:17px;font-weight:700;color:var(--text)">{{ $employee->name }}</h2>
        <p style="font-size:13px;color:var(--muted);margin-top:3px">{{ $employee->position ?? 'Tanpa jabatan' }}</p>
        <div style="margin-top:10px">
          <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:99px;font-size:11.5px;font-weight:700;
            background:{{ $employee->is_active ? 'rgba(52,211,153,.1)' : 'rgba(239,68,68,.1)' }};
            color:{{ $employee->is_active ? '#34d399' : '#f87171' }}">
            <i class="fa-solid fa-circle" style="font-size:7px"></i>
            {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
          </span>
        </div>
        @if($employee->join_date)
        <p style="font-size:11.5px;color:var(--muted);margin-top:10px">
          <i class="fa-solid fa-calendar-days" style="margin-right:4px"></i>
          Masuk {{ $employee->join_date->translatedFormat('d M Y') }}
        </p>
        @endif
      </div>

      {{-- Akun / Account section --}}
      <div class="section-card">
        <div class="section-head">
          <span class="font-display" style="font-size:14px;font-weight:700;color:var(--text)">
            <i class="fa-solid fa-circle-user" style="color:var(--ac);margin-right:7px;font-size:12px"></i>Akun Sistem
          </span>
        </div>
        <div style="padding:16px 18px">
          @if($employee->user)
            <div style="font-size:13px;color:var(--text);font-weight:600;margin-bottom:2px">{{ $employee->user->name }}</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:4px">{{ $employee->user->email }}</div>
            <div style="font-size:11.5px;color:var(--sub);margin-bottom:14px">
              <i class="fa-solid fa-circle-check" style="color:#34d399;margin-right:4px"></i>
              {{ $employee->user->role_label ?? 'Karyawan' }}
            </div>
            <button onclick="document.getElementById('modal-edit-account').classList.add('open')"
              style="width:100%;padding:9px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px"
              onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
              <i class="fa-solid fa-pen" style="font-size:11px"></i> Edit Akun
            </button>
          @else
            <p style="font-size:12.5px;color:var(--muted);margin-bottom:14px">Karyawan ini belum memiliki akun untuk login ke sistem.</p>
            <button onclick="document.getElementById('modal-account').classList.add('open')"
              style="width:100%;padding:9px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:'Clash Display',sans-serif">
              <i class="fa-solid fa-key" style="margin-right:6px;font-size:11px"></i>Buat Akun
            </button>
          @endif
        </div>
      </div>
    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:20px">

      {{-- Info pribadi --}}
      <div class="section-card">
        <div class="section-head">
          <span class="font-display" style="font-size:14px;font-weight:700;color:var(--text)">
            <i class="fa-solid fa-id-card" style="color:var(--ac);margin-right:7px;font-size:12px"></i>Data Karyawan
          </span>
          <button onclick="document.getElementById('modal-edit').classList.add('open')"
            style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:12px;font-weight:600;cursor:pointer">
            <i class="fa-solid fa-pen" style="font-size:10px"></i> Edit
          </button>
        </div>
        <table style="width:100%;border-collapse:collapse">
          <tbody>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:11px 18px;width:38%;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;white-space:nowrap">Nama Lengkap</td>
              <td style="padding:11px 18px;font-size:13.5px;color:var(--text);font-weight:600">{{ $employee->name }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:11px 18px;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px">Outlet</td>
              <td style="padding:11px 18px">
                @if($employee->outlet)
                  <span style="display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:600;color:var(--text)">
                    <i class="fa-solid fa-store" style="font-size:11px;color:var(--ac)"></i>
                    {{ $employee->outlet->name }}
                  </span>
                @else
                  <span style="color:var(--muted);font-size:13.5px">— belum ditentukan</span>
                @endif
              </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:11px 18px;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px">Jabatan</td>
              <td style="padding:11px 18px;font-size:13.5px;color:var(--text)">{{ $employee->position ?: '—' }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:11px 18px;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px">Status</td>
              <td style="padding:11px 18px">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:700;
                  background:{{ $employee->is_active ? 'rgba(52,211,153,.1)' : 'rgba(239,68,68,.1)' }};
                  color:{{ $employee->is_active ? '#34d399' : '#f87171' }}">
                  <i class="fa-solid fa-circle" style="font-size:6px"></i>
                  {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:11px 18px;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px">Tanggal Masuk</td>
              <td style="padding:11px 18px;font-size:13.5px;color:var(--text)">
                {{ $employee->join_date ? $employee->join_date->translatedFormat('d M Y') : '—' }}
              </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:11px 18px;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px">No. WhatsApp</td>
              <td style="padding:11px 18px">
                @if($employee->whatsapp)
                  <a href="https://wa.me/{{ preg_replace('/\D/', '', $employee->whatsapp) }}" target="_blank"
                    style="display:inline-flex;align-items:center;gap:6px;color:#34d399;text-decoration:none;font-size:13.5px;font-weight:600">
                    <i class="fa-brands fa-whatsapp"></i>{{ $employee->whatsapp }}
                  </a>
                @else
                  <span style="color:var(--muted);font-size:13.5px">—</span>
                @endif
              </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:11px 18px;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px">Pendidikan</td>
              <td style="padding:11px 18px">
                @if($employee->education_level)
                  <span style="display:inline-flex;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:700;background:rgba(99,102,241,.1);color:#818cf8">
                    {{ $employee->education_level }}
                  </span>
                @else
                  <span style="color:var(--muted);font-size:13.5px">—</span>
                @endif
              </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:11px 18px;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;vertical-align:top">Alamat</td>
              <td style="padding:11px 18px;font-size:13.5px;color:var(--text);white-space:pre-wrap">{{ $employee->address ?: '—' }}</td>
            </tr>
            <tr>
              <td style="padding:11px 18px;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;vertical-align:top">Catatan</td>
              <td style="padding:11px 18px;font-size:13.5px;color:var(--muted);white-space:pre-wrap">{{ $employee->notes ?: '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      {{-- Tab bar: Master Gaji / Riwayat Pembayaran --}}
      <div style="display:flex;gap:4px;background:var(--surface2);border-radius:12px;padding:4px;width:fit-content" id="salary-tab-bar">
        <button id="stab-master" onclick="switchSalaryTab('master')"
          style="padding:7px 18px;border-radius:9px;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:all .18s">
          <i class="fa-solid fa-money-bill-wave" style="margin-right:6px;font-size:11px"></i>Master Gaji
        </button>
        <button id="stab-payments" onclick="switchSalaryTab('payments')"
          style="padding:7px 18px;border-radius:9px;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:all .18s">
          <i class="fa-solid fa-hand-holding-dollar" style="margin-right:6px;font-size:11px"></i>Gaji Diterima
          @if($payments->count())
          <span style="margin-left:4px;background:var(--ac);color:#fff;font-size:10px;font-weight:800;padding:1px 6px;border-radius:99px">{{ $payments->count() }}</span>
          @endif
        </button>
      </div>

      {{-- ── TAB: Master Gaji ── --}}
      <div id="tab-master" class="section-card">
        <div class="section-head">
          <div>
            <span class="font-display" style="font-size:14px;font-weight:700;color:var(--text)">
              <i class="fa-solid fa-money-bill-wave" style="color:var(--ac);margin-right:7px;font-size:12px"></i>Master Gaji
            </span>
            <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Riwayat standar gaji &amp; referensi penggajian</div>
          </div>
          <button onclick="document.getElementById('modal-salary').classList.add('open')"
            style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;border:none;background:var(--ac-lt);color:var(--ac);font-size:12px;font-weight:700;cursor:pointer">
            <i class="fa-solid fa-plus" style="font-size:10px"></i> Tambah
          </button>
        </div>

        @php $activeSalary = $employee->salaries->firstWhere('is_active', true); @endphp

        @if($activeSalary)
        <div style="padding:14px 18px;background:rgba(var(--ac-rgb),.06);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px">
          <div style="flex:1">
            <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.6px;margin-bottom:3px">Gaji Aktif Saat Ini</div>
            <div style="font-size:22px;font-weight:800;color:var(--ac);font-family:'Clash Display',sans-serif">
              Rp {{ number_format($activeSalary->amount,0,',','.') }}
            </div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px">
              Berlaku sejak {{ $activeSalary->effective_date->translatedFormat('d M Y') }}
              @if($activeSalary->notes) &nbsp;·&nbsp; {{ $activeSalary->notes }} @endif
            </div>
          </div>
          <i class="fa-solid fa-circle-check" style="font-size:22px;color:var(--ac);opacity:.6"></i>
        </div>
        @else
        <div style="padding:12px 18px;background:rgba(245,158,11,.06);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;font-size:14px"></i>
          <span style="font-size:13px;color:#fbbf24;font-weight:600">Belum ada gaji aktif — klik ★ pada entri yang berlaku.</span>
        </div>
        @endif

        @if($employee->salaries->isEmpty())
        <div style="padding:40px;text-align:center">
          <i class="fa-solid fa-file-invoice-dollar" style="font-size:28px;color:var(--muted);opacity:.3;display:block;margin-bottom:10px"></i>
          <p style="font-size:13px;color:var(--muted)">Belum ada data master gaji. Tambahkan entri pertama.</p>
        </div>
        @else
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="background:var(--surface2)">
              <th style="padding:9px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Nominal</th>
              <th style="padding:9px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Berlaku Sejak</th>
              <th style="padding:9px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Keterangan</th>
              <th style="padding:9px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Status</th>
              <th style="padding:9px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--muted)"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($employee->salaries as $sal)
          <tr style="border-top:1px solid var(--border);{{ $sal->is_active ? 'background:rgba(var(--ac-rgb),.04)' : '' }}">
            <td style="padding:11px 16px;font-size:15px;font-weight:700;color:var(--text)">
              Rp {{ number_format($sal->amount,0,',','.') }}
            </td>
            <td style="padding:11px 16px;font-size:13px;color:var(--sub)">
              {{ $sal->effective_date->translatedFormat('d M Y') }}
            </td>
            <td style="padding:11px 16px;font-size:13px;color:var(--muted)">
              {{ $sal->notes ?: '—' }}
            </td>
            <td style="padding:11px 16px;text-align:center">
              @if($sal->is_active)
                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;background:rgba(var(--ac-rgb),.12);color:var(--ac);font-size:11.5px;font-weight:700">
                  <i class="fa-solid fa-circle" style="font-size:6px"></i> Aktif
                </span>
              @else
                <form method="POST" action="{{ route('employees.salaries.activate', [$employee, $sal]) }}" style="display:inline">
                  @csrf
                  <button type="submit" title="Jadikan gaji aktif"
                    style="padding:4px 10px;border-radius:99px;border:1px dashed var(--border);background:transparent;color:var(--muted);font-size:11.5px;font-weight:600;cursor:pointer"
                    onmouseover="this.style.borderColor='var(--ac)';this.style.color='var(--ac)'"
                    onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
                    <i class="fa-regular fa-star" style="font-size:11px;margin-right:3px"></i>Set Aktif
                  </button>
                </form>
              @endif
            </td>
            <td style="padding:11px 16px;text-align:right">
              <form method="POST" action="{{ route('employees.salaries.destroy', [$employee, $sal]) }}"
                onsubmit="return confirm('Hapus entri gaji ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                  style="padding:5px 9px;border-radius:8px;border:none;background:transparent;color:var(--muted);cursor:pointer;font-size:12px"
                  onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @endforeach
          </tbody>
        </table>
        @endif
      </div>

      {{-- ── TAB: Riwayat Gaji Diterima ── --}}
      <div id="tab-payments" class="section-card" style="display:none">
        <div class="section-head">
          <div>
            <span class="font-display" style="font-size:14px;font-weight:700;color:var(--text)">
              <i class="fa-solid fa-hand-holding-dollar" style="color:#34d399;margin-right:7px;font-size:12px"></i>Riwayat Gaji Diterima
            </span>
            <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Catatan realisasi pembayaran gaji</div>
          </div>
          <a href="{{ route('employees.salary-payments.create', $employee) }}"
            style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;border:none;background:rgba(52,211,153,.12);color:#34d399;font-size:12px;font-weight:700;text-decoration:none">
            <i class="fa-solid fa-plus" style="font-size:10px"></i> Catat Pembayaran
          </a>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('employees.show', $employee) }}"
          style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;background:var(--surface2)">
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
          <a href="{{ route('employees.show', $employee) }}?tab=payments&pay_year={{ $payYear }}"
            style="padding:7px 12px;border-radius:10px;border:1px solid var(--border);color:var(--sub);font-size:12.5px;font-weight:600;text-decoration:none">Reset</a>
          @endif
          @if($payments->count())
          <span style="margin-left:auto;font-size:13px;font-weight:700;color:#34d399">
            Total: Rp {{ number_format($payments->sum('amount'),0,',','.') }}
          </span>
          @endif
        </form>

        {{-- Tabel --}}
        @if($payments->isEmpty())
        <div style="padding:40px;text-align:center">
          <i class="fa-solid fa-hand-holding-dollar" style="font-size:28px;color:var(--muted);opacity:.3;display:block;margin-bottom:10px"></i>
          <p style="font-size:13px;color:var(--muted)">Belum ada catatan pembayaran gaji{{ $payMonth ? ' pada bulan ini' : ' di tahun '.$payYear }}.</p>
        </div>
        @else
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="background:var(--surface2)">
              <th style="padding:9px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Tgl Bayar</th>
              <th style="padding:9px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Periode</th>
              <th style="padding:9px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Jumlah</th>
              <th style="padding:9px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Catatan</th>
              <th style="padding:9px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Dicatat oleh</th>
              <th style="padding:9px 16px"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($payments as $pay)
          <tr style="border-top:1px solid var(--border)">
            <td style="padding:11px 16px;font-size:13px;color:var(--sub)">
              {{ $pay->paid_at->translatedFormat('d M Y') }}
            </td>
            <td style="padding:11px 16px">
              <span style="display:inline-flex;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:600;background:rgba(52,211,153,.1);color:#34d399">
                {{ $pay->period_label }}
              </span>
            </td>
            <td style="padding:11px 16px;text-align:right;font-size:15px;font-weight:800;color:var(--text)">
              Rp {{ number_format($pay->amount,0,',','.') }}
            </td>
            <td style="padding:11px 16px;font-size:12.5px;color:var(--muted)">{{ $pay->notes ?: '—' }}</td>
            <td style="padding:11px 16px;font-size:12px;color:var(--muted)">{{ $pay->creator->name ?? '—' }}</td>
            <td style="padding:11px 16px;text-align:right">
              <form method="POST" action="{{ route('employees.salary-payments.destroy', [$employee, $pay]) }}"
                onsubmit="return confirm('Hapus catatan pembayaran ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                  style="padding:5px 9px;border-radius:8px;border:none;background:transparent;color:var(--muted);cursor:pointer;font-size:12px"
                  onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @endforeach
          </tbody>
          <tfoot>
            <tr style="border-top:2px solid var(--border);background:var(--surface2)">
              <td colspan="2" style="padding:10px 16px;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px">Total</td>
              <td style="padding:10px 16px;text-align:right;font-size:16px;font-weight:800;color:#34d399">
                Rp {{ number_format($payments->sum('amount'),0,',','.') }}
              </td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
        </table>
        @endif
      </div>

    </div>
  </div>

</div>

{{-- ════ MODAL EDIT ════ --}}
<div id="modal-edit" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:560px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-pen" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Edit Data Karyawan
      </h3>
      <button onclick="document.getElementById('modal-edit').classList.remove('open')"
        style="width:28px;height:28px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        @php
          $ownerOutletsEdit = match(auth()->user()->role) {
            'admin' => \App\Models\Outlet::orderBy('name')->get(),
            default => auth()->user()->outlets()->orderBy('name')->get(),
          };
        @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div style="grid-column:1/-1">
            <label class="f-label">Nama Lengkap <span style="color:#f87171">*</span></label>
            <input name="name" type="text" required class="f-input" value="{{ old('name', $employee->name) }}">
          </div>
          <div style="grid-column:1/-1">
            <label class="f-label">
              Outlet Tempat Bertugas
              <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--muted);margin-left:4px">— tempat keluarnya gaji</span>
            </label>
            <select name="outlet_id" class="f-input">
              <option value="">— Pilih Outlet —</option>
              @foreach($ownerOutletsEdit as $ol)
              <option value="{{ $ol->id }}" {{ old('outlet_id', $employee->outlet_id) == $ol->id ? 'selected' : '' }}>
                {{ $ol->name }}
              </option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="f-label">Jabatan / Posisi</label>
            <input name="position" type="text" class="f-input" value="{{ old('position', $employee->position) }}">
          </div>
          <div>
            <label class="f-label">No. WhatsApp</label>
            <input name="whatsapp" type="text" class="f-input" value="{{ old('whatsapp', $employee->whatsapp) }}">
          </div>
          <div>
            <label class="f-label">Pendidikan Terakhir</label>
            <select name="education_level" class="f-input">
              <option value="">-- Pilih --</option>
              @foreach(['SD','SMP','SMA','D3','S1','S2','S3'] as $edu)
              <option value="{{ $edu }}" {{ old('education_level', $employee->education_level) === $edu ? 'selected' : '' }}>{{ $edu }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="f-label">Tanggal Masuk</label>
            <input name="join_date" type="date" class="f-input"
              value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}">
          </div>
          <div style="grid-column:1/-1">
            <label class="f-label">Alamat</label>
            <textarea name="address" class="f-input" rows="2" style="resize:vertical">{{ old('address', $employee->address) }}</textarea>
          </div>
          <div style="grid-column:1/-1">
            <label class="f-label">Catatan</label>
            <textarea name="notes" class="f-input" rows="2" style="resize:vertical">{{ old('notes', $employee->notes) }}</textarea>
          </div>
          <div style="grid-column:1/-1">
            <label class="f-label">Foto Baru</label>
            @if($employee->photo)
            <div style="margin-bottom:8px">
              <img src="{{ asset('storage/'.$employee->photo) }}" style="width:60px;height:60px;border-radius:10px;object-fit:cover">
            </div>
            @endif
            <input name="photo" type="file" accept="image/*" class="f-input" style="padding:6px">
            <p style="font-size:11px;color:var(--muted);margin-top:4px">Kosongkan jika tidak ingin mengganti foto</p>
          </div>
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
        <button type="button" onclick="document.getElementById('modal-edit').classList.remove('open')"
          style="flex:0 0 auto;padding:10px 18px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px;font-size:12px"></i>Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ════ MODAL TAMBAH GAJI ════ --}}
<div id="modal-salary" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:440px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-money-bill-wave" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tambah Riwayat Gaji
      </h3>
      <button onclick="document.getElementById('modal-salary').classList.remove('open')"
        style="width:28px;height:28px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('employees.salaries.store', $employee) }}">
      @csrf
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
        <div style="padding:10px 14px;border-radius:10px;background:var(--surface2);font-size:12.5px;color:var(--muted)">
          <i class="fa-solid fa-circle-info" style="color:var(--ac);margin-right:5px"></i>
          Catat standar gaji karyawan. Tandai sebagai <strong style="color:var(--text)">Aktif</strong> jika ini adalah gaji yang berlaku sekarang.
        </div>
        <div>
          <label class="f-label">Nominal Gaji (Rp) <span style="color:#f87171">*</span></label>
          <input name="amount" type="number" required min="1" class="f-input" placeholder="cth: 3000000">
        </div>
        <div>
          <label class="f-label">Berlaku Sejak <span style="color:#f87171">*</span></label>
          <input name="effective_date" type="date" required class="f-input" value="{{ date('Y-m-d') }}">
        </div>
        <div>
          <label class="f-label">Keterangan</label>
          <input name="notes" type="text" class="f-input" placeholder="cth: Kenaikan gaji tahun 2026">
        </div>
        <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;border:1px solid var(--border);cursor:pointer"
          onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
          <input type="checkbox" name="is_active" value="1"
            style="width:18px;height:18px;accent-color:var(--ac);cursor:pointer;flex-shrink:0">
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--text)">Jadikan gaji aktif</div>
            <div style="font-size:11.5px;color:var(--muted)">Entri sebelumnya yang aktif akan otomatis dinonaktifkan</div>
          </div>
        </label>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
        <button type="button" onclick="document.getElementById('modal-salary').classList.remove('open')"
          style="flex:0 0 auto;padding:10px 18px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px;font-size:12px"></i>Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ════ MODAL EDIT AKUN ════ --}}
@if($employee->user_id)
@php
  $ownerOutlets = match(auth()->user()->role) {
    'admin' => \App\Models\Outlet::orderBy('name')->get(),
    default => auth()->user()->outlets()->orderBy('name')->get(),
  };
  $currentOutletIds = $employee->user?->assignedOutlets->pluck('id')->toArray() ?? [];
@endphp
<div id="modal-edit-account" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:440px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-pen" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Edit Akun Karyawan
      </h3>
      <button onclick="document.getElementById('modal-edit-account').classList.remove('open')"
        style="width:28px;height:28px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('employees.account.update', $employee) }}">
      @csrf @method('PUT')
      <input type="hidden" name="_form" value="edit_account">
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        <div>
          <label class="f-label">Role / Akses <span style="color:#f87171">*</span></label>
          <select name="role" required class="f-input">
            <option value="kasir"        {{ ($employee->user->role ?? '') === 'kasir'        ? 'selected' : '' }}>Kasir — POS, stok harian, pengeluaran</option>
            <option value="admin_outlet" {{ ($employee->user->role ?? '') === 'admin_outlet' ? 'selected' : '' }}>Admin Outlet — Semua operasional + produk &amp; laporan</option>
          </select>
        </div>
        <div>
          <label class="f-label">Email Login <span style="color:#f87171">*</span></label>
          <input name="email" type="email" required class="f-input"
            value="{{ old('account_email', $employee->user->email) }}">
          @error('account_email') <p style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="f-label">Password Baru</label>
          <input name="password" type="password" class="f-input" placeholder="Kosongkan jika tidak diubah">
          @error('account_password') <p style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="f-label">Akses Outlet <span style="color:#f87171">*</span></label>
          <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden">
            @forelse($ownerOutlets as $ot)
            <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}cursor:pointer"
              onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
              <input type="checkbox" name="outlet_ids[]" value="{{ $ot->id }}"
                style="width:16px;height:16px;accent-color:var(--ac);cursor:pointer"
                {{ in_array($ot->id, old('outlet_ids', $currentOutletIds)) ? 'checked' : '' }}>
              <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $ot->name }}</div>
                @if($ot->address)<div style="font-size:11px;color:var(--muted)">{{ $ot->address }}</div>@endif
              </div>
            </label>
            @empty
            <div style="padding:14px;font-size:13px;color:var(--muted)">Tidak ada outlet tersedia.</div>
            @endforelse
          </div>
          @error('outlet_ids') <p style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</p> @enderror
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
        <button type="button" onclick="document.getElementById('modal-edit-account').classList.remove('open')"
          style="flex:0 0 auto;padding:10px 18px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px;font-size:12px"></i>Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- ════ MODAL BUAT AKUN ════ --}}
@if(!$employee->user_id)
<div id="modal-account" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:440px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-key" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Buat Akun untuk {{ $employee->name }}
      </h3>
      <button onclick="document.getElementById('modal-account').classList.remove('open')"
        style="width:28px;height:28px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('employees.account.create', $employee) }}">
      @csrf
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Role / Akses <span style="color:#f87171">*</span></label>
          <select name="role" required class="f-input">
            <option value="kasir" {{ old('role') === 'kasir' ? 'selected' : '' }}>Kasir — POS, stok harian, pengeluaran</option>
            <option value="admin_outlet" {{ old('role') === 'admin_outlet' ? 'selected' : '' }}>Admin Outlet — Semua operasional + produk & laporan</option>
          </select>
        </div>
        <div>
          <label class="f-label">Email Login <span style="color:#f87171">*</span></label>
          <input name="email" type="email" required class="f-input" placeholder="email@contoh.com"
            value="{{ old('email') }}">
          @error('email') <p style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="f-label">Password <span style="color:#f87171">*</span></label>
          <input name="password" type="password" required class="f-input" placeholder="Min. 6 karakter">
          @error('password') <p style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="f-label">Akses Outlet <span style="color:#f87171">*</span></label>
          @php
            $ownerOutlets = match(auth()->user()->role) {
              'admin' => \App\Models\Outlet::orderBy('name')->get(),
              default => auth()->user()->outlets()->orderBy('name')->get(),
            };
          @endphp
          <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden">
            @forelse($ownerOutlets as $ot)
            <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}cursor:pointer"
              onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
              <input type="checkbox" name="outlet_ids[]" value="{{ $ot->id }}"
                style="width:16px;height:16px;accent-color:var(--ac);cursor:pointer"
                {{ in_array($ot->id, old('outlet_ids', [])) ? 'checked' : '' }}>
              <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $ot->name }}</div>
                @if($ot->address)<div style="font-size:11px;color:var(--muted)">{{ $ot->address }}</div>@endif
              </div>
            </label>
            @empty
            <div style="padding:14px;font-size:13px;color:var(--muted)">Tidak ada outlet tersedia.</div>
            @endforelse
          </div>
          @error('outlet_ids') <p style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</p> @enderror
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
        <button type="button" onclick="document.getElementById('modal-account').classList.remove('open')"
          style="flex:0 0 auto;padding:10px 18px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
          <i class="fa-solid fa-key" style="margin-right:6px;font-size:12px"></i>Buat Akun
        </button>
      </div>
    </form>
  </div>
</div>
@endif

@push('scripts')
<script>
function switchSalaryTab(tab) {
  ['master','payments'].forEach(t => {
    document.getElementById('tab-' + t).style.display = t === tab ? '' : 'none';
    const btn = document.getElementById('stab-' + t);
    if (t === tab) {
      btn.style.background = 'var(--surface)';
      btn.style.color      = 'var(--ac)';
      btn.style.boxShadow  = '0 1px 4px rgba(0,0,0,.18)';
    } else {
      btn.style.background = 'transparent';
      btn.style.color      = 'var(--sub)';
      btn.style.boxShadow  = 'none';
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  switchSalaryTab('{{ $activeTab }}');

  @if($errors->any())
    @if(old('_form') === 'edit_account')
      const ea = document.getElementById('modal-edit-account');
      if (ea) ea.classList.add('open');
    @elseif(old('_tab') === 'payments')
      switchSalaryTab('payments');
    @else
      const ma = document.getElementById('modal-account');
      if (ma) ma.classList.add('open');
    @endif
  @endif
});
</script>
@endpush

</x-app-layout>
