{{-- Tab: Fitur --}}
@php
  $badgeColor = ['kafe'=>'badge-red','warung_makan'=>'badge-red','retail'=>'badge-blue','salon'=>'badge-amber','laundry'=>'badge-green','sewa'=>'badge-gray'];
  $grouped = collect($features)->groupBy('outlet_type');
@endphp

<div class="card" style="margin-bottom:16px">
  <div style="padding:16px 20px">
    <p style="font-size:12.5px;color:var(--muted);line-height:1.6">
      Fitur dikelompokkan <strong style="color:var(--sub)">per jenis outlet</strong> — Coffee & Resto, Rumah Makan,
      Retail, Salon, Laundry, dan Sewa masing-masing punya daftar fiturnya sendiri sepenuhnya, tidak ada lagi
      grup "Umum" lintas jenis — bahkan fitur seperti Export Excel atau Hapus Watermark diatur terpisah per jenis outlet.
      Daftar ini bersifat teknis (menentukan bagian aplikasi mana yang di-kunci) sehingga hanya bisa ditambah
      oleh developer — yang bisa Anda atur adalah fitur mana masuk ke Paket mana, di tab
      <a href="{{ route('admin.pro.plans') }}" style="color:var(--ac);font-weight:600">Paket</a>.
    </p>
  </div>
</div>

@foreach($outletTypes as $key => $label)
<div class="card" style="margin-bottom:16px">
  <div class="card-header">
    <div class="card-title">
      <span class="badge {{ $badgeColor[$key] ?? 'badge-gray' }}" style="margin-right:8px">{{ $label }}</span>
    </div>
  </div>

  @if(($grouped[$key] ?? collect())->isEmpty())
  <div style="padding:24px 20px;text-align:center;color:var(--muted);font-size:12.5px">
    Belum ada fitur khusus untuk jenis outlet ini.
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Fitur</th>
          <th>Kode (slug)</th>
          <th>Deskripsi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($grouped[$key] as $f)
        <tr>
          <td class="td-main">{{ $f['name'] }}</td>
          <td><code style="font-size:11.5px;background:var(--surface2);padding:2px 7px;border-radius:5px;color:var(--sub)">{{ $f['slug'] }}</code></td>
          <td style="font-size:12.5px;color:var(--sub);max-width:360px">{{ $f['description'] }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endforeach
