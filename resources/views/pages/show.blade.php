<x-public-layout :title="($page->meta_title ?: $page->title) . ' — Pabalu'" :description="$page->meta_description" :og-image="$page->image ? asset('storage/' . $page->image) : null">

@push('styles')
  .page-hero{background:var(--mint-50);padding:36px 0;}
  .page-hero h1{font-size:26px;font-weight:800;color:var(--green-900);}
  .page-content{padding:36px 0 60px;}
  .page-content img.page-featured{width:100%;max-height:380px;object-fit:cover;border-radius:16px;margin-bottom:26px;}
  .page-content .body{font-size:14.5px;line-height:1.9;color:var(--ink);max-width:820px;margin:0 auto;}
  .page-content .body p{margin-bottom:14px;}
  .page-content .body h1,.page-content .body h2,.page-content .body h3{font-family:'Sora',sans-serif;color:var(--green-900);margin:24px 0 12px;}
  .page-content .body ul,.page-content .body ol{margin:0 0 14px 22px;}
  .page-content .body a{color:var(--green-700);text-decoration:underline;}
  .page-content .body img{max-width:100%;border-radius:10px;margin:14px 0;}
  .page-content .body table{width:100%;border-collapse:collapse;margin-bottom:14px;}
  .page-content .body table td,.page-content .body table th{border:1px solid var(--line);padding:8px;}
  .page-draft-banner{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:12.5px;font-weight:600;padding:10px 16px;border-radius:10px;max-width:820px;margin:0 auto 20px;display:flex;align-items:center;gap:8px;}
@endpush

@if(!$page->is_active)
<div class="container" style="padding-top:16px">
  <div class="page-draft-banner">
    <i class="fa-solid fa-triangle-exclamation"></i>
    Mode preview admin — halaman ini <strong>belum aktif</strong> dan tidak terlihat oleh pengunjung publik.
  </div>
</div>
@endif

<section class="page-hero">
  <div class="container">
    <h1>{{ $page->title }}</h1>
  </div>
</section>

<section class="page-content">
  <div class="container">
    @if($page->image)
    <img src="{{ asset('storage/' . $page->image) }}" alt="{{ $page->title }}" class="page-featured">
    @endif
    <div class="body">{!! $page->content !!}</div>
  </div>
</section>

</x-public-layout>
