{{-- Tab: Kode Pelunasan --}}
@php
  $statusBadge = ['aktif' => 'badge-green', 'terpakai' => 'badge-blue', 'nonaktif' => 'badge-gray', 'kadaluarsa' => 'badge-gray'];
  $statusLabel = ['aktif' => 'Aktif', 'terpakai' => 'Sudah Habis', 'nonaktif' => 'Nonaktif', 'kadaluarsa' => 'Kadaluarsa'];
@endphp
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-key a-text" style="margin-right:8px"></i>Kode Pelunasan</div>
    <button onclick="openModal('modal-generate-payment-code')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Buat Kode Baru
    </button>
  </div>
  <p style="padding:14px 20px 0;font-size:12px;color:var(--muted);line-height:1.6">
    Kode ini dibagikan ke owner/outlet setelah pembayaran tagihan dikonfirmasi (mis. transfer manual). Owner masukkan
    kode di halaman Tagihan outlet mereka untuk menandai tagihan lunas sendiri — kode yang sama bisa dipakai beberapa
    outlet berbeda sampai batas pemakaian habis.
  </p>

  @if($codes->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-key"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Kode</div>
    <p style="font-size:13px;color:var(--muted)">Buat kode pelunasan pertama untuk dibagikan ke owner.</p>
  </div>
  @else
  <div style="overflow-x:auto;margin-top:8px">
    <table class="tbl">
      <thead>
        <tr>
          <th>Kode</th>
          <th style="text-align:center;width:90px">Tipe</th>
          <th style="text-align:center;width:110px">Status</th>
          <th style="text-align:center;width:100px">Pemakaian</th>
          <th>Kadaluarsa</th>
          <th>Dipakai Oleh Outlet</th>
          <th style="text-align:center;width:70px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($codes as $c)
        <tr>
          <td class="td-main">
            <a href="{{ route('admin.tagihan.codes.show', $c) }}" style="text-decoration:none">
              <code style="font-size:12px;background:var(--surface2);padding:3px 8px;border-radius:6px;color:var(--ac)">{{ $c->code }}</code>
            </a>
          </td>
          <td style="text-align:center">
            @if($c->is_free)
              <span class="badge badge-amber"><i class="fa-solid fa-gift" style="font-size:9px"></i>Gratis</span>
            @else
              <span class="badge badge-gray">Berbayar</span>
            @endif
          </td>
          <td style="text-align:center">
            <span class="badge {{ $statusBadge[$c->status] }}">{{ $statusLabel[$c->status] }}</span>
          </td>
          <td style="text-align:center;font-size:13px;font-weight:700;color:var(--text)">
            {{ $c->uses_count }} / {{ $c->max_uses }}
            @if($c->max_uses_per_outlet)
            <div style="font-size:10px;font-weight:500;color:var(--muted);margin-top:1px">maks {{ $c->max_uses_per_outlet }}/outlet</div>
            @endif
          </td>
          <td style="font-size:12.5px;color:var(--sub)">
            {{ $c->expires_at?->format('d/m/Y') ?? 'Tidak ada' }}
          </td>
          <td style="font-size:12.5px;color:var(--sub);max-width:260px">
            @forelse($c->used_by_list->take(2) as $u)
              <div>{{ $u['outlet_name'] }} <span style="color:var(--muted)">· {{ $u['at']?->format('d/m/Y') }}</span></div>
            @empty
              <span style="color:var(--muted)">—</span>
            @endforelse
            @if($c->used_by_list->count() > 2)
              <a href="{{ route('admin.tagihan.codes.show', $c) }}" style="font-size:11px;color:var(--ac);font-weight:600">
                +{{ $c->used_by_list->count() - 2 }} lainnya
              </a>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
            <a href="{{ route('admin.tagihan.codes.show', $c) }}" title="Lihat Pemakaian"
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px;display:grid;place-items:center;text-decoration:none">
              <i class="fa-solid fa-eye"></i>
            </a>
            <button title="Hapus Kode" onclick='openDeletePaymentCode({{ json_encode(["id"=>$c->id,"code"=>$c->code,"usesCount"=>$c->uses_count]) }})'
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
<div class="modal-backdrop" id="modal-generate-payment-code" onclick="if(event.target===this)closeModal('modal-generate-payment-code')">
  <div class="modal-box" style="max-width:440px">
    <form method="POST" action="{{ route('admin.tagihan.codes.store') }}">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Buat Kode Pelunasan</h3>
        <button type="button" onclick="closeModal('modal-generate-payment-code')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Jumlah Kode</label>
            <input name="qty" type="number" class="f-input" value="1" min="1" max="100">
            <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Berapa kode BERBEDA dibuat sekaligus.</p>
          </div>
          <div>
            <label class="f-label">Maks. Pemakaian Total <span style="color:var(--ac)">*</span></label>
            <input name="max_uses" type="number" class="f-input" value="1" min="1" required>
            <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Total pemakaian gabungan SEMUA outlet (bisa dari outlet yang sama beberapa kali).</p>
          </div>
        </div>
        <div>
          <label class="f-label">Maks. Pemakaian per Outlet <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input name="max_uses_per_outlet" type="number" class="f-input" min="1" placeholder="Kosongkan = tidak dibatasi">
          <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Berapa kali SATU outlet yang sama boleh pakai kode ini — mis. isi 3 supaya 1 outlet bisa melunasi 3 tagihan sekaligus dengan kode ini.</p>
        </div>
        <div>
          <label class="f-label">Masa Berlaku Kode <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input name="expires_at" type="date" class="f-input">
          <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Kode tidak bisa dipakai lagi setelah tanggal ini. Kosongkan = tidak kadaluarsa.</p>
        </div>
        <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:13px;color:var(--sub)">
          <input type="checkbox" name="is_free" value="1">
          <span>
            Kode gratis (tagihan yang dilunasi lewat kode ini dihitung <strong>digratiskan</strong>, bukan pendapatan)
          </span>
        </label>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-generate-payment-code')"
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

{{-- Modal Hapus Kode --}}
<div class="modal-backdrop" id="modal-delete-payment-code" onclick="if(event.target===this)closeModal('modal-delete-payment-code')">
  <div class="modal-box" style="max-width:400px">
    <form id="form-delete-payment-code" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 8px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Kode Ini?</h3>
        <p style="font-size:13px;color:var(--muted);line-height:1.6">
          Kode <code id="del-payment-code-label" style="font-size:12px;background:var(--surface2);color:var(--text);padding:2px 8px;border-radius:6px;font-weight:700"></code>
          akan dihapus permanen dan tidak bisa dipakai lagi.
        </p>
      </div>
      <div id="del-payment-code-warning" style="display:none;margin:14px 24px 0;padding:11px 14px;border-radius:10px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);
           display:flex;align-items:flex-start;gap:9px">
        <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;font-size:13px;margin-top:1px"></i>
        <p style="font-size:12px;color:#fbbf24;line-height:1.5">
          Kode ini <strong id="del-payment-code-uses-count"></strong>. Tagihan yang sudah dilunasi lewat kode ini <strong>tidak akan batal lunas</strong> — hanya kodenya yang dihapus.
        </p>
      </div>
      <div style="padding:20px 24px 24px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete-payment-code')"
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
function openDeletePaymentCode(c){
  document.getElementById('form-delete-payment-code').action = '/admin/tagihan/kode/' + c.id;
  document.getElementById('del-payment-code-label').textContent = c.code;

  const warning = document.getElementById('del-payment-code-warning');
  if (c.usesCount > 0) {
    document.getElementById('del-payment-code-uses-count').textContent = 'sudah dipakai ' + c.usesCount + ' outlet';
    warning.style.display = 'flex';
  } else {
    warning.style.display = 'none';
  }

  openModal('modal-delete-payment-code');
}
</script>
