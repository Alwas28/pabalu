<x-outlet-layout :outlet="$outlet" pageTitle="Detail Pelanggan">

@php
  $statusBadge = ['aktif' => 'badge-blue', 'selesai' => 'badge-green'];
@endphp

<div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-size:13px;color:var(--muted)" class="animate-fadeUp">
  <a href="{{ $outlet->route('customers.index') }}" style="color:var(--muted);text-decoration:none">
    <i class="fa-solid fa-users"></i> Semua Pelanggan
  </a>
  <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
  <span style="color:var(--text);font-weight:500">{{ $customer->name }}</span>
</div>

{{-- ── HEADER PELANGGAN ── --}}
<div class="card animate-fadeUp">
  <div class="card-body" style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:14px">
      <div class="a-grad" style="width:52px;height:52px;border-radius:14px;display:grid;place-items:center;color:#fff;font-size:20px;font-weight:700;flex-shrink:0">
        {{ strtoupper(substr($customer->name, 0, 1)) }}
      </div>
      <div>
        <div class="font-display" style="font-size:18px;font-weight:700;color:var(--text)">{{ $customer->name }}</div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;display:flex;gap:12px;flex-wrap:wrap">
          @if($customer->phone)<span><i class="fa-solid fa-phone" style="margin-right:4px"></i>{{ $customer->phone }}</span>@endif
          @if($customer->email)<span><i class="fa-solid fa-envelope" style="margin-right:4px"></i>{{ $customer->email }}</span>@endif
          @if($customer->city)<span><i class="fa-solid fa-location-dot" style="margin-right:4px"></i>{{ $customer->city }}</span>@endif
        </div>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">
      @if($stats['active_rental'])
      <span class="badge badge-blue"><i class="fa-solid fa-key" style="font-size:10px"></i> Ada Sewa Aktif</span>
      @else
      <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i> Tidak Ada Sewa Aktif</span>
      @endif
      @if($requirements->where('status','wajib')->isNotEmpty())
        @if($isFullyVerified)
        <span class="badge badge-green"><i class="fa-solid fa-circle-check" style="font-size:10px"></i> Dokumen Lengkap</span>
        @else
        <span class="badge badge-red"><i class="fa-solid fa-triangle-exclamation" style="font-size:10px"></i> Dokumen Belum Lengkap</span>
        @endif
      @endif
    </div>
  </div>
  @if($customer->address || $customer->notes)
  <div class="card-body" style="border-top:1px solid var(--border);padding-top:14px;display:flex;flex-direction:column;gap:6px;font-size:12.5px;color:var(--sub)">
    @if($customer->address)<div><i class="fa-solid fa-map" style="width:16px;color:var(--muted)"></i> {{ $customer->address }}</div>@endif
    @if($customer->notes)<div><i class="fa-solid fa-note-sticky" style="width:16px;color:var(--muted)"></i> {{ $customer->notes }}</div>@endif
  </div>
  @endif
</div>

