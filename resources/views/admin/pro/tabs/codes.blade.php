{{-- Tab: Kode Aktivasi --}}
@php
  $statusBadge = ['aktif' => 'badge-green', 'terpakai' => 'badge-blue', 'kadaluarsa' => 'badge-gray'];
  $statusLabel = ['aktif' => 'Aktif', 'terpakai' => 'Sudah Dipakai', 'kadaluarsa' => 'Kadaluarsa'];
@endphp
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-key a-text" style="margin-right:8px"></i>Kode Aktivasi</div>
    <button onclick="openModal('modal-generate-code')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Buat Kode Baru
    </button>
  </div>

  @if($codes->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-key"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Kode</div>
    <p style="font-size:13px;color:var(--muted)">Buat kode aktivasi pertama untuk dibagikan ke owner.</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Paket</th>
          <th style="text-align:center;width:110px">Status</th>
          <th style="text-align:center;width:90px">Pemakaian</th>
          <th>Dipakai Oleh</th>
          <th>Kadaluarsa Kode</th>
          <th style="text-align:center;width:70px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($codes as $c)
        <tr>
          <td class="td-main">
            <a href="{{ route('admin.pro.codes.show', $c) }}" style="text-decoration:none">
              <code style="font-size:12px;background:var(--surface2);padding:3px 8px;border-radius:6px;color:var(--ac)">{{ $c->code }}</code>
            </a>
          </td>
          <td style="font-size:13px;color:var(--sub)">{{ $c->plan->name }}</td>
          <td style="text-align:center">
            <span class="badge {{ $statusBadge[$c->status] }}">{{ $statusLabel[$c->status] }}</span>
          </td>
          <td style="text-align:center;font-size:13px;font-weight:700;color:var(--text)">
            {{ $c->uses_count }} / {{ $c->max_uses }}
          </td>
          <td style="font-size:12.5px;color:var(--sub);max-width:220px">
            @forelse($c->used_by_list->take(2) as $u)
              <div>{{ $u['name'] ?? '(akun terhapus)' }} <span style="color:var(--muted)">· {{ $u['at']?->format('d/m/Y') }}</span></div>
            @empty
              <span style="color:var(--muted)">—</span>
            @endforelse
            @if($c->used_by_list->count() > 2)
              <a href="{{ route('admin.pro.codes.show', $c) }}" style="font-size:11px;color:var(--ac);font-weight:600">
                +{{ $c->used_by_list->count() - 2 }} lainnya
              </a>
            @endif
          </td>
          <td style="font-size:12.5px;color:var(--sub)">
            {{ $c->expires_at?->format('d/m/Y') ?? '—' }}
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
            <a href="{{ route('admin.pro.codes.show', $c) }}" title="Lihat Pemakaian"
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px;display:grid;place-items:center;text-decoration:none">
              <i class="fa-solid fa-eye"></i>
            </a>
            <button title="Edit Batas Pemakaian" onclick='openEditCode({{ json_encode(["id"=>$c->id,"code"=>$c->code,"max_uses"=>$c->max_uses,"uses_count"=>$c->uses_count]) }})'
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px">
              <i class="fa-solid fa-pen"></i>
            </button>
            <button title="Hapus Kode" onclick='openDeleteCode({{ json_encode(["id"=>$c->id,"code"=>$c->code,"usesCount"=>$c->uses_count]) }})'
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

{{-- Modal Buat Kode --}}
<div class="modal-backdrop" id="modal-generate-code" onclick="if(event.target===this)closeModal('modal-generate-code')">
  <div class="modal-box" style="max-width:480px">
    <form method="POST" action="{{ route('admin.pro.codes.store') }}">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Buat Kode Aktivasi</h3>
        <button type="button" onclick="closeModal('modal-generate-code')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Paket <span style="color:var(--ac)">*</span></label>
          <select name="plan_id" class="f-input" required>
            <option value="">Pilih paket...</option>
            @foreach($plans as $plan)
              <option value="{{ $plan->id }}">{{ $plan->name }}</option>
            @endforeach
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Jumlah Kode</label>
            <input name="qty" type="number" class="f-input" value="1" min="1" max="100">
            <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Berapa kode BERBEDA yang dibuat sekaligus.</p>
          </div>
          <div>
            <label class="f-label">Maks. Pemakaian per Kode</label>
            <input name="max_uses" type="number" class="f-input" value="1" min="1">
            <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Berapa owner BERBEDA boleh pakai kode yang sama.</p>
          </div>
        </div>
        <div>
          <label class="f-label">Masa Aktif Pro (hari)</label>
          <input name="valid_days" type="number" class="f-input" value="30" min="1" placeholder="cth: 30" required>
          <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Berapa lama Pro aktif SETELAH kode dipakai (per owner yang redeem).</p>
        </div>
        <div>
          <label class="f-label">Kode Kadaluarsa Pada <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input name="expires_at" type="date" class="f-input">
          <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Batas waktu kode ini masih bisa DITUKAR (bukan masa aktif Pro-nya). Kosongkan = tidak kadaluarsa.</p>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-generate-code')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Buat Kode
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Edit Batas Pemakaian --}}
<div class="modal-backdrop" id="modal-edit-code" onclick="if(event.target===this)closeModal('modal-edit-code')">
  <div class="modal-box" style="max-width:420px">
    <form id="form-edit-code" method="POST" action="">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Batas Pemakaian</h3>
        <button type="button" onclick="closeModal('modal-edit-code')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Kode</label>
          <div id="ed-code-label" style="font-size:13px;font-weight:700;color:var(--text);padding:9px 0"></div>
        </div>
        <div>
          <label class="f-label">Maks. Pemakaian <span style="color:var(--ac)">*</span></label>
          <input id="ed-code-max-uses" name="max_uses" type="number" class="f-input" min="1" required>
          <p id="ed-code-hint" style="font-size:10.5px;color:var(--muted);margin-top:3px"></p>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit-code')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Hapus Kode --}}
