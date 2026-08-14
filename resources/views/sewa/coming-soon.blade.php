<x-outlet-layout :outlet="$outlet" :pageTitle="$title">

<div class="card animate-fadeUp" style="text-align:center;padding:52px 24px">
  <div style="width:72px;height:72px;border-radius:50%;background:var(--ac-lt);color:var(--ac);
              display:grid;place-items:center;margin:0 auto 20px;font-size:28px">
    <i class="fa-solid {{ $icon }}"></i>
  </div>
  <div style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:8px;font-family:'Clash Display',sans-serif">
    {{ $title }}
  </div>
  <div style="font-size:13.5px;color:var(--muted);line-height:1.7;max-width:440px;margin:0 auto">
    {{ $desc }}
  </div>
  <div style="margin-top:18px">
    <span class="badge badge-amber"><i class="fa-solid fa-hammer" style="font-size:10px"></i> Sedang dikembangkan</span>
  </div>
  <div style="margin-top:24px">
    <a href="{{ $outlet->route('show') }}"
       style="padding:10px 20px;border-radius:10px;background:var(--ac);color:#fff;
              text-decoration:none;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:7px">
      <i class="fa-solid fa-house"></i> Kembali ke Dashboard Toko
    </a>
  </div>
</div>

</x-outlet-layout>