{{-- ── DOKUMEN ── --}}
@if($requirements->isNotEmpty())
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-id-card" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Dokumen</span>
  </div>
  <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px">
    @foreach($requirements as $req)
    @php $doc = $customerDocs->get($req->id); @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 14px;border-radius:12px;background:var(--surface2);flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:12px">
        @if($doc && $doc->photo)
        <a href="{{ Storage::url($doc->photo) }}" target="_blank" rel="noopener">
          @if(str_ends_with($doc->photo, '.pdf'))
          <div style="width:52px;height:52px;border-radius:8px;background:var(--border);display:grid;place-items:center;color:#f87171;font-size:18px">
            <i class="fa-solid fa-file-pdf"></i>
          </div>
          @else
          <img src="{{ Storage::url($doc->photo) }}" alt="{{ $req->name }}" style="width:52px;height:52px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
          @endif
        </a>
        @else
        <div style="width:52px;height:52px;border-radius:8px;background:var(--border);display:grid;place-items:center;color:var(--muted);font-size:16px">
          <i class="fa-solid fa-image"></i>
        </div>
        @endif
        <div>
          <div style="font-size:13.5px;font-weight:700;color:var(--text)">
            {{ $req->name }}
            <span class="req-chip" style="padding:2px 8px;font-size:10px;margin-left:6px;{{ $req->status === 'wajib' ? 'border-color:var(--ac);color:var(--ac)' : '' }}">{{ ucfirst($req->status) }}</span>
          </div>
          <div style="margin-top:4px">
            @if(!$doc)
            <span class="badge badge-gray">Belum Diunggah</span>
            @elseif($doc->status === 'terverifikasi')
            <span class="badge badge-green">Terverifikasi</span>
            @elseif($doc->status === 'ditolak')
            <span class="badge badge-red">Ditolak{{ $doc->notes ? ' — '.$doc->notes : '' }}</span>
            @else
            <span class="badge badge-amber">Menunggu Verifikasi</span>
            @endif
          </div>
        </div>
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        @if($doc && $doc->status === 'menunggu')
        <form method="POST" action="{{ $outlet->route('customers.documents.verify', [$customer, $doc]) }}" style="display:inline">
          @csrf @method('PATCH')
          <button type="submit" style="padding:7px 12px;border-radius:8px;border:1px solid rgba(52,211,153,.3);background:transparent;color:#34d399;font-size:12px;font-weight:600;cursor:pointer">
            <i class="fa-solid fa-check" style="margin-right:4px"></i>Verifikasi
          </button>
        </form>
        <button type="button" onclick="openRejectDoc({{ $doc->id }}, {{ $customer->id }}, {{ json_encode($req->name) }})"
          style="padding:7px 12px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:transparent;color:#f87171;font-size:12px;font-weight:600;cursor:pointer">
          <i class="fa-solid fa-xmark" style="margin-right:4px"></i>Tolak
        </button>
        @endif
        <button type="button" onclick="openUploadDoc({{ $req->id }}, {{ json_encode($req->name) }})"
          style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--sub);font-size:12px;font-weight:600;cursor:pointer">
          <i class="fa-solid fa-upload" style="margin-right:4px"></i>{{ $doc ? 'Unggah Ulang' : 'Unggah' }}
        </button>
      </div>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-key"></i></div>
    <div><div class="stat-num">{{ $stats['total_rentals'] }}</div><div class="stat-label">Total Sewa</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-money-bill-wave"></i></div>
    <div><div class="stat-num">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</div><div class="stat-label">Total Transaksi</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-clock"></i></div>
    <div><div class="stat-num">{{ $stats['late_count'] }}x</div><div class="stat-label">Keterlambatan</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div><div class="stat-num">{{ $stats['damage_count'] }}x</div><div class="stat-label">Denda/Kerusakan</div></div>
  </div>
</div>

