<x-outlet-layout :outlet="$outlet" pageTitle="Semua Pelanggan">

<div class="card animate-fadeUp">
  <div class="card-header" style="flex-wrap:wrap;gap:10px">
    <span class="card-title">Semua Pelanggan</span>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <form method="GET" action="{{ $outlet->route('customers.index') }}" style="display:flex">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, HP, atau email..."
          class="f-input" style="width:230px;border-radius:10px 0 0 10px">
        <button type="submit"
          style="padding:0 14px;border-radius:0 10px 10px 0;border:1px solid var(--border);border-left:none;background:var(--surface2);color:var(--sub);cursor:pointer">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>
      <a href="{{ $outlet->route('customers.create') }}"
        style="padding:9px 16px;border-radius:9px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px;text-decoration:none;white-space:nowrap">
        <i class="fa-solid fa-user-plus"></i> Tambah Pelanggan
      </a>
    </div>
  </div>
  <div class="card-body">

    @if($customers->isEmpty())
    <div style="text-align:center;padding:44px 20px;color:var(--muted);font-size:13px;border:1px dashed var(--border);border-radius:12px">
      <i class="fa-solid fa-users" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px"></i>
      @if($q !== '')
        Tidak ada pelanggan yang cocok dengan pencarian "{{ $q }}".
      @else
        Belum ada pelanggan. Klik "Tambah Pelanggan" untuk mendaftarkan pelanggan baru.
      @endif
    </div>
    @else
    <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Nama</th>
          <th>No. HP</th>
          <th>Email</th>
          <th>Kota</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($customers as $customer)
        <tr>
          <td class="td-main">
            <a href="{{ $outlet->route('customers.show', [$customer]) }}" style="color:var(--ac);font-weight:600;text-decoration:none">
              {{ $customer->name }}
            </a>
          </td>
          <td>{{ $customer->phone ?: '—' }}</td>
          <td>{{ $customer->email ?: '—' }}</td>
          <td>{{ $customer->city ?: '—' }}</td>
          <td style="text-align:right;white-space:nowrap">
            <button type="button" onclick="openEdit({{ $customer->id }}, {{ json_encode($customer->only(['name','phone','email','address','city','birth_date','gender','notes'])) }})"
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button type="button" onclick="confirmDelete({{ $customer->id }}, {{ json_encode($customer->name) }})"
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:#f87171;font-size:12px;margin-left:4px">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <div style="margin-top:16px">{{ $customers->links() }}</div>
    @endif

  </div>
</div>

{{-- Modal Edit --}}
<div class="modal-backdrop" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal-box" style="max-width:560px;max-height:88vh;overflow-y:auto">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <span class="card-title">Edit Pelanggan</span>
      <button onclick="closeModal('modal-edit')" type="button"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:14px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" id="edit-form" action="">
      @csrf
      @method('PATCH')
      @include('sewa.customers._form', ['prefix' => 'e-', 'useOld' => false])
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit" class="btn-save">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Hapus --}}
<div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px 24px 20px;text-align:center">
      <div style="width:54px;height:54px;border-radius:14px;background:rgba(239,68,68,.12);color:#f87171;display:grid;place-items:center;margin:0 auto 14px;font-size:22px">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Pelanggan?</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.65">
        Pelanggan <strong id="delete-name" style="color:var(--text)"></strong> akan dihapus permanen.
      </p>
    </div>
    <form method="POST" id="delete-form" action="">
      @csrf
      @method('DELETE')
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-trash-can" style="margin-right:6px"></i>Ya, Hapus
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openEdit(id, data) {
  document.getElementById('edit-form').action = '{{ $outlet->route('customers.index') }}/' + id;
  document.getElementById('e-name').value       = data.name || '';
  document.getElementById('e-phone').value      = data.phone || '';
  document.getElementById('e-email').value      = data.email || '';
  document.getElementById('e-address').value    = data.address || '';
  document.getElementById('e-city').value       = data.city || '';
  document.getElementById('e-birth_date').value = data.birth_date || '';
  document.getElementById('e-gender').value     = data.gender || '';
  document.getElementById('e-notes').value      = data.notes || '';
  openModal('modal-edit');
}

function confirmDelete(id, name) {
  document.getElementById('delete-form').action = '{{ $outlet->route('customers.index') }}/' + id;
  document.getElementById('delete-name').textContent = name;
  openModal('modal-delete');
}
</script>
@endpush

</x-outlet-layout>
