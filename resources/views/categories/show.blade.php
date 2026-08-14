<x-public-layout :title="$category->name . ' — Pabalu'" :description="$category->description">

@push('styles')
  .hcat-hero{background:var(--mint-50);padding:32px 0;}
  .hcat-hero .row{display:flex;align-items:center;gap:16px;}
  .hcat-hero .icon{width:56px;height:56px;border-radius:14px;background:#fff;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--green-700);font-size:22px;}
  .hcat-hero h1{font-size:22px;font-weight:800;color:var(--green-900);}
  .hcat-hero p{font-size:13px;color:var(--ink-soft);margin-top:4px;}
  .hcat-results{padding:30px 0 60px;}
  .hcat-count{font-size:12.5px;color:var(--ink-soft);margin-bottom:16px;}

  .r-prod-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}
  .r-prod-card{border:1px solid var(--line);border-radius:12px;padding:11px;background:#fff;transition:.2s;display:block;}
  .r-prod-card:hover{box-shadow:var(--shadow-lg);transform:translateY(-3px);}
  .r-prod-img{background:var(--mint-50);border-radius:9px;height:100px;display:flex;align-items:center;justify-content:center;margin-bottom:9px;overflow:hidden;}
  .r-prod-img img{height:100%;width:100%;object-fit:cover;}
  .r-prod-img i{font-size:26px;color:var(--line);}
  .r-prod-name{font-size:12px;font-weight:700;color:var(--ink);margin-bottom:4px;line-height:1.3;min-height:31px;}
  .r-prod-outlet{font-size:10.5px;color:var(--ink-soft);margin-bottom:6px;display:flex;align-items:center;gap:4px;}
  .r-prod-price{font-size:13px;font-weight:800;color:var(--green-900);}

  .empty-state{text-align:center;padding:40px 20px;color:var(--ink-soft);font-size:13px;background:#fff;border:1px dashed var(--line);border-radius:12px;}

  @media(max-width:1080px){.r-prod-grid{grid-template-columns:repeat(3,1fr);}}
  @media(max-width:640px){.r-prod-grid{grid-template-columns:repeat(2,1fr);}}
@endpush

<section class="hcat-hero">
  <div class="container row">
    <div class="icon"><i class="fa-solid {{ $category->icon ?: 'fa-tag' }}"></i></div>
    <div>
      <h1>{{ $category->name }}</h1>
      @if($category->description)
      <p>{{ $category->description }}</p>
      @endif
    </div>
  </div>
</section>

<section class="hcat-results">
  <div class="container">
    <div class="hcat-count">{{ $products->count() }} produk ditemukan</div>

    @if($products->isEmpty())
    <div class="empty-state">
      <i class="fa-solid fa-basket-shopping" style="font-size:26px;opacity:.3;margin-bottom:10px;display:block"></i>
      Belum ada produk pada kategori ini.
    </div>
    @else
    <div class="r-prod-grid">
      @foreach($products as $product)
      @php
        $outlet = $product->outlet;
        $link = null;
        if ($outlet) {
          if ($outlet->rp() === 'fnb') $link = route('public.menu', $outlet->code);
          elseif ($outlet->rp() === 'retail') $link = route('public.katalog', $outlet->code);
        }
      @endphp
      <a href="{{ $link ?: '#' }}" class="r-prod-card" @if(!$link) onclick="event.preventDefault()" style="cursor:default" @endif>
        <div class="r-prod-img">
          @if($product->image)
          <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
          @else
          <i class="fa-solid fa-image"></i>
          @endif
        </div>
        <div class="r-prod-name">{{ $product->name }}</div>
        <div class="r-prod-outlet"><i class="fa-solid fa-shop" style="font-size:9px"></i> {{ $outlet->name ?? '—' }}</div>
        <div class="r-prod-price">Rp {{ number_format($product->price) }}</div>
      </a>
      @endforeach
    </div>
    @endif
  </div>
</section>

</x-public-layout>
