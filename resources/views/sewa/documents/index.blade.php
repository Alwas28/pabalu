<x-outlet-layout :outlet="$outlet" pageTitle="Dokumen">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Dokumen</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">Verifikasi dokumen identitas pelanggan pada outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong></p>
</div>

@if($requirements->isEmpty())
<div class="card animate-fadeUp d1" style="padding:40px 24px;text-align:center">
  <i class="fa-solid fa-id-card" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
  <p style="font-size:13.5px;color:var(--muted)">Outlet ini belum punya persyaratan dokumen.</p>
  <a href="{{ $outlet->route('settings.edit', ['tab' => 'requirements']) }}" style="color:var(--ac);font-weight:600;font-size:13px;text-decoration:none;display:inline-block;margin-top:8px">
    Atur Persyaratan Dokumen &rarr;
  </a>
</div>
@else

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-list-check"></i></div>
    <div><div class="stat-num">{{ $requirements->count() }}</div><div class="stat-label">Persyaratan Dokumen ({{ $requiredCount }} wajib)</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-hourglass-half"></i></div>
    <div><div class="stat-num">{{ $pendingCount }}</div><div class="stat-label">Dokumen Menunggu Verifikasi</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-users"></i></div>
    <div><div class="stat-num">{{ $customers->count() }}</div><div class="stat-label">Total Pelanggan{{ $status ? ' (difilter)' : '' }}</div></div>
  </div>
</div>

{{-- ── FILTER ── --}}
<div class="card animate-fadeUp d2" style="padding:14px 18px">
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="{{ $outlet->route('documents.index') }}"
      style="padding:8px 14px;border-radius:9px;font-size:12.5px;font-weight:600;text-decoration:none;border:1px solid {{ !$status ? 'var(--ac)' : 'var(--border)' }};color:{{ !$status ? 'var(--ac)' : 'var(--muted)' }};background:{{ !$status ? 'var(--ac-lt)' : 'transparent' }}">
      Semua
    </a>
    <a href="{{ $outlet->route('documents.index', ['status' => 'belum']) }}"
      style="padding:8px 14px;border-radius:9px;font-size:12.5px;font-weight:600;text-decoration:none;border:1px solid {{ $status === 'belum' ? 'var(--ac)' : 'var(--border)' }};color:{{ $status === 'belum' ? 'var(--ac)' : 'var(--muted)' }};background:{{ $status === 'belum' ? 'var(--ac-lt)' : 'transparent' }}">
      Belum Lengkap
    </a>
    <a href="{{ $outlet->route('documents.index', ['status' => 'lengkap']) }}"
      style="padding:8px 14px;border-radius:9px;font-size:12.5px;font-weight:600;text-decoration:none;border:1px solid {{ $status === 'lengkap' ? 'var(--ac)' : 'var(--border)' }};color:{{ $status === 'lengkap' ? 'var(--ac)' : 'var(--muted)' }};background:{{ $status === 'lengkap' ? 'var(--ac-lt)' : 'transparent' }}">
      Lengkap
    </a>
  </div>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-id-card" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Daftar Pelanggan</span>
  </div>

  @if($customers->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-id-card" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Tidak ada pelanggan yang cocok.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>Pelanggan</th>
        <th>Status Dokumen</th>
        <th style="text-align:center">Kelengkapan</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($customers as $c)
      @php $complete = $c->hasVerifiedAllRequiredDocuments(); @endphp
      <tr>
        <td class="td-main">
          <a href="{{ $outlet->route('customers.show', [$c]) }}" style="color:var(--ac);font-weight:600;text-decoration:none">{{ $c->name }}</a>
          <div style="font-size:11px;color:var(--muted)">{{ $c->phone ?: '—' }}</div>
        </td>
        <td>
          <div style="display:flex;gap:5px;flex-wrap:wrap">
            @foreach($requirements as $req)
            @php $doc = $c->documents->firstWhere('rental_document_requirement_id', $req->id); @endphp
            @if(!$doc)
              <span class="badge badge-gray" title="{{ $req->name }}">{{ $req->name }}: Belum Ada</span>
            @elseif($doc->status === 'terverifikasi')
              <span class="badge badge-green" title="{{ $req->name }}">{{ $req->name }}: OK</span>
            @elseif($doc->status === 'ditolak')
              <span class="badge badge-red" title="{{ $req->name }}">{{ $req->name }}: Ditolak</span>
            @else
              <span class="badge badge-amber" title="{{ $req->name }}">{{ $req->name }}: Menunggu</span>
            @endif
            @endforeach
          </div>
        </td>
        <td style="text-align:center">
          @if($requirements->where('status','wajib')->isEmpty())
          <span class="badge badge-gray">Tanpa Syarat</span>
          @elseif($complete)
          <span class="badge badge-green"><i class="fa-solid fa-circle-check" style="font-size:9px"></i> Lengkap</span>
          @else
          <span class="badge badge-red"><i class="fa-solid fa-triangle-exclamation" style="font-size:9px"></i> Belum Lengkap</span>
          @endif
        </td>
        <td style="text-align:right">
          <a href="{{ $outlet->route('customers.show', [$c]) }}"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;text-decoration:none;display:inline-block">
            Kelola Dokumen
          </a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>
@endif

</x-outlet-layout>
