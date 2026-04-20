@props([
    'title'       => 'Pabalu — Sistem Manajemen UMKM',
    'description' => 'Platform manajemen UMKM untuk mengelola produk, transaksi, outlet, dan laporan bisnis Anda dengan mudah.',
    'image'       => null,
    'url'         => null,
    'noindex'     => false,
    'type'        => 'website',
])
@php
    $ogImage = $image ?? asset('img/Logo Pabalu.png');
    $ogUrl   = $url   ?? url()->current();
    $appName = \App\Models\Setting::get('app_name', config('app.name', 'Pabalu'));
@endphp

<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">
<link rel="canonical" href="{{ $ogUrl }}">
<link rel="icon" type="image/x-icon" href="{{ asset('img/Logo.ico') }}">

{{-- Open Graph --}}
<meta property="og:site_name"   content="{{ $appName }}">
<meta property="og:type"        content="{{ $type }}">
<meta property="og:url"         content="{{ $ogUrl }}">
<meta property="og:title"       content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image"       content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale"      content="id_ID">

{{-- Twitter Card --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image"       content="{{ $ogImage }}">
