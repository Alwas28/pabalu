<x-public-layout :title="($q !== '' ? 'Hasil pencarian: '.$q : 'Pencarian') . ' — Pabalu'">

@push('styles')
  .search-hero{background:var(--mint-50);padding:28px 0;}
  .search-hero h1{font-size:20px;font-weight:800;color:var(--green-900);}
  .search-hero p{font-size:13px;color:var(--ink-soft);margin-top:4px;}
  .search-results{padding:30px 0 60px;}
  .result-sec{margin-bottom:36px;}
  .result-sec h2{font-size:15px;font-weight:800;color:var(--green-900);margin-bottom:14px;display:flex;align-items:center;gap:8px;}
  .result-sec h2 .count{font-size:11px;font-weight:700;color:var(--green-700);background:var(--mint-100);padding:2px 9px;border-radius:99px;}

  .r-prod-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}
  .r-prod-card{border:1px solid var(--line);border-radius:12px;padding:11px;background:#fff;transition:.2s;display:block;}
  .r-prod-card:hover{box-shadow:var(--shadow-lg);transform:translateY(-3px);}
  .r-prod-img{background:var(--mint-50);border-radius:9px;height:100px;display:flex;align-items:center;justify-content:center;margin-bottom:9px;overflow:hidden;}
  .r-prod-img img{height:100%;width:100%;object-fit:cover;}
  .r-prod-img i{font-size:26px;color:var(--line);}
  .r-prod-name{font-size:12px;font-weight:700;color:var(--ink);margin-bottom:4px;line-height:1.3;min-height:31px;}
  .r-prod-outlet{font-size:10.5px;color:var(--ink-soft);margin-bottom:6px;display:flex;align-items:center;gap:4px;}
  .r-prod-price{font-size:13px;font-weight:800;color:var(--green-900);}

  .r-outlet-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
  .r-outlet-card{border:1px solid var(--line);border-radius:12px;padding:16px;background:#fff;transition:.2s;display:flex;gap:12px;align-items:flex-start;}
  .r-outlet-card.clickable:hover{box-shadow:var(--shadow-lg);transform:translateY(-3px);}
  .r-outlet-icon{width:44px;height:44px;border-radius:10px;background:var(--mint-100);color:var(--green-700);display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
  .r-outlet-name{font-size:13.5px;font-weight:700;color:var(--ink);margin-bottom:3px;}
  .r-outlet-type{display:inline-block;font-size:10px;font-weight:700;color:var(--green-700);background:var(--mint-100);padding:2px 8px;border-radius:99px;margin-bottom:6px;}
  .r-outlet-addr{font-size:11.5px;color:var(--ink-soft);line-height:1.5;}

  .empty-state{text-align:center;padding:40px 20px;color:var(--ink-soft);font-size:13px;background:#fff;border:1px dashed var(--line);border-radius:12px;}

  @media(max-width:1080px){.r-prod-grid{grid-template-columns:repeat(3,1fr);}.r-outlet-grid{grid-template-columns:1fr 1fr;}}
  @media(max-width:640px){.r-prod-grid{grid-template-columns:repeat(2,1fr);}.r-outlet-grid{grid-template-columns:1fr;}}
@endpush

<section class="search-hero">
  <div class="container">
    <h1>Hasil Pencarian</h1>
    @if($q !== '')
      @if($fuzzy)
        <p><i class="fa-solid fa-wand-magic-sparkles" style="color:var(--green-700);margin-right:4px"></i>Tidak ada hasil persis untuk "<strong style="color:var(--ink)">{{ $q }}</strong>" — menampilkan yang mirip:</p>
      @else
        <p>Menampilkan hasil untuk <strong style="color:var(--ink)">"{{ $q }}"</strong></p>
      @endif
    @else
      <p>Ketik kata kunci pada kotak pencarian di atas untuk mulai mencari.</p>
    @endif
  </div>
</section>

<section class="search-results">
  <div class="container">

    @if($q === '')
      <div class="empty-state">
        <i class="fa-solid fa-magnifying-glass" style="font-size:26px;opacity:.3;margin-bottom:10px;display:block"></i>
        Belum ada kata kunci pencarian.
      </div>
    @else

      @if($wantProducts)
      <div class="result-sec">
        <h2><i class="fa-solid fa-basket-shopping" style="color:var(--green-700);font-size:13px"></i> Produk <span class="count">{{ $products->count() }}</span></h2>
        @if($products->isEmpty())
        <div class="empty-state">Tidak ada produk yang cocok dengan "{{ $q }}".</div>
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
      @endif

      @if($wantOutlets)
      <div class="result-sec">
        <h2><i class="fa-solid fa-shop" style="color:var(--green-700);font-size:13px"></i> Outlet <span class="count">{{ $outlets->count() }}</span></h2>
        @if($outlets->isEmpty())
        <div class="empty-state">Tidak ada outlet yang cocok dengan "{{ $q }}".</div>
        @else
        <div class="r-outlet-grid">
          @foreach($outlets as $outlet)
          @php
            $link = null;
            if ($outlet->rp() === 'fnb') $link = route('public.menu', $outlet->code);
            elseif ($outlet->rp() === 'retail') $link = route('public.katalog', $outlet->code);
          @endphp
          <a href="{{ $link ?: '#' }}" class="r-outlet-card {{ $link ? 'clickable' : '' }}" @if(!$link) onclick="event.preventDefault()" style="cursor:default" @endif>
            <div class="r-outlet-icon"><i class="fa-solid {{ $outlet->outletType->icon ?? 'fa-store' }}"></i></div>
            <div>
              <div class="r-outlet-name">{{ $outlet->name }}</div>
              <span class="r-outlet-type">{{ $outlet->outletType->name ?? '—' }}</span>
              @if($outlet->address)
              <div class="r-outlet-addr"><i class="fa-solid fa-location-dot" style="margin-right:4px"></i>{{ $outlet->address }}</div>
              @endif
            </div>
          </a>
          @endforeach
        </div>
        @endif
      </div>
      @endif

    @endif

  </div>
</section>

</x-public-layout>
