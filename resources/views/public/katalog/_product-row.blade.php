@php
  $imgUrl = $prod->image ? Storage::url($prod->image) : null;
@endphp

<div class="prod-row">
  {{-- Thumbnail --}}
  <div class="prod-thumb {{ $imgUrl ? 'has-img' : '' }}"
    @if($imgUrl)
      style="background-image:url('{{ $imgUrl }}');background-size:cover;background-position:center"
      onclick="showImg({{ Js::from($imgUrl) }}, {{ Js::from($prod->name) }})"
    @endif>
    @if($imgUrl)
      <span class="thumb-zoom"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
    @else
      <i class="fa-solid fa-image"></i>
    @endif
  </div>

  {{-- Info --}}
  <div class="prod-info">
    <div class="prod-name">{{ $prod->name }}</div>
    <div class="prod-price">Rp {{ number_format($prod->price, 0, ',', '.') }}</div>
  </div>
</div>