<div class="modal-backdrop" id="modal-delete-code" onclick="if(event.target===this)closeModal('modal-delete-code')">
  <div class="modal-box" style="max-width:400px">
    <form id="form-delete-code" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 8px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Kode Ini?</h3>
        <p style="font-size:13px;color:var(--muted);line-height:1.6">
          Kode <code id="del-code-label" style="font-size:12px;background:var(--surface2);color:var(--text);padding:2px 8px;border-radius:6px;font-weight:700"></code>
          akan dihapus permanen dan tidak bisa dipakai lagi.
        </p>
      </div>
      <div id="del-code-warning" style="display:none;margin:14px 24px 0;padding:11px 14px;border-radius:10px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);
           display:flex;align-items:flex-start;gap:9px">
        <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;font-size:13px;margin-top:1px"></i>
        <p style="font-size:12px;color:#fbbf24;line-height:1.5">
          Kode ini <strong id="del-code-uses-count"></strong>. Owner yang sudah pakai <strong>tidak akan kehilangan</strong> paket Pro-nya — hanya kodenya yang dihapus.
        </p>
      </div>
      <div style="padding:20px 24px 24px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete-code')"
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

<script>
function openDeleteCode(c){
  document.getElementById('form-delete-code').action = '/admin/pro/kode/' + c.id;
  document.getElementById('del-code-label').textContent = c.code;

  const warning = document.getElementById('del-code-warning');
  if (c.usesCount > 0) {
    document.getElementById('del-code-uses-count').textContent = 'sudah dipakai ' + c.usesCount + ' owner';
    warning.style.display = 'flex';
  } else {
    warning.style.display = 'none';
  }

  openModal('modal-delete-code');
}

function openEditCode(c){
  document.getElementById('form-edit-code').action = '/admin/pro/kode/' + c.id;
  document.getElementById('ed-code-label').textContent = c.code;
  document.getElementById('ed-code-max-uses').value = c.max_uses;
  document.getElementById('ed-code-max-uses').min = Math.max(1, c.uses_count);
  document.getElementById('ed-code-hint').textContent = 'Sudah dipakai ' + c.uses_count + ' owner — tidak bisa diset lebih kecil dari itu.';
  openModal('modal-edit-code');
}
</script>
