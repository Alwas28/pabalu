@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $isOwner = $user && $user->role === 'owner';

    $message = $exception->getMessage() ?: 'Anda tidak memiliki akses untuk melakukan aksi ini.';
    $isPlanLock = str_contains(mb_strtolower($message), 'paket');

    // Hanya render UI kaya (dengan sidebar) kalau outlet dari route ini benar-benar
    // milik/bisa diakses owner yang login — kalau bukan (mis. 403 karena BUKAN
    // pemilik outlet ini), jangan bocorkan sidebar/data outlet orang lain.
    $outlet = null;
    if ($isOwner) {
        $routeOutlet = request()->route('outlet');
        if ($routeOutlet instanceof \App\Models\Outlet && $routeOutlet->isAccessibleBy($user)) {
            $outlet = $routeOutlet;
        }
    }
@endphp

@if(!$isOwner)
{{-- Non-owner (kasir/admin_outlet/admin/tamu) — perilaku bawaan Laravel, tidak diubah. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forbidden</title>
    <style>
        body{font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;margin:0;background:#fff;color:#000}
        .wrap{display:flex;align-items:center;justify-content:center;min-height:100vh}
        .box{display:flex;align-items:center;padding-top:2rem}
        h1{padding:0 1rem;font-size:1.125rem;border-right:1px solid #cbd5e0;margin:0;font-weight:400}
        .msg{margin-left:1rem;font-size:1.125rem}
    </style>
</head>
<body>
    <div class="wrap" role="main">
        <div class="box">
            <h1>403</h1>
            <div class="msg">{{ $message }}</div>
        </div>
    </div>
</body>
</html>
@else
    @if($outlet)
    <x-outlet-layout :outlet="$outlet" pageTitle="Akses Ditolak">
        @include('errors.partials.403-card', [
            'message'    => $message,
            'isPlanLock' => $isPlanLock,
            'backUrl'    => $outlet->route('show'),
            'backLabel'  => 'Kembali ke Dashboard Toko',
        ])
    </x-outlet-layout>
    @else
    <x-app-layout>
        <x-slot name="pageTitle">Akses Ditolak</x-slot>
        @include('errors.partials.403-card', [
            'message'    => $message,
            'isPlanLock' => $isPlanLock,
            'backUrl'    => route('outlets.index'),
            'backLabel'  => 'Kembali ke Semua Outlet',
        ])
    </x-app-layout>
    @endif
@endif
