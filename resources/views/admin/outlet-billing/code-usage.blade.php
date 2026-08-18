<x-app-layout>
<x-slot name="pageTitle">Pemakaian Kode Pelunasan</x-slot>

@php
  $statusBadge = ['aktif' => 'badge-green', 'terpakai' => 'badge-blue', 'nonaktif' => 'badge-gray', 'kadaluarsa' => 'badge-gray'];
  $statusLabel = ['aktif' => 'Aktif', 'terpakai' => 'Sudah Habis', 'nonaktif' => 'Nonaktif', 'kadaluarsa' => 'Kadaluarsa'];
@endphp

<a href="{{ route('admin.tagihan.codes') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--sub);text-decoration:none;margin-bottom:14px">
  <i class="fa-solid fa-arrow-left"></i> Kembali ke Kode Pelunasan
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
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Tipe</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">
        @if($code->is_free)
          <span class="badge badge-amber"><i class="fa-solid fa-gift" style="font-size:9px"></i>Gratis</span>
        @else
          <span class="badge badge-gray">Berbayar</span>
        @endif
      </div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Pemakaian Total</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">{{ $code->uses_count }} / {{ $code->max_uses }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Maks. per Outlet</div>
      <div style="font-size:14px;font-weight:600;color:var(--text);margin-top:3px">{{ $code->max_uses_per_outlet ?? 'Tidak dibatasi' }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Masa Berlaku</div>
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
    <div class="card-title"><i class="fa-solid fa-shop a-text" style="margin-right:8px"></i>Outlet yang Sudah Memakai ({{ $code->usages->count() }})</div>
  </div>

  @if($code->usages->isEmpty())
  <div style="padding:40px 20px;text-align:center;color:var(--muted);font-size:13px">
    Belum ada outlet yang memakai kode ini.
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Outlet</th>
          <th>Owner</th>
          <th>Tagihan yang Dilunasi</th>
          <th>Digunakan Pada</th>
        </tr>
      </thead>
      <tbody>
        @foreach($code->usages as $u)
        <tr>
          <td class="td-main">{{ $u->outlet?->name ?? '(outlet terhapus)' }}</td>
          <td style="font-size:12.5px;color:var(--sub)">{{ $u->outlet?->owner?->name ?? '—' }}</td>
          <td style="font-size:12.5px;color:var(--sub)">
            @if($u->invoice)
              Rp {{ number_format($u->invoice->amount, 0, ',', '.') }}
            @else
              (tagihan terhapus)
            @endif
          </td>
          <td style="font-size:12.5px;color:var(--sub)">{{ $u->used_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

</x-app-layout>
