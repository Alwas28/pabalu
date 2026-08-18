<x-app-layout>
<x-slot name="pageTitle">{{ $type === 'dibayar' ? 'Rincian Tagihan Dibayar' : 'Rincian Tagihan Digratiskan' }}</x-slot>

<a href="{{ route('admin.tagihan.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--sub);text-decoration:none;margin-top:14px;margin-bottom:14px">
  <i class="fa-solid fa-arrow-left"></i> Kembali ke Tagihan Pro Plan
</a>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:{{ $type === 'dibayar' ? 'rgba(52,211,153,.15)' : 'rgba(251,191,36,.15)' }};color:{{ $type === 'dibayar' ? '#34d399' : '#fbbf24' }}">
      <i class="fa-solid {{ $type === 'dibayar' ? 'fa-sack-dollar' : 'fa-gift' }}"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:19px">Rp {{ number_format($total, 0, ',', '.') }}</div>
      <div class="stat-label">{{ $type === 'dibayar' ? 'Total Pendapatan' : 'Total Digratiskan' }}</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(129,140,248,.15);color:#818cf8"><i class="fa-solid fa-receipt"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">{{ $count }} tagihan</div>
      <div class="stat-label">Jumlah Tagihan</div>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid {{ $type === 'dibayar' ? 'fa-sack-dollar' : 'fa-gift' }} a-text" style="margin-right:8px"></i>
      Daftar Tagihan {{ $type === 'dibayar' ? 'Dibayar' : 'Digratiskan' }}
    </div>
  </div>

  @if($invoices->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid {{ $type === 'dibayar' ? 'fa-sack-dollar' : 'fa-gift' }}"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text)">Belum ada tagihan {{ $type === 'dibayar' ? 'yang dibayar' : 'yang digratiskan' }}.</div>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Outlet</th>
          <th>Owner</th>
          <th style="text-align:right">Nominal</th>
          @if($type === 'dibayar')
          <th>Cara Lunas</th>
          @else
          <th>Kode</th>
          @endif
          <th>Tanggal Lunas</th>
          <th style="text-align:center;width:90px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoices as $inv)
        <tr>
          <td class="td-main">{{ $inv->outlet?->name ?? '(outlet terhapus)' }}</td>
          <td style="font-size:12.5px;color:var(--sub)">{{ $inv->outlet?->owner?->name ?? '—' }}</td>
          <td style="text-align:right;font-size:13px;font-weight:700;color:var(--text)">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
          @if($type === 'dibayar')
          <td style="font-size:12.5px;color:var(--sub)">{{ $inv->paymentUsage ? 'Kode pelunasan' : 'Admin' }}</td>
          @else
          <td style="font-size:12.5px;color:var(--sub)">
            <code style="font-size:11px;background:var(--surface2);padding:2px 6px;border-radius:5px">{{ $inv->paymentUsage?->code?->code ?? '—' }}</code>
          </td>
          @endif
          <td style="font-size:12.5px;color:var(--sub)">{{ $inv->paid_at?->format('d/m/Y') }}</td>
          <td style="text-align:center">
            @if($inv->outlet)
            <a href="{{ route('admin.tagihan.show', $inv->outlet) }}" title="Lihat Outlet"
              style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px;display:inline-grid;place-items:center;text-decoration:none">
              <i class="fa-solid fa-eye"></i>
            </a>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

</x-app-layout>
