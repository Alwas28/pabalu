@php
  $authRole    = Auth::user()->role;
  $isAdmin     = $authRole === 'admin';
  $roleOptions = [
    'admin'        => ['label' => 'Administrator',  'icon' => 'fa-shield-halved', 'desc' => 'Akses penuh sistem — email otomatis terverifikasi'],
    'owner'        => ['label' => 'Pemilik Toko',   'icon' => 'fa-store',         'desc' => 'Kelola outlet, produk, user, dan laporan'],
    'admin_outlet' => ['label' => 'Admin Outlet',   'icon' => 'fa-user-tie',      'desc' => 'Operasional harian + laporan outlet sendiri'],
    'kasir'        => ['label' => 'Kasir',           'icon' => 'fa-cash-register', 'desc' => 'POS, stok harian, pengeluaran, antrian order'],
  ];
@endphp

<x-app-layout>
<x-slot name="pageTitle">Tambah User</x-slot>
<x-slot name="headerAction">
  <a href="{{ route('users.index') }}"
    style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
    <i class="fa-solid fa-arrow-left"></i><span>Kembali</span>
  </a>
</x-slot>

<div style="max-width:680px" class="animate-fadeUp">
  <form method="POST" action="{{ route('users.store') }}">
    @csrf

    {{-- Info Card --}}
    <div style="background:var(--ac-lt2);border:1px solid var(--ac-lt);border-radius:14px;padding:14px 18px;margin-bottom:20px;display:flex;gap:12px;align-items:flex-start">
      <i class="fa-solid fa-circle-info a-text" style="margin-top:2px;flex-shrink:0"></i>
      <div style="font-size:13px;color:var(--sub);line-height:1.6">
        User baru harus <strong style="color:var(--text)">verifikasi email</strong> sebelum dapat menggunakan sistem.
        Khusus role <strong style="color:var(--text)">Administrator</strong>, email akan otomatis terverifikasi.
      </div>
    </div>

    {{-- Form Card --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-user-plus a-text" style="margin-right:8px"></i>Data User Baru
        </div>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:18px">

        {{-- Nama (Administrator/Pemilik Toko — tidak terhubung karyawan) --}}
        <div id="name-field">
          <label class="f-label" for="name">Nama Lengkap <span style="color:#f87171">*</span></label>
          <input id="name" name="name" type="text" class="f-input"
            value="{{ old('name') }}"
            placeholder="Nama lengkap user..."
            autocomplete="off">
          @error('name')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        {{-- Cari Karyawan (Kasir/Admin Outlet — menggantikan input nama, nama & outlet
             akun otomatis ikut data karyawan yang dipilih) --}}
        @php
          $selectedEmployee = $employees->firstWhere('id', (int) old('employee_id'));
        @endphp
        <div id="employee-search-field" style="display:none;position:relative">
          <label class="f-label" for="employee-search">Cari Karyawan <span style="color:#f87171">*</span></label>
          <input type="text" id="employee-search" class="f-input" autocomplete="off"
            placeholder="Ketik nama karyawan..."
            value="{{ $selectedEmployee->name ?? '' }}">
          <input type="hidden" name="employee_id" id="employee_id" value="{{ old('employee_id') }}">
          <div id="employee-results" style="display:none;position:absolute;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow-y:auto;background:var(--surface);border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);z-index:20"></div>
          <p style="font-size:11px;color:var(--muted);margin-top:5px">Cuma menampilkan karyawan yang belum punya akun login. Nama &amp; akses outlet akun otomatis mengikuti data karyawan yang dipilih.</p>
          @error('employee_id')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        {{-- Email --}}
        <div>
          <label class="f-label" for="email">Alamat Email <span style="color:#f87171">*</span></label>
          <input id="email" name="email" type="email" class="f-input"
            value="{{ old('email') }}"
            placeholder="user@domain.com"
            autocomplete="off">
          @error('email')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        {{-- Role --}}
        <div>
          <label class="f-label">Role <span style="color:#f87171">*</span></label>
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:6px" id="role-cards">
            @foreach($allowedRoles as $roleKey)
            @if(isset($roleOptions[$roleKey]))
            @php $opt = $roleOptions[$roleKey]; @endphp
            <label style="cursor:pointer">
              <input type="radio" name="role" value="{{ $roleKey }}"
                {{ old('role') === $roleKey ? 'checked' : '' }}
                onchange="onRoleChange(this)"
                style="display:none">
              <div class="role-card{{ old('role') === $roleKey ? ' role-card-active' : '' }}"
                data-role="{{ $roleKey }}"
                style="border:1.5px solid var(--border);border-radius:12px;padding:14px;display:flex;align-items:flex-start;gap:10px;transition:border-color .2s,background .2s">
                <div style="width:34px;height:34px;border-radius:9px;background:var(--surface2);display:grid;place-items:center;flex-shrink:0;font-size:14px;color:var(--sub);transition:background .2s,color .2s" class="role-icon">
                  <i class="fa-solid {{ $opt['icon'] }}"></i>
                </div>
                <div>
                  <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $opt['label'] }}</div>
                  <div style="font-size:11.5px;color:var(--muted);margin-top:2px;line-height:1.4">{{ $opt['desc'] }}</div>
                  @if($roleKey === 'admin')
                  <div style="margin-top:6px;display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:99px;background:rgba(16,185,129,.14);color:#34d399;font-size:10.5px;font-weight:600">
                    <i class="fa-solid fa-circle-check" style="font-size:9px"></i> Auto-verified
                  </div>
                  @endif
                </div>
              </div>
            </label>
            @endif
            @endforeach
          </div>
          @error('role')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        {{-- Password --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div>
            <label class="f-label" for="password">Password <span style="color:#f87171">*</span></label>
            <div style="position:relative">
              <input id="password" name="password" type="password" class="f-input"
                placeholder="Min. 8 karakter"
                style="padding-right:40px">
              <button type="button" onclick="togglePass('password','eye-pw')"
                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px">
                <i class="fa-solid fa-eye" id="eye-pw"></i>
              </button>
            </div>
            @error('password')
              <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="f-label" for="password_confirmation">Konfirmasi Password <span style="color:#f87171">*</span></label>
            <div style="position:relative">
              <input id="password_confirmation" name="password_confirmation" type="password" class="f-input"
                placeholder="Ulangi password"
                style="padding-right:40px">
              <button type="button" onclick="togglePass('password_confirmation','eye-pc')"
                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px">
                <i class="fa-solid fa-eye" id="eye-pc"></i>
              </button>
            </div>
          </div>
        </div>

      </div>

      {{-- Footer --}}
      <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ route('users.index') }}"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
          Batal
        </a>
        <button type="submit"
          class="a-grad" style="padding:9px 22px;border-radius:11px;border:none;color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px">
          <i class="fa-solid fa-user-plus"></i> Tambah User
        </button>
      </div>
    </div>

  </form>
