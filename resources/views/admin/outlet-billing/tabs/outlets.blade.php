{{-- Tab: Daftar Outlet --}}
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-shop a-text" style="margin-right:8px"></i>Daftar Outlet</div>
  </div>

  @if($outlets->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-shop"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Outlet</div>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Outlet</th>
          <th>Owner</th>
          <th>Jenis Outlet</th>
          <th style="text-align:center;width:150px">Tagihan Belum Lunas</th>
          <th style="text-align:center;width:90px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($outlets as $o)
        <tr>
          <td class="td-main">{{ $o->name }}</td>
          <td style="font-size:13px;color:var(--sub)">{{ $o->owner?->name ?? '(tanpa owner)' }}</td>
          <td style="font-size:13px;color:var(--sub)">{{ $o->outletType?->name ?? '—' }}</td>
          <td style="text-align:center">
            @if($o->unpaid_invoices_count > 0)
              <span class="badge badge-gray">{{ $o->unpaid_invoices_count }} belum lunas</span>
            @else
              <span style="font-size:12px;color:var(--muted)">—</span>
            @endif
          </td>
          <td style="text-align:center">
            <a href="{{ route('admin.tagihan.show', $o) }}"
              style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:9px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:12px;font-weight:600;text-decoration:none">
              <i class="fa-solid fa-eye"></i> Detail
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
