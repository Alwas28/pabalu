<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Struk Laundry #{{ $laundryOrder->order_number }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Courier New',Courier,monospace;background:#fff;color:#000;font-size:12px;width:80mm;margin:0 auto;padding:4mm}
@media print{body{width:80mm;margin:0;padding:4mm} .no-print{display:none!important} @page{margin:0;size:80mm auto}}
h1{font-size:15px;font-weight:700;text-align:center;margin-bottom:2px}
.sub{font-size:10px;text-align:center;color:#444}
.divider{border:none;border-top:1px dashed #888;margin:6px 0}
.divider-solid{border:none;border-top:1px solid #000;margin:6px 0}
.row{display:flex;justify-content:space-between;margin:2px 0;font-size:11px}
.row.bold{font-weight:700;font-size:12px}
.section-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin:8px 0 4px;text-align:center}
.item-name{font-weight:600;font-size:11px}
.item-detail{font-size:10px;color:#444;margin-top:1px}
.order-num-big{font-size:22px;font-weight:900;letter-spacing:2px;text-align:center;margin:8px 0 4px;border:2px solid #000;padding:6px 12px;display:inline-block;width:100%;text-align:center}
.status-badge{display:inline-block;border:1px solid #000;padding:2px 10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin:4px auto;display:block;text-align:center;width:fit-content;margin:4px auto}
.info-line{font-size:11px;margin:2px 0}
.info-label{font-weight:700;display:inline}
.footer-note{font-size:10px;text-align:center;color:#444;margin-top:8px;line-height:1.5}
.qr-wrap{text-align:center;margin:8px 0 4px}
.qr-wrap svg{width:110px;height:110px}
.qr-label{font-size:9px;text-align:center;color:#444;letter-spacing:.5px;margin-top:2px}
.print-btn{display:block;margin:16px auto;padding:10px 28px;background:#e8192c;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;font-family:sans-serif}
</style>
</head>
<body>

{{-- Header Toko --}}
<h1>{{ strtoupper($outlet->name) }}</h1>
@if($outlet->address)
<div class="sub">{{ $outlet->address }}</div>
@endif
@if($outlet->phone)
<div class="sub">Telp: {{ $outlet->phone }}</div>
@endif

<hr class="divider-solid">

{{-- Judul Dokumen --}}
<div class="section-title">Struk {{ $laundryOrder->paid_at ? 'Pembayaran' : 'Penerimaan' }} Laundry</div>

{{-- Nomor Pesanan Besar --}}
<div class="order-num-big">{{ $laundryOrder->order_number }}</div>
<div class="status-badge">{{ \App\Models\LaundryOrder::$statusLabels[$laundryOrder->status] ?? $laundryOrder->status }}</div>

<hr class="divider">

{{-- Info Pelanggan --}}
<div class="section-title">Data Pelanggan</div>
<div class="info-line"><span class="info-label">Nama :</span> {{ $laundryOrder->customer_name }}</div>
@if($laundryOrder->customer_phone)
<div class="info-line"><span class="info-label">HP   :</span> {{ $laundryOrder->customer_phone }}</div>
@endif
@if($laundryOrder->weight_kg)
<div class="info-line"><span class="info-label">Berat:</span> {{ number_format($laundryOrder->weight_kg, 1) }} kg</div>
@endif
@if($laundryOrder->estimated_done_at)
<div class="info-line"><span class="info-label">Est. Selesai:</span> {{ $laundryOrder->estimated_done_at->format('d M Y, H:i') }}</div>
@endif
<div class="info-line"><span class="info-label">Masuk :</span> {{ $laundryOrder->created_at->format('d/m/Y H:i') }}</div>
<div class="info-line"><span class="info-label">Kasir :</span> {{ $laundryOrder->user?->name ?? '-' }}</div>

@if($laundryOrder->notes)
<hr class="divider">
<div class="section-title">Catatan</div>
<div style="font-size:11px;line-height:1.5">{{ $laundryOrder->notes }}</div>
@endif

<hr class="divider">

{{-- Item Cucian --}}
<div class="section-title">Item Cucian</div>
@foreach($laundryOrder->items as $item)
<div class="item-name">{{ $item->product_name }}</div>
<div class="row" style="margin-left:4px">
  <span class="item-detail">
    {{ number_format($item->qty, $item->qty == (int)$item->qty ? 0 : 2) }}
    {{ $item->unit }} × Rp {{ number_format($item->product_price) }}
  </span>
  <span style="font-weight:600">Rp {{ number_format($item->subtotal) }}</span>
</div>
@if($item->item_notes)
<div class="item-detail" style="margin-left:4px;font-style:italic">*{{ $item->item_notes }}</div>
@endif
@endforeach

<hr class="divider-solid">

<div class="row bold">
  <span>TOTAL</span>
  <span>Rp {{ number_format($laundryOrder->total) }}</span>
</div>

@if($laundryOrder->paid_at)
<div class="status-badge" style="border-width:2px">LUNAS</div>
<hr class="divider">
<div class="row">
  <span>Metode Bayar</span>
  <span>{{ \App\Models\LaundryOrder::$paymentLabels[$laundryOrder->payment_method] ?? $laundryOrder->payment_method }}</span>
</div>
<div class="row">
  <span>Jumlah Bayar</span>
  <span>Rp {{ number_format($laundryOrder->payment_amount) }}</span>
</div>
@if($laundryOrder->change_amount > 0)
<div class="row bold">
  <span>Kembalian</span>
  <span>Rp {{ number_format($laundryOrder->change_amount) }}</span>
</div>
@endif
<div class="info-line" style="margin-top:4px">Dibayar: {{ $laundryOrder->paid_at->format('d/m/Y H:i') }}</div>
@else
<div class="status-badge" style="border-width:2px">BELUM LUNAS</div>
@endif

<hr class="divider">

{{-- QR Code — mengarah ke halaman cek status publik --}}
@php $trackUrl = route('laundry.track', $laundryOrder->order_number); @endphp
<div class="qr-wrap">
  {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(110)->margin(1)->generate($trackUrl) !!}
</div>
<div class="qr-label">SCAN UNTUK CEK STATUS CUCIAN</div>
<div style="font-size:9px;text-align:center;color:#666;margin-bottom:6px;letter-spacing:.3px">{{ $laundryOrder->order_number }}</div>

<hr class="divider">

<div class="footer-note">
  Simpan struk ini sebagai bukti<br>
  pengambilan laundry Anda.<br>
  Tunjukkan saat mengambil cucian.<br>
  <br>
  Terima kasih telah mempercayakan<br>
  cucian Anda kepada kami!
</div>

<div style="display:flex;justify-content:center;align-items:center;gap:14px;margin-top:10px">
  <img src="/img/Logo%20Viteks%20Hitam.png" alt="Viteks" style="height:34px;object-fit:contain">
  <img src="/img/Logo%20Pabalu%20-%20Hitam.png" alt="Pabalu" style="height:26px;object-fit:contain">
</div>
<div style="font-size:8px;text-align:center;color:#888;margin-top:3px;letter-spacing:.3px">
  <a href="https://pabalu.id/" style="color:#888;text-decoration:none">pabalu.id</a> didukung oleh viteks.id
</div>

{{-- Tombol Print (hanya di layar) --}}
<div class="no-print" style="margin-top:16px">
  <button class="print-btn" onclick="window.print()">
    <i>🖨</i> Cetak Struk
  </button>
  <a href="{{ $outlet->route('laundry-orders.show', [$laundryOrder->id]) }}"
     style="display:block;text-align:center;margin-top:8px;font-size:12px;color:#666;font-family:sans-serif">
    ← Kembali ke Detail Pesanan
  </a>
</div>

<script>
if (new URLSearchParams(location.search).get('autoprint') === '1') {
  window.addEventListener('load', function() { setTimeout(function(){ window.print(); }, 400); });
}
</script>

</body>
</html>
