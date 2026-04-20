<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Struk {{ $transaction->nomor_transaksi }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:      #0f1117;
      --surface: #1a1d27;
      --border:  #2a2d3a;
      --text:    #e8eaf0;
      --sub:     #9ca3af;
      --muted:   #6b7280;
      --ac:      #7c6af7;
      --ac-lt:   rgba(124,106,247,.12);
    }
    @media(prefers-color-scheme: light) {
      :root {
        --bg:      #f4f5f7;
        --surface: #ffffff;
        --border:  #e5e7eb;
        --text:    #111827;
        --sub:     #4b5563;
        --muted:   #9ca3af;
        --ac:      #7c6af7;
        --ac-lt:   rgba(124,106,247,.10);
      }
    }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 24px 16px;
      gap: 16px;
    }

    /* ── Screen-only action bar ── */
    .action-bar {
      width: 100%;
      max-width: 360px;
      display: flex;
      gap: 10px;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 10px 18px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: 1px solid var(--border);
      background: var(--surface);
      color: var(--text);
      text-decoration: none;
      transition: opacity .15s;
      font-family: inherit;
    }
    .btn:hover { opacity: .8; }
    .btn-primary {
      background: var(--ac);
      border-color: var(--ac);
      color: #fff;
      flex: 1;
      justify-content: center;
    }
    .btn-ghost { flex: 1; justify-content: center; }

    @if(session('success'))
    .success-banner {
      width: 100%;
      max-width: 360px;
      background: rgba(52,211,153,.12);
      border: 1px solid rgba(52,211,153,.3);
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 13px;
      color: #34d399;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    @endif

    /* ── Receipt card ── */
    .receipt {
      width: 100%;
      max-width: 360px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      font-size: 13px;
    }

    .receipt-header {
      text-align: center;
      padding: 20px 20px 16px;
      border-bottom: 1px dashed var(--border);
      background: var(--ac-lt);
    }
    .receipt-header .outlet-name {
      font-size: 17px;
      font-weight: 700;
      color: var(--text);
      letter-spacing: .3px;
    }
    .receipt-header .outlet-meta {
      font-size: 11.5px;
      color: var(--sub);
      margin-top: 3px;
      line-height: 1.5;
    }

    .receipt-section {
      padding: 12px 20px;
      border-bottom: 1px dashed var(--border);
    }
    .receipt-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 8px;
      padding: 3px 0;
    }
    .receipt-row .label { color: var(--sub); font-size: 12px; }
    .receipt-row .value { font-weight: 600; color: var(--text); font-size: 12.5px; text-align: right; }

    .items-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12.5px;
    }
    .items-table th {
      color: var(--muted);
      font-weight: 600;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .5px;
      padding: 6px 0;
      border-bottom: 1px solid var(--border);
    }
    .items-table td {
      padding: 7px 0;
      border-bottom: 1px dotted var(--border);
      color: var(--text);
      vertical-align: top;
    }
    .items-table tr:last-child td { border-bottom: none; }
    .items-table .td-nama { width: 50%; }
    .items-table .td-qty  { width: 15%; text-align: center; color: var(--sub); }
    .items-table .td-harga{ width: 20%; text-align: right; color: var(--sub); font-size: 11.5px; }
    .items-table .td-sub  { width: 25%; text-align: right; font-weight: 600; }

    .totals-section {
      padding: 12px 20px;
      border-bottom: 1px dashed var(--border);
    }
    .total-row {
      display: flex;
      justify-content: space-between;
      padding: 3px 0;
      font-size: 12.5px;
    }
    .total-row .label { color: var(--sub); }
    .total-row .value { font-weight: 600; }
    .total-row.grand {
      margin-top: 6px;
      padding-top: 8px;
      border-top: 2px solid var(--border);
    }
    .total-row.grand .label { font-size: 14px; font-weight: 700; color: var(--text); }
    .total-row.grand .value { font-size: 16px; font-weight: 700; color: var(--ac); }
    .total-row.kembalian .value { color: #34d399; }
    .total-row.metode .value {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      color: var(--ac);
    }

    .receipt-footer {
      text-align: center;
      padding: 16px 20px;
      font-size: 12px;
      color: var(--muted);
      line-height: 1.6;
    }
    .receipt-footer strong { color: var(--sub); }

    @if($transaction->status === 'void')
    .void-stamp {
      text-align: center;
      padding: 10px;
      background: rgba(239,68,68,.1);
      border-bottom: 1px dashed var(--border);
    }
    .void-stamp span {
      font-size: 13px;
      font-weight: 700;
      color: #f87171;
      letter-spacing: 2px;
      text-transform: uppercase;
    }
    @endif

    /* ── Print styles ── */
    @media print {
      @page { margin: 5mm; size: 80mm auto; }
      body {
        background: white !important;
        color: black !important;
        padding: 0;
        gap: 0;
      }
      .action-bar,
      .success-banner,
      .no-print { display: none !important; }
      .receipt {
        max-width: 100%;
        border: none;
        border-radius: 0;
        font-size: 11px;
      }
      .receipt-header   { background: white !important; border-color: #ccc; }
      .receipt-section,
      .items-table td,
      .items-table th,
      .totals-section   { border-color: #ccc; }
      .receipt-row .label,
      .total-row .label { color: #555; }
      .receipt-row .value,
      .total-row .value,
      .items-table td   { color: black; }
      .total-row.grand .value { color: black; font-size: 14px; }
      .total-row.kembalian .value { color: black; }
      .total-row.metode .value { color: black; }
      .receipt-header .outlet-name { color: black; }
      .receipt-header .outlet-meta { color: #555; }
      .receipt-footer { color: #555; }
    }
  </style>
</head>
<body>

  @if(session('success'))
  <div class="success-banner no-print">
    <i class="fa-solid fa-circle-check"></i>
    {{ session('success') }}
  </div>
  @endif

  {{-- Action bar --}}
  <div class="action-bar no-print">
    <a href="{{ route('transactions.pos', ['outlet_id' => $transaction->outlet_id]) }}" class="btn btn-ghost">
      <i class="fa-solid fa-cash-register"></i> Kasir
    </a>
    <a href="{{ route('transactions.index') }}" class="btn btn-ghost">
      <i class="fa-solid fa-list"></i> Riwayat
    </a>
    <button onclick="window.print()" class="btn btn-primary">
      <i class="fa-solid fa-print"></i> Cetak
    </button>
  </div>

  {{-- Receipt --}}
  <div class="receipt">

    {{-- Outlet header --}}
    <div class="receipt-header">
      <div class="outlet-name">{{ $transaction->outlet->nama ?? 'Toko' }}</div>
      @if($transaction->outlet?->alamat)
      <div class="outlet-meta">{{ $transaction->outlet->alamat }}</div>
      @endif
      @if($transaction->outlet?->telepon)
      <div class="outlet-meta"><i class="fa-solid fa-phone" style="font-size:10px;margin-right:3px"></i>{{ $transaction->outlet->telepon }}</div>
      @endif
    </div>

    @if($transaction->status === 'void')
    <div class="void-stamp">
      <span><i class="fa-solid fa-ban"></i> VOID / Dibatalkan</span>
    </div>
    @endif

    {{-- Info transaksi --}}
    <div class="receipt-section">
      <div class="receipt-row">
        <span class="label">No. Transaksi</span>
        <span class="value" style="font-family:monospace;font-size:12px">{{ $transaction->nomor_transaksi }}</span>
      </div>
      <div class="receipt-row">
        <span class="label">Tanggal</span>
        <span class="value">{{ \Carbon\Carbon::parse($transaction->tanggal)->translatedFormat('d F Y') }}</span>
      </div>
      <div class="receipt-row">
        <span class="label">Waktu</span>
        <span class="value">{{ $transaction->created_at->format('H:i') }} WIB</span>
      </div>
      <div class="receipt-row">
        <span class="label">Kasir</span>
        <span class="value">{{ $transaction->kasir->name ?? '—' }}</span>
      </div>
      @if($transaction->keterangan)
      <div class="receipt-row">
        <span class="label">Ket.</span>
        <span class="value">{{ $transaction->keterangan }}</span>
      </div>
      @endif
    </div>

    {{-- Items --}}
    <div class="receipt-section">
      <table class="items-table">
        <thead>
          <tr>
            <th class="td-nama" style="text-align:left">Produk</th>
            <th class="td-qty">Qty</th>
            <th class="td-harga">Harga</th>
            <th class="td-sub">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @foreach($transaction->items as $item)
          <tr>
            <td class="td-nama">{{ $item->nama_produk }}</td>
            <td class="td-qty">{{ $item->qty }}</td>
            <td class="td-harga">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
            <td class="td-sub">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Totals --}}
    <div class="totals-section">
      <div class="total-row grand">
        <span class="label">Total</span>
        <span class="value">{{ $currencySymbol }} {{ number_format($transaction->total, 0, ',', '.') }}</span>
      </div>

      @php
        $metodeLabel = ['tunai'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer Bank','gateway'=>'Payment Gateway'];
        $metodeIcon  = ['tunai'=>'money-bill-wave','qris'=>'qrcode','transfer'=>'building-columns','gateway'=>'credit-card'];
        $m = $transaction->metode_bayar ?? 'tunai';
      @endphp
      <div class="total-row metode">
        <span class="label">Metode</span>
        <span class="value">
          <i class="fa-solid fa-{{ $metodeIcon[$m] }}" style="font-size:11px"></i>
          {{ $metodeLabel[$m] }}
        </span>
      </div>

      @if($m === 'tunai')
      <div class="total-row">
        <span class="label">Bayar</span>
        <span class="value">{{ $currencySymbol }} {{ number_format($transaction->bayar, 0, ',', '.') }}</span>
      </div>
      <div class="total-row kembalian">
        <span class="label">Kembalian</span>
        <span class="value">{{ $currencySymbol }} {{ number_format($transaction->kembalian, 0, ',', '.') }}</span>
      </div>
      @else
      <div class="total-row" style="color:#34d399">
        <span class="label">Status</span>
        <span class="value" style="color:#34d399">
          <i class="fa-solid fa-circle-check" style="font-size:11px"></i> Lunas
        </span>
      </div>
      @if($transaction->bukti_bayar)
      <div class="total-row no-print" style="margin-top:6px">
        <span class="label">Bukti Bayar</span>
        <a href="{{ Storage::url($transaction->bukti_bayar) }}" target="_blank"
          style="font-size:12px;color:var(--ac);text-decoration:none;font-weight:600">
          <i class="fa-solid fa-image"></i> Lihat Foto
        </a>
      </div>
      @endif
      @endif
    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
      {!! nl2br(e(\App\Models\Setting::get('receipt_footer', 'Terima kasih telah berbelanja!'))) !!}
    </div>

  </div>

  <script>
  // Auto-print jika dari POS (ada query ?autoprint=1)
  var params = new URLSearchParams(window.location.search);
  if (params.get('autoprint') === '1') {
    window.addEventListener('load', function(){ setTimeout(window.print, 400); });
  }
  </script>

</body>
</html>
