<x-public-layout>

@push('styles')
  /* ===== Hero Slider ===== */
  .hero{padding:16px 0;}
  .slider{
    position:relative;border-radius:16px;overflow:hidden;background:var(--green-900);
    min-height:320px;
  }
  .slide{
    display:none;min-height:320px;position:relative;overflow:hidden;cursor:pointer;
  }
  .slide.active{display:block;animation:fadeIn .6s ease;}
  @keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
  .slide-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;}
  .slide-hint{
    position:absolute;right:16px;bottom:16px;z-index:5;background:rgba(10,15,12,.65);color:#fff;
    font-size:11.5px;font-weight:700;padding:8px 14px;border-radius:20px;
    display:inline-flex;align-items:center;gap:6px;backdrop-filter:blur(4px);transition:.2s;
  }
  .slide:hover .slide-hint{background:var(--green-600);}
  .btn-primary{background:var(--green-600);color:#fff;padding:10px 20px;border-radius:9px;font-weight:700;font-size:12.5px;display:inline-flex;align-items:center;gap:7px;transition:.2s;}
  .btn-primary:hover{background:var(--green-700);}
  .slider-arrows{position:absolute;bottom:14px;right:18px;display:flex;gap:6px;z-index:10;}
  .slider-arrows button{width:28px;height:28px;border-radius:50%;background:#fff;box-shadow:var(--shadow);font-size:13px;color:var(--green-900);}
  .slider-dots{position:absolute;bottom:20px;left:16px;display:flex;gap:6px;z-index:10;}
  .slider-dots span{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.5);cursor:pointer;transition:.2s;}
  .slider-dots span.active{background:#fff;width:18px;border-radius:5px;}

  /* ===== Modal info slide ===== */
  .slide-modal-backdrop{
    position:fixed;inset:0;background:rgba(10,15,12,.6);backdrop-filter:blur(3px);z-index:600;
    display:flex;align-items:center;justify-content:center;padding:20px;
    opacity:0;visibility:hidden;transition:opacity .2s,visibility .2s;
  }
  .slide-modal-backdrop.open{opacity:1;visibility:visible;}
  .slide-modal-box{
    background:#fff;border-radius:18px;max-width:480px;width:100%;overflow:hidden;
    box-shadow:0 30px 70px rgba(0,0,0,.35);transform:translateY(14px);transition:transform .25s;position:relative;
  }
  .slide-modal-backdrop.open .slide-modal-box{transform:translateY(0);}
  .slide-modal-close{
    position:absolute;top:12px;right:12px;width:32px;height:32px;border-radius:50%;
    background:rgba(0,0,0,.45);color:#fff;font-size:14px;z-index:2;display:flex;align-items:center;justify-content:center;
  }
  .slide-modal-img{width:100%;height:220px;object-fit:cover;display:block;}
  .slide-modal-body{padding:22px 24px 26px;}
  .slide-modal-badge{display:inline-block;background:var(--sun);color:var(--green-900);font-weight:800;font-size:11px;padding:5px 12px;border-radius:20px;margin-bottom:10px;}
  .slide-modal-body h3{font-size:20px;font-weight:800;color:var(--green-900);margin-bottom:8px;line-height:1.25;}
  .slide-modal-body p{font-size:13px;color:var(--ink-soft);line-height:1.6;margin-bottom:16px;}

  .sec-arrows{display:flex;gap:6px;}
  .sec-arrows button{width:26px;height:26px;border-radius:50%;border:1.5px solid var(--line);background:#fff;color:var(--ink-soft);font-size:12px;}

  /* Featured categories */
  .cat-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;}
  .cat-card{border:1px solid var(--line);border-radius:12px;padding:13px 8px 11px;text-align:center;transition:.2s;background:#fff;}
  .cat-card:hover{box-shadow:var(--shadow);transform:translateY(-3px);border-color:transparent;}
  .cat-card img{width:46px;height:46px;object-fit:cover;border-radius:10px;margin:0 auto 9px;}
  .cat-card span{font-size:11px;font-weight:600;color:var(--ink);}

  /* Promo banners */
  .promo-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
  .promo-banner{
    border-radius:13px;overflow:hidden;position:relative;min-height:120px;
    display:flex;align-items:center;
  }
  .promo-banner img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;}
  .promo-banner::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(14,59,42,.72) 30%,rgba(14,59,42,.05) 75%);}
  .promo-content{position:relative;z-index:2;padding:0 24px;color:#fff;}
  .promo-content .off{font-size:11px;font-weight:600;opacity:.9;margin-bottom:5px;}
  .promo-content h3{font-size:17px;font-weight:800;margin-bottom:11px;}
  .btn-light{background:var(--green-900);color:#fff;padding:8px 14px;border-radius:7px;font-size:11.5px;font-weight:700;}

  /* Product cards */
  .prod-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}
  .prod-card{border:1px solid var(--line);border-radius:12px;padding:11px;position:relative;background:#fff;transition:.2s;}
  .prod-card:hover{box-shadow:var(--shadow-lg);transform:translateY(-3px);}
  .prod-tag{position:absolute;top:9px;left:9px;background:var(--clay);color:#fff;font-size:9.5px;font-weight:800;padding:2px 7px;border-radius:4px;z-index:2;}
  .prod-tag.hot{background:#E8494A;}
  .prod-tag.new{background:var(--green-600);}
  .prod-img{background:var(--mint-50);border-radius:9px;height:100px;display:flex;align-items:center;justify-content:center;margin-bottom:9px;overflow:hidden;}
  .prod-img img{height:100%;width:100%;object-fit:cover;}
  .prod-cat{font-size:10px;color:var(--ink-soft);margin-bottom:3px;}
  .prod-name{font-size:12px;font-weight:700;margin-bottom:5px;line-height:1.3;min-height:31px;}
  .prod-rating{font-size:10.5px;color:var(--sun);margin-bottom:7px;display:flex;align-items:center;gap:4px;}
  .prod-rating em{color:var(--ink-soft);font-style:normal;}
  .prod-bottom{display:flex;justify-content:space-between;align-items:center;}
  .prod-price{font-size:13px;font-weight:800;color:var(--green-900);}
  .prod-price del{font-weight:500;color:var(--ink-soft);font-size:10.5px;margin-left:4px;}
  .add-btn{background:var(--green-600);color:#fff;font-size:10.5px;font-weight:700;padding:6px 10px;border-radius:7px;display:flex;align-items:center;gap:3px;}
  .add-btn:hover{background:var(--green-700);}

  /* Daily best sells */
  .best-grid{display:grid;grid-template-columns:1.1fr repeat(3,1fr);gap:12px;}
  .best-feature{
    background:linear-gradient(160deg,#1A2E22,#0E3B2A);border-radius:13px;color:#fff;
    padding:18px;position:relative;overflow:hidden;min-height:190px;display:flex;flex-direction:column;justify-content:flex-end;
  }
  .best-feature img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.35;}
  .best-feature .content{position:relative;z-index:2;}
  .best-feature h3{font-size:15px;font-weight:800;margin-bottom:11px;line-height:1.3;}
  .best-feature .btn-primary{background:var(--sun);color:var(--green-900);}
  .best-feature .btn-primary:hover{background:#ffc245;}

  @media(max-width:1080px){
    .cat-grid{grid-template-columns:repeat(4,1fr);}
    .prod-grid{grid-template-columns:repeat(3,1fr);}
    .best-grid{grid-template-columns:1fr 1fr;}
  }
  @media(max-width:860px){
    .promo-row{grid-template-columns:1fr;}
    .slide{min-height:220px;}
    .slider{min-height:220px;}
    .slider-dots{left:50%;transform:translateX(-50%);}
  }
  @media(max-width:640px){
    .cat-grid{grid-template-columns:repeat(3,1fr);}
    .prod-grid{grid-template-columns:repeat(2,1fr);}
    .best-grid{grid-template-columns:1fr;}
  }
  @media(max-width:420px){
    .cat-grid{grid-template-columns:repeat(2,1fr);}
  }
@endpush

<!-- HERO SLIDER -->
<section class="hero">
  <div class="container">
    <div class="slider">
      @forelse($sliders as $i => $slide)
      <div class="slide {{ $i === 0 ? 'active' : '' }}" onclick="openSlideModal({{ $i }})"
        data-image="{{ asset('storage/' . $slide->image) }}"
        data-badge="{{ $slide->badge }}"
        data-title="{{ $slide->title }}"
        data-desc="{{ $slide->description }}"
        data-btn-text="{{ $slide->button_text }}"
        data-btn-url="{{ $slide->button_url }}">
        <img class="slide-bg" src="{{ asset('storage/' . $slide->image) }}" alt="{{ $slide->title }}">
      </div>
      @empty
      <div class="slide active" style="background:var(--green-900);display:flex;align-items:center;justify-content:center;cursor:default" onclick="event.stopPropagation()">
        <div style="text-align:center;color:#fff;padding:24px">
          <img src="/img/logo-pabalu.png" alt="Pabalu" style="width:64px;height:64px;object-fit:contain;margin:0 auto 14px">
          <h2 style="font-size:20px;font-weight:800;margin-bottom:8px">Selamat Datang di Pabalu</h2>
          <p style="font-size:13px;opacity:.85;max-width:420px;margin:0 auto">Platform kasir & marketplace untuk UMKM — banner promo di sini akan tampil begitu admin menambahkannya.</p>
        </div>
      </div>
      @endforelse
      <div class="slider-dots" id="dots"></div>
      <div class="slider-arrows">
        <button id="prevBtn">‹</button>
        <button id="nextBtn">›</button>
      </div>
    </div>
  </div>
</section>

{{-- Modal info slide --}}
<div class="slide-modal-backdrop" id="slideModal" onclick="if(event.target===this)closeSlideModal()">
  <div class="slide-modal-box">
    <button class="slide-modal-close" onclick="closeSlideModal()">✕</button>
    <img id="sm-image" class="slide-modal-img" src="" alt="">
    <div class="slide-modal-body">
      <span id="sm-badge" class="slide-modal-badge"></span>
      <h3 id="sm-title"></h3>
      <p id="sm-desc"></p>
      <a id="sm-btn" href="#" class="btn-primary"></a>
    </div>
  </div>
</div>

<!-- FEATURED CATEGORIES -->
<section class="section">
  <div class="container">
    <div class="sec-head">
      <h2>Kategori Pilihan</h2>
      <div class="sec-arrows"><button>‹</button><button>›</button></div>
    </div>
    <div class="cat-grid">
      @forelse($featuredCategories as $fcat)
      <a href="{{ route('product-categories.show', $fcat) }}" class="cat-card">
        <div style="width:46px;height:46px;border-radius:10px;background:var(--mint-100);color:var(--green-700);display:flex;align-items:center;justify-content:center;margin:0 auto 9px;font-size:19px">
          <i class="fa-solid {{ $fcat->icon ?: 'fa-tag' }}"></i>
        </div>
        <span>{{ $fcat->name }}</span>
      </a>
      @empty
      <p style="grid-column:1/-1;text-align:center;color:var(--ink-soft);font-size:13px;padding:20px 0">
        Belum ada kategori pilihan. Admin bisa menandainya lewat Jenis Outlet → Detail → Kategori.
      </p>
      @endforelse
    </div>

    @if($featuredCategoryProducts->isNotEmpty())
    <div class="prod-grid" style="margin-top:18px">
      @foreach($featuredCategoryProducts as $product)
      @php
        $outlet = $product->outlet;
        $link = null;
        if ($outlet) {
          if ($outlet->rp() === 'fnb') $link = route('public.menu', $outlet->code);
          elseif ($outlet->rp() === 'retail') $link = route('public.katalog', $outlet->code);
        }
      @endphp
      <a href="{{ $link ?: '#' }}" class="prod-card" @if(!$link) onclick="event.preventDefault()" style="cursor:default" @endif>
        <div class="prod-img">
          @if($product->image)
          <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
          @else
          <i class="fa-solid fa-image" style="font-size:22px;color:var(--line)"></i>
          @endif
        </div>
        <div class="prod-cat">{{ $product->category->name ?? '' }}</div>
        <div class="prod-name">{{ $product->name }}</div>
        <div class="prod-bottom">
          <div class="prod-price">Rp {{ number_format($product->price) }}</div>
          <span style="font-size:10px;color:var(--ink-soft)"><i class="fa-solid fa-shop" style="font-size:9px;margin-right:3px"></i>{{ $outlet->name ?? '' }}</span>
        </div>
      </a>
      @endforeach
    </div>
    @endif
  </div>
</section>

<!-- PROMO BANNERS (dikelola admin: Pengaturan Homepage > Iklan/Promo) -->
@if($promoBanners->isNotEmpty())
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="promo-row">
      @foreach($promoBanners as $banner)
      <div class="promo-banner">
        <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}">
        <div class="promo-content">
          @if($banner->badge)
          <div class="off">{{ $banner->badge }}</div>
          @endif
          <h3>{{ $banner->title }}</h3>
          @if($banner->button_text)
          <a href="{{ $banner->button_url ?: '#' }}" class="btn-light">{{ $banner->button_text }}</a>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@php
  // Link produk publik: cuma outlet fnb (menu) & retail (katalog) yang punya halaman
  // publik saat ini — sama aturan yang dipakai section "Kategori Pilihan" di atas.
  $productLink = function ($product) {
    $outlet = $product->outlet;
    if (!$outlet) return null;
    return match ($outlet->rp()) {
      'fnb'    => route('public.menu', $outlet->code),
      'retail' => route('public.katalog', $outlet->code),
      default  => null,
    };
  };
@endphp

<!-- POPULAR PRODUCTS -->
@if($featuredProducts->isNotEmpty())
<section class="section">
  <div class="container">
    <div class="sec-head"><h2>Produk Populer</h2></div>
    <div class="prod-grid">
      @foreach($featuredProducts as $product)
      @php $link = $productLink($product); @endphp
      <a href="{{ $link ?: '#' }}" class="prod-card" @if(!$link) onclick="event.preventDefault()" style="cursor:default" @endif>
        <div class="prod-img">
          @if($product->image)
          <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
          @else
          <i class="fa-solid fa-image" style="font-size:22px;color:var(--line)"></i>
          @endif
        </div>
        <div class="prod-cat">{{ $product->category->name ?? $product->outlet->outletType->name ?? '' }}</div>
        <div class="prod-name">{{ $product->name }}</div>
        <div class="prod-bottom">
          <div class="prod-price">Rp {{ number_format($product->price) }}</div>
          <span style="font-size:10px;color:var(--ink-soft)"><i class="fa-solid fa-shop" style="font-size:9px;margin-right:3px"></i>{{ $product->outlet->name ?? '' }}</span>
        </div>
      </a>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- PRODUK TERLARIS (dihitung dari total qty terjual sungguhan, bukan contoh) -->
@if($bestSellers->isNotEmpty())
<section class="section">
  <div class="container">
    <div class="sec-head"><h2>Produk Terlaris</h2></div>
    <div class="best-grid">
      @foreach($bestSellers as $i => $product)
      @php $link = $productLink($product); @endphp
      @if($i === 0)
      <a href="{{ $link ?: '#' }}" class="best-feature" @if(!$link) onclick="event.preventDefault()" style="cursor:default" @endif>
        @if($product->image)
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
        @endif
        <div class="content">
          <h3>{{ $product->name }} — produk terlaris di {{ $product->outlet->name ?? 'Pabalu' }}.</h3>
          <span class="btn-primary">Lihat Produk →</span>
        </div>
      </a>
      @else
      <a href="{{ $link ?: '#' }}" class="prod-card" @if(!$link) onclick="event.preventDefault()" style="cursor:default" @endif>
        <div class="prod-img">
          @if($product->image)
          <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
          @else
          <i class="fa-solid fa-image" style="font-size:22px;color:var(--line)"></i>
          @endif
        </div>
        <div class="prod-cat">{{ $product->category->name ?? $product->outlet->outletType->name ?? '' }}</div>
        <div class="prod-name">{{ $product->name }}</div>
        <div class="prod-bottom">
          <div class="prod-price">Rp {{ number_format($product->price) }}</div>
          <span style="font-size:10px;color:var(--ink-soft)"><i class="fa-solid fa-shop" style="font-size:9px;margin-right:3px"></i>{{ $product->outlet->name ?? '' }}</span>
        </div>
      </a>
      @endif
      @endforeach
    </div>
  </div>
</section>
@endif

@push('scripts')
<script>
  // ---- Hero slider ----
  const slides = document.querySelectorAll('.slide');
  const dotsWrap = document.getElementById('dots');
  let current = 0;
  slides.forEach((_, i) => {
    const d = document.createElement('span');
    if (i === 0) d.classList.add('active');
    d.addEventListener('click', () => goTo(i));
    dotsWrap.appendChild(d);
  });
  const dots = dotsWrap.querySelectorAll('span');
  function goTo(i) {
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');
    current = (i + slides.length) % slides.length;
    slides[current].classList.add('active');
    dots[current].classList.add('active');
  }
  document.getElementById('nextBtn').addEventListener('click', () => goTo(current + 1));
  document.getElementById('prevBtn').addEventListener('click', () => goTo(current - 1));

  // ---- Modal info slide ----
  function openSlideModal(index) {
    const el = slides[index];
    if (!el) return;
    document.getElementById('sm-image').src = el.dataset.image || '';

    const badgeEl = document.getElementById('sm-badge');
    badgeEl.textContent = el.dataset.badge || '';
    badgeEl.style.display = el.dataset.badge ? '' : 'none';

    document.getElementById('sm-title').textContent = el.dataset.title || '';

    const descEl = document.getElementById('sm-desc');
    descEl.textContent = el.dataset.desc || '';
    descEl.style.display = el.dataset.desc ? '' : 'none';

    const btnEl = document.getElementById('sm-btn');
    if (el.dataset.btnText) {
      btnEl.style.display = '';
      btnEl.textContent = el.dataset.btnText + ' →';
      btnEl.href = el.dataset.btnUrl || '#';
    } else {
      btnEl.style.display = 'none';
    }

    document.getElementById('slideModal').classList.add('open');
  }
  function closeSlideModal() {
    document.getElementById('slideModal').classList.remove('open');
  }
  let auto = setInterval(() => goTo(current + 1), 5000);
  document.querySelector('.slider').addEventListener('mouseenter', () => clearInterval(auto));
  document.querySelector('.slider').addEventListener('mouseleave', () => auto = setInterval(() => goTo(current + 1), 5000));
</script>
@endpush

</x-public-layout>