{{-- ── RIWAYAT SEWA ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Riwayat Sewa</span>
    <a href="{{ $outlet->route('rentals.create') }}"
      style="padding:8px 14px;border-radius:9px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:12.5px;font-weight:700;text-decoration:none">
      <i class="fa-solid fa-plus" style="margin-right:5px"></i>Sewa Baru
    </a>
  </div>

  @if($rentals->isEmpty())
  <div style="padding:48px 24px;text-align:center">
    <i class="fa-solid fa-key" style="font-size:24px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Belum ada riwayat sewa untuk pelanggan ini.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>No. Transaksi</th>
        <th>Barang / Unit</th>
        <th>Periode</th>
        <th style="text-align:right">Biaya</th>
        <th style="text-align:center">Status</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rentals as $r)
      <tr>
        <td class="td-main" style="font-family:monospace;font-size:12px">{{ $r->order_number }}</td>
        <td>
          {{ $r->rentalUnit->rentalItem->name }}
          <div style="font-size:11px;color:var(--muted)">{{ $r->rentalUnit->code }}</div>
        </td>
        <td style="font-size:12.5px">{{ $r->start_at->translatedFormat('d M Y') }} – {{ $r->end_at->translatedFormat('d M Y') }}</td>
        <td style="text-align:right">Rp {{ number_format($r->total_amount, 0, ',', '.') }}</td>
        <td style="text-align:center">
          <span class="badge {{ $statusBadge[$r->status] ?? 'badge-gray' }}">{{ ucfirst($r->status) }}</span>
        </td>
        <td style="text-align:right">
          <a href="{{ $outlet->route('rentals.show', [$r]) }}"
            style="font-size:12px;font-weight:600;color:var(--ac);text-decoration:none">Detail</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>

{{-- ══ MODAL UNGGAH DOKUMEN ══ --}}
<div class="modal-backdrop" id="modal-upload-doc" onclick="if(event.target===this)closeModal('modal-upload-doc')">
  <div class="modal-box" style="max-width:420px">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-upload" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Unggah Dokumen
      </h3>
      <button type="button" onclick="closeModal('modal-upload-doc')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);font-size:14px;cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="upload-doc-form" method="POST" action="{{ $outlet->route('customers.documents.upload', [$customer]) }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div style="font-size:13px;color:var(--sub)">Persyaratan: <strong id="ud-req-name" style="color:var(--text)"></strong></div>
        <input type="hidden" name="rental_document_requirement_id" id="ud-req-id">
        <div>
          <label class="f-label">Foto Dokumen <span style="color:#f87171">*</span></label>
          <label id="ud-photo-label" for="ud-photo"
            style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px;border:2px dashed var(--border);border-radius:12px;padding:18px;cursor:pointer;transition:border-color .15s;min-height:100px;position:relative;overflow:hidden"
            onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
            <div id="ud-photo-placeholder" style="display:flex;flex-direction:column;align-items:center;gap:6px;pointer-events:none">
              <i class="fa-solid fa-camera" style="font-size:22px;color:var(--muted)"></i>
              <span style="font-size:12px;color:var(--muted)">Klik untuk unggah foto/PDF dokumen</span>
              <span style="font-size:10px;color:var(--muted);opacity:.7">JPG, PNG, WebP, atau PDF</span>
            </div>
            <img id="ud-photo-preview" src="" alt="" style="display:none;max-height:140px;max-width:100%;border-radius:8px;object-fit:contain">
            <div id="ud-photo-pdf-preview" style="display:none;flex-direction:column;align-items:center;gap:6px;pointer-events:none">
              <i class="fa-solid fa-file-pdf" style="font-size:32px;color:#f87171"></i>
              <span id="ud-photo-pdf-name" style="font-size:11.5px;color:var(--sub);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
            </div>
          </label>
          <input type="file" id="ud-photo" name="photo" accept="image/jpeg,image/png,image/webp,application/pdf,.pdf" style="display:none" onchange="previewUploadDoc(this)" required>
        </div>
      </div>
      <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-upload-doc')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
          Batal
        </button>
        <button type="submit" class="btn-save">
          <i class="fa-solid fa-upload" style="margin-right:6px"></i>Unggah
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ══ MODAL TOLAK DOKUMEN ══ --}}
<div class="modal-backdrop" id="modal-reject-doc" onclick="if(event.target===this)closeModal('modal-reject-doc')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-xmark" style="color:#f87171;margin-right:8px;font-size:13px"></i>Tolak Dokumen
      </h3>
      <button type="button" onclick="closeModal('modal-reject-doc')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);font-size:14px;cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="reject-doc-form" method="POST" action="">
      @csrf @method('PATCH')
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div style="font-size:13px;color:var(--sub)">Persyaratan: <strong id="rd-req-name" style="color:var(--text)"></strong></div>
        <div>
          <label class="f-label">Alasan Penolakan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="notes" class="f-input" rows="2" maxlength="255" placeholder="cth: Foto buram, tidak terbaca"></textarea>
        </div>
      </div>
      <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-reject-doc')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:#ef4444;color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
          <i class="fa-solid fa-xmark" style="margin-right:6px"></i>Tolak
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';
const customerId = {{ $customer->id }};

function openUploadDoc(reqId, reqName) {
  document.getElementById('ud-req-id').value = reqId;
  document.getElementById('ud-req-name').textContent = reqName;
  document.getElementById('ud-photo').value = '';
  document.getElementById('ud-photo-preview').style.display = 'none';
  document.getElementById('ud-photo-pdf-preview').style.display = 'none';
  document.getElementById('ud-photo-placeholder').style.display = 'flex';
  openModal('modal-upload-doc');
}

function previewUploadDoc(input) {
  const file = input.files[0];
  if (!file) return;

  if (file.type === 'application/pdf') {
    document.getElementById('ud-photo-preview').style.display = 'none';
    document.getElementById('ud-photo-placeholder').style.display = 'none';
    document.getElementById('ud-photo-pdf-name').textContent = file.name;
    document.getElementById('ud-photo-pdf-preview').style.display = 'flex';
    return;
  }

  document.getElementById('ud-photo-pdf-preview').style.display = 'none';
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('ud-photo-preview').src = e.target.result;
    document.getElementById('ud-photo-preview').style.display = 'block';
    document.getElementById('ud-photo-placeholder').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

function openRejectDoc(docId, custId, reqName) {
  document.getElementById('rd-req-name').textContent = reqName;
  document.getElementById('reject-doc-form').action = `/${outletRp}/${outletId}/customers/${custId}/documents/${docId}/reject`;
  openModal('modal-reject-doc');
}
</script>
@endpush

</x-outlet-layout>
