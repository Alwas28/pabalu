@php
  $canOrder   = ($selfOrderEnabled ?? true) && ($openingDone ?? true);
  $outOfStock = ($requiresOpening ?? false) && $prod->stock <= 0;
  $imgUrl     = $prod->image ? Storage::url($prod->image) : null;
@endphp

<div class="prod-card {{ $outOfStock ? 'unavailable' : '' }}" data-pid="{{ $prod->id }}">

  {{-- Gambar --}}
  <div class="prod-img {{ $imgUrl ? 'has-img' : '' }}"
    @if($imgUrl)
      style="background-image:url('{{ $imgUrl }}');background-size:cover;background-position:center"
      onclick="showImg({{ Js::from($imgUrl) }}, {{ Js::from($prod->name) }})"
    @endif>
    @if($imgUrl)
      <span class="img-zoom"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
    @else
    <div class="no-img-wrap">
      <i class="fa-solid fa-image"></i>
      <span>No Image</span>
    </div>
    @endif
  </div>

  {{-- Info --}}
  <div class="prod-body">
    <div class="prod-name">{{ $prod->name }}</div>
    @if($prod->description)
    <div class="prod-desc">{{ $prod->description }}</div>
    @endif
    <div class="prod-price">{{ 'Rp ' . number_format($prod->price, 0, ',', '.') }}</div>
    @if($outOfStock)
      <span class="prod-badge-sold">Stok Habis</span>
    @endif
  </div>

  {{-- Add / Qty — hanya tampil jika ordering aktif dan produk tersedia --}}
  @if($canOrder && !$outOfStock)
  <div class="prod-foot">
    @php $safeImg = $imgUrl ? addslashes($imgUrl) : '' @endphp
    <button class="btn-add"
      onclick="addToCart({{ $prod->id }}, {{ Js::from($prod->name) }}, {{ $prod->price }}, '{{ $safeImg }}')">
      <i class="fa-solid fa-plus"></i>
    </button>
    <div class="qty-ctrl" style="display:none">
      <button onclick="setQty({{ $prod->id }}, {{ Js::from($prod->name) }}, {{ $prod->price }}, '{{ $safeImg }}', (cart[{{ $prod->id }}]?.qty??0)-1)">
        <i class="fa-solid fa-minus" style="font-size:11px"></i>
      </button>
      <span class="n">1</span>
      <button onclick="setQty({{ $prod->id }}, {{ Js::from($prod->name) }}, {{ $prod->price }}, '{{ $safeImg }}', (cart[{{ $prod->id }}]?.qty??0)+1)">
        <i class="fa-solid fa-plus" style="font-size:11px"></i>
      </button>
    </div>
  </div>
  @endif

</div>
