<x-outlet-layout :outlet="$outlet" pageTitle="Tambah Pelanggan">

<div style="max-width:640px">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-size:13px;color:var(--muted)">
    <a href="{{ $outlet->route('customers.index') }}" style="color:var(--muted);text-decoration:none">
      <i class="fa-solid fa-users"></i> Semua Pelanggan
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">Tambah Pelanggan</span>
  </div>

  <div class="card animate-fadeUp">
    <div class="card-header">
      <span class="card-title">Data Pelanggan Baru</span>
    </div>
    <form method="POST" action="{{ $outlet->route('customers.store') }}">
      @csrf
      @include('sewa.customers._form', ['prefix' => ''])
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ $outlet->route('customers.index') }}"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
          Batal
        </a>
        <button type="submit" class="btn-save">
          <i class="fa-solid fa-user-plus" style="margin-right:6px"></i>Simpan Pelanggan
        </button>
      </div>
    </form>
  </div>

</div>

</x-outlet-layout>
