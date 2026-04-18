<x-app-layout title="Panduan Penggunaan">

@push('styles')
<style>
.guide-body{font-size:14px;color:var(--text);line-height:1.85}
.guide-body h1,.guide-body h2,.guide-body h3,.guide-body h4{
  font-family:'Clash Display',sans-serif;font-weight:700;color:var(--text);
  margin-top:28px;margin-bottom:10px;line-height:1.3
}
.guide-body h1{font-size:22px;border-bottom:2px solid var(--border);padding-bottom:10px;margin-bottom:14px}
.guide-body h2{font-size:18px;color:var(--ac)}
.guide-body h3{font-size:15px}
.guide-body p{margin-bottom:12px;color:var(--sub)}
.guide-body ul,.guide-body ol{padding-left:22px;margin-bottom:12px;color:var(--sub)}
.guide-body li{margin-bottom:5px}
.guide-body strong{color:var(--text);font-weight:600}
.guide-body code{
  background:var(--surface2);border:1px solid var(--border);border-radius:5px;
  padding:1px 6px;font-size:12px;font-family:monospace;color:var(--ac)
}
.guide-body pre{
  background:var(--surface2);border:1px solid var(--border);border-radius:10px;
  padding:14px 16px;overflow-x:auto;margin-bottom:14px
}
.guide-body pre code{background:none;border:none;padding:0;font-size:12.5px}
.guide-body blockquote{
  border-left:3px solid var(--ac);padding:8px 16px;margin:14px 0;
  background:var(--ac-lt);border-radius:0 8px 8px 0;color:var(--sub)
}
.guide-body hr{border:none;border-top:1px solid var(--border);margin:24px 0}
.guide-body table{width:100%;border-collapse:collapse;margin-bottom:14px;font-size:13px}
.guide-body th{background:var(--surface2);padding:8px 12px;text-align:left;border:1px solid var(--border);font-weight:600;color:var(--text)}
.guide-body td{padding:8px 12px;border:1px solid var(--border);color:var(--sub)}
.guide-body a{color:var(--ac);text-decoration:underline}
</style>
@endpush

<div style="max-width:820px">

  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:4px">
    <div style="font-size:13px;color:var(--muted)">
      Panduan resmi penggunaan sistem Pabalu untuk Owner dan Kasir.
    </div>
    @can('guide.update')
    <a href="{{ route('guide.edit') }}" class="btn btn-primary" style="text-decoration:none">
      <i class="fa-solid fa-pen-to-square"></i> Edit Panduan
    </a>
    @endcan
  </div>

  <div class="card animate-fadeUp">
    <div class="card-body" style="padding:28px 32px">
      @if($html)
        <div class="guide-body">{!! $html !!}</div>
      @else
        <div style="text-align:center;padding:56px 24px;color:var(--muted)">
          <i class="fa-solid fa-book-open" style="font-size:40px;opacity:.3;display:block;margin-bottom:14px"></i>
          <div style="font-size:14px;font-weight:600;color:var(--sub);margin-bottom:6px">Panduan belum tersedia</div>
          <div style="font-size:13px">Administrator belum menambahkan panduan penggunaan.</div>
          @can('guide.update')
          <a href="{{ route('guide.edit') }}" class="btn btn-primary" style="margin-top:16px;display:inline-flex;text-decoration:none">
            <i class="fa-solid fa-plus"></i> Buat Panduan
          </a>
          @endcan
        </div>
      @endif
    </div>
  </div>

</div>

</x-app-layout>
