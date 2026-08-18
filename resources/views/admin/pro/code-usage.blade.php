<x-app-layout>
<x-slot name="pageTitle">Pemakaian Kode</x-slot>

@php
  $statusBadge = ['aktif' => 'badge-green', 'terpakai' => 'badge-blue', 'kadaluarsa' => 'badge-gray'];
  $statusLabel = ['aktif' => 'Aktif', 'terpakai' => 'Sudah Dipakai', 'kadaluarsa' => 'Kadaluarsa'];
@endphp

<a href="{{ route('admin.pro.codes') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--sub);text-decoration:none;margin-bottom:14px">
  <i class="fa-solid fa-arrow-left"></i> Kembali ke Kode Aktivasi
</a>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <code style="font-size:15px;background:var(--surface2);padding:4px 10px;border-radius:7px">{{ $code->code }}</code>
    </div>
    <span class="badge {{ $statusBadge[$code->status] }}">{{ $statusLabel[$code->status] }}</span>
  </div>
  <div style="padding:18px 20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px">
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Paket</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">{{ $code->plan->name }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Pemakaian</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">{{ $code->uses_count }} / {{ $code->max_uses }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Masa Aktif Pro</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">{{ $code->valid_days }} hari / pemakaian</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Kadaluarsa Kode</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">{{ $code->expires_at?->format('d/m/Y') ?? 'Tidak ada' }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Dibuat Oleh</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">{{ $code->creator?->name ?? '—' }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Dibuat Pada</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">{{ $code->created_at->format('d/m/Y H:i') }}</div>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-users a-text" style="margin-right:8px"></i>Owner yang Sudah Memakai ({{ $code->subscriptions->count() }})</div>
  </div>

  @if($code->subscriptions->isEmpty())
  <div style="padding:40px 20px;text-align:center;color:var(--muted);font-size:13px">
    Belum ada owner yang memakai kode ini.
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Owner</th>
          <th>Email</th>
          <th>Diaktifkan Pada</th>
          <th>Berlaku Sampai</th>
          <th style="text-align:center;width:70px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($code->subscriptions as $sub)
        <tr>
          <td class="td-main">{{ $sub->owner?->name ?? '(akun terhapus)' }}</td>
          <td style="font-size:12.5px;color:var(--sub)">{{ $sub->owner?->email ?? '—' }}</td>
          <td style="font-size:12.5px;color:var(--sub)">{{ $sub->activated_at->format('d/m/Y H:i') }}</td>
          <td style="font-size:12.5px;color:var(--sub)">{{ $sub->expires_at?->format('d/m/Y') ?? '—' }}</td>
          <td style="text-align:center">
            <form method="POST" action="{{ route('admin.pro.codes.usage.destroy', [$code, $sub]) }}" style="margin:0"
                  onsubmit="return confirm('Hapus pemakaian kode ini oleh &quot;{{ $sub->owner?->name }}&quot;? Owner ini akan kehilangan akses Pro yang didapat dari kode ini, dan 1 slot pemakaian dikembalikan ke kode.')">
              @csrf @method('DELETE')
              <button type="submit" title="Hapus Pemakaian"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

</x-app-layout>