</div>

@push('scripts')
<script>
function onRoleChange(input) {
  document.querySelectorAll('.role-card').forEach(card => {
    card.style.borderColor = 'var(--border)';
    card.style.background  = 'transparent';
    card.querySelector('.role-icon').style.background = 'var(--surface2)';
    card.querySelector('.role-icon').style.color = 'var(--sub)';
  });
  const active = input.closest('label').querySelector('.role-card');
  active.style.borderColor = 'var(--ac)';
  active.style.background  = 'var(--ac-lt)';
  active.querySelector('.role-icon').style.background = 'var(--ac-lt)';
  active.querySelector('.role-icon').style.color = 'var(--ac)';

  // Role operasional (kasir/admin_outlet) WAJIB terhubung ke data karyawan — nama
  // diambil dari situ, jadi input Nama diganti field cari karyawan. Admin/Pemilik Toko
  // tidak punya konsep karyawan, tetap pakai input nama manual.
  const isEmployeeRole = ['kasir', 'admin_outlet'].includes(input.value);
  document.getElementById('name-field').style.display = isEmployeeRole ? 'none' : '';
  document.getElementById('employee-search-field').style.display = isEmployeeRole ? '' : 'none';
  if (isEmployeeRole) {
    document.getElementById('name').value = '';
  } else {
    document.getElementById('employee-search').value = '';
    document.getElementById('employee_id').value = '';
  }
}

const EMPLOYEES = @json($employees->map(fn ($e) => ['id' => $e->id, 'name' => $e->name, 'position' => $e->position]));

function renderEmployeeResults(filter) {
  const box = document.getElementById('employee-results');
  const q = filter.trim().toLowerCase();
  const matches = q === '' ? EMPLOYEES : EMPLOYEES.filter(e => e.name.toLowerCase().includes(q));

  if (matches.length === 0) {
    box.innerHTML = '<div style="padding:10px 14px;font-size:12.5px;color:var(--muted)">Tidak ada karyawan cocok / semua karyawan sudah punya akun.</div>';
  } else {
    box.innerHTML = matches.map(e => `
      <div class="employee-result-item" data-id="${e.id}" data-name="${e.name.replace(/"/g, '&quot;')}"
        style="padding:10px 14px;font-size:13px;color:var(--text);cursor:pointer;border-bottom:1px solid var(--border)">
        ${e.name}${e.position ? ' <span style="color:var(--muted);font-size:11px">— ' + e.position + '</span>' : ''}
      </div>
    `).join('');
    box.querySelectorAll('.employee-result-item').forEach(item => {
      item.addEventListener('mouseover', () => item.style.background = 'var(--surface2)');
      item.addEventListener('mouseout', () => item.style.background = 'transparent');
      item.addEventListener('click', () => {
        document.getElementById('employee-search').value = item.dataset.name;
        document.getElementById('employee_id').value = item.dataset.id;
        box.style.display = 'none';
      });
    });
  }
  box.style.display = '';
}

document.getElementById('employee-search').addEventListener('input', (e) => {
  document.getElementById('employee_id').value = '';
  renderEmployeeResults(e.target.value);
});
document.getElementById('employee-search').addEventListener('focus', (e) => renderEmployeeResults(e.target.value));
document.addEventListener('click', (e) => {
  if (!e.target.closest('#employee-search-field')) {
    document.getElementById('employee-results').style.display = 'none';
  }
});

function togglePass(fieldId, iconId) {
  const f = document.getElementById(fieldId);
  const i = document.getElementById(iconId);
  if (f.type === 'password') {
    f.type = 'text';
    i.className = 'fa-solid fa-eye-slash';
  } else {
    f.type = 'password';
    i.className = 'fa-solid fa-eye';
  }
}

// Init active role card state on page load
document.querySelectorAll('input[name="role"]:checked').forEach(input => onRoleChange(input));
</script>
@endpush

</x-app-layout>
