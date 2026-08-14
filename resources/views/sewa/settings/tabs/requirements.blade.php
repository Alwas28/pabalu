<div class="card animate-fadeUp">
  <div class="card-header">
    <span class="card-title">Persyaratan Dokumen</span>
    <button type="button" onclick="openReqModal('create')"
      style="padding:8px 14px;border-radius:9px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px">
      <i class="fa-solid fa-plus"></i> Tambah Persyaratan
    </button>
  </div>
  <div class="card-body">
    <p style="font-size:12.5px;color:var(--muted);margin-bottom:18px;line-height:1.7;max-width:640px">
      Tentukan dokumen apa saja yang diperlukan untuk transaksi rental pada outlet ini.
      <strong style="color:var(--text)">Wajib</strong> — pelanggan tidak dapat bertransaksi sebelum dokumen ini terverifikasi.
      <strong style="color:var(--text)">Opsional</strong> — boleh dilengkapi tapi tidak menghalangi transaksi.
    </p>

    @if($requirements->isEmpty())
    <div style="text-align:center;padding:40px 20px;color:var(--muted);font-size:13px;border:1px dashed var(--border);border-radius:12px">
      <i class="fa-solid fa-list-check" style="font-size:24px;opacity:.4;display:block;margin-bottom:10px"></i>
      Belum ada persyaratan dokumen. Klik "Tambah Persyaratan" untuk menambahkan.
    </div>
    @else
    <table class="tbl">
      <thead>
        <tr>
          <th>Nama Dokumen</th>
          <th>Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($requirements as $req)
        <tr>
          <td class="td-main">{{ $req->name }}</td>
          <td>
            @if($req->status === 'wajib')
            <span class="badge badge-red">Wajib</span>
            @else
            <span class="badge badge-gray">Opsional</span>
            @endif
          </td>
          <td style="text-align:right;white-space:nowrap">
            <button type="button" onclick="openReqModal('edit', {{ $req->id }}, {{ json_encode($req->name) }}, '{{ $req->status }}')"
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button type="button" onclick="confirmDeleteReq({{ $req->id }}, {{ json_encode($req->name) }})"
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:#f87171;font-size:12px;margin-left:4px">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>
</div>

{{-- Modal Tambah/Edit --}}
<div class="modal-backdrop" id="modal-req" onclick="if(event.target===this)closeModal('modal-req')">
  <div class="modal-box">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <span class="card-title" id="req-modal-title">Tambah Persyaratan</span>
      <button onclick="closeModal('modal-req')" type="button"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:14px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" id="req-form" action="{{ $outlet->route('settings.requirements.store') }}">
      @csrf
      <input type="hidden" name="_method" id="req-method" value="POST">

      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Nama Dokumen</label>
          <input type="text" name="name" id="req-name" class="f-input" placeholder="Contoh: KTP, SIM, Kartu Anggota..." required>
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px">
            @foreach($suggestedNames as $sug)
            <button type="button" onclick="document.getElementById('req-name').value='{{ $sug }}'"
              style="padding:5px 11px;border-radius:99px;font-size:11.5px;font-weight:600;border:1px solid var(--border);background:var(--surface2);color:var(--sub);cursor:pointer;font-family:inherit">
              {{ $sug }}
            </button>
            @endforeach
          </div>
          <div id="req-name-error" class="f-err" style="display:none;font-size:12px;color:#f87171;margin-top:5px"></div>
        </div>

        <div>
          <label class="f-label">Status</label>
          <div style="display:flex;gap:8px">
            <label class="req-chip" style="flex:1;text-align:center;padding:9px">
              <input type="radio" name="status" value="opsional" checked> Opsional
            </label>
            <label class="req-chip" style="flex:1;text-align:center;padding:9px">
              <input type="radio" name="status" value="wajib"> Wajib
            </label>
          </div>
        </div>
      </div>

      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-req')"
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

{{-- Modal Konfirmasi Hapus --}}
<div class="modal-backdrop" id="modal-req-delete" onclick="if(event.target===this)closeModal('modal-req-delete')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px 24px 20px;text-align:center">
      <div style="width:54px;height:54px;border-radius:14px;background:rgba(239,68,68,.12);color:#f87171;display:grid;place-items:center;margin:0 auto 14px;font-size:22px">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Persyaratan?</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.65">
        Persyaratan <strong id="req-delete-name" style="color:var(--text)"></strong> akan dihapus permanen.
      </p>
    </div>
    <form method="POST" id="req-delete-form" action="">
      @csrf
      @method('DELETE')
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-req-delete')"
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
const reqStoreUrl = '{{ $outlet->route('settings.requirements.store') }}';

function reqUpdateUrl(id) {
  return reqStoreUrl + '/' + id;
}

function openReqModal(mode, id = null, name = '', status = 'opsional') {
  const form   = document.getElementById('req-form');
  const title  = document.getElementById('req-modal-title');
  const method = document.getElementById('req-method');

  document.getElementById('req-name-error').style.display = 'none';

  if (mode === 'edit') {
    title.textContent = 'Edit Persyaratan';
    form.action = reqUpdateUrl(id);
    method.value = 'PATCH';
    document.getElementById('req-name').value = name;
    const radio = document.querySelector(`#req-form input[name="status"][value="${status}"]`);
    if (radio) radio.checked = true;
  } else {
    title.textContent = 'Tambah Persyaratan';
    form.action = reqStoreUrl;
    method.value = 'POST';
    document.getElementById('req-name').value = '';
    document.querySelector('#req-form input[name="status"][value="opsional"]').checked = true;
  }

  openModal('modal-req');
}

function confirmDeleteReq(id, name) {
  document.getElementById('req-delete-form').action = reqUpdateUrl(id);
  document.getElementById('req-delete-name').textContent = name;
  openModal('modal-req-delete');
}

@if($errors->any() && old('name') !== null)
  openReqModal('{{ old('_method') === 'PATCH' ? 'edit' : 'create' }}', null, @json(old('name')), '{{ old('status', 'opsional') }}');
  document.getElementById('req-name-error').textContent = @json($errors->first('name'));
  document.getElementById('req-name-error').style.display = @json($errors->has('name')) ? 'block' : 'none';
@endif
</script>
@endpush
