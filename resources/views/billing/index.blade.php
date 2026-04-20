<x-app-layout title="Tagihan Aplikasi">

@if($midtransClientKey)
<script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
@endif

  {{-- Banner: tidak ada tagihan --}}
  @if(! $invoice)
  <div style="text-align:center;padding:60px 20px;color:var(--muted)">
    <i class="fa-solid fa-circle-check" style="font-size:48px;color:#34d399;margin-bottom:16px;display:block"></i>
    <div style="font-size:17px;font-weight:700;color:#34d399;margin-bottom:6px">Tidak Ada Tagihan Aktif</div>
    <div style="font-size:13.5px">Akun Anda dalam kondisi baik. Tidak ada tagihan yang perlu dibayar saat ini.</div>
  </div>
  @else

  {{-- Tagihan Aktif --}}
  @php
    $isOverdue = $invoice->isOverdue();
    $isDueSoon = $invoice->isDueSoon();
    $accentColor = $isOverdue ? '#f87171' : ($isDueSoon ? '#f59e0b' : '#818cf8');
    $bgColor     = $isOverdue ? 'rgba(239,68,68,.08)' : ($isDueSoon ? 'rgba(245,158,11,.08)' : 'rgba(99,102,241,.08)');
    $borderColor = $isOverdue ? 'rgba(239,68,68,.3)'  : ($isDueSoon ? 'rgba(245,158,11,.3)'  : 'rgba(99,102,241,.3)');
  @endphp
  <div style="background:{{ $bgColor }};border:1px solid {{ $borderColor }};border-radius:16px;padding:24px 28px;margin-bottom:20px">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
          <i class="fa-solid fa-file-invoice-dollar" style="font-size:20px;color:{{ $accentColor }}"></i>
          <span style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;color:{{ $accentColor }}">
            Tagihan Aktif
          </span>
          @if($isOverdue)
          <span style="font-size:11px;padding:3px 9px;border-radius:99px;background:rgba(239,68,68,.2);color:#f87171;font-weight:600">OVERDUE</span>
          @elseif($isDueSoon)
          <span style="font-size:11px;padding:3px 9px;border-radius:99px;background:rgba(245,158,11,.2);color:#f59e0b;font-weight:600">JATUH TEMPO SEGERA</span>
          @endif
        </div>
        <div style="font-size:13.5px;color:var(--text);margin-bottom:4px">{{ $invoice->description }}</div>
        @if($invoice->period_label)
        <div style="font-size:12.5px;color:var(--sub)">Periode: {{ $invoice->period_label }}</div>
        @endif
        <div style="font-size:12.5px;color:var(--muted);margin-top:4px">
          Jatuh tempo: <strong style="color:{{ $accentColor }}">{{ $invoice->due_date->isoFormat('D MMMM YYYY') }}</strong>
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-family:'Clash Display',sans-serif;font-size:28px;font-weight:700;color:{{ $accentColor }}">
          Rp {{ number_format($invoice->amount, 0, ',', '.') }}
        </div>
      </div>
    </div>

    @if($midtransClientKey)
    <div style="margin-top:20px;padding-top:20px;border-top:1px solid {{ $borderColor }}">
      <div id="snap-container" style="display:none;margin-bottom:16px"></div>
      <div id="pay-status" style="display:none;margin-bottom:16px"></div>

      <button id="btn-bayar" onclick="openBillingSnap()"
        style="display:inline-flex;align-items:center;gap:8px;padding:11px 24px;border-radius:12px;
               background:linear-gradient(135deg,{{ $accentColor }},{{ $isOverdue ? '#ef4444' : '#6366f1' }});
               color:#fff;font-weight:700;font-size:14px;border:none;cursor:pointer;transition:opacity .15s"
        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <i class="fa-solid fa-credit-card"></i> Bayar Sekarang
      </button>
    </div>
    @else
    <div style="margin-top:16px;padding:12px 16px;background:rgba(100,116,139,.1);border-radius:10px;font-size:13px;color:var(--sub)">
      <i class="fa-solid fa-circle-info" style="margin-right:6px"></i>
      Pembayaran online belum tersedia. Hubungi admin untuk konfirmasi pembayaran.
    </div>
    @endif
  </div>
  @endif

  {{-- Riwayat Tagihan --}}
  @if($history->count())
  <div class="card animate-fadeUp">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px"></i>
        Riwayat Tagihan
      </div>
    </div>
    <div class="card-body" style="padding:0">
      @foreach($history as $h)
      <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:600">{{ $h->description }}</div>
          <div style="font-size:12px;color:var(--muted)">
            {{ $h->due_date->isoFormat('D MMM YYYY') }}
            @if($h->period_label) · {{ $h->period_label }} @endif
          </div>
        </div>
        <div style="text-align:right">
          <div style="font-weight:700;font-size:13.5px;color:var(--ac)">Rp {{ number_format($h->amount, 0, ',', '.') }}</div>
          @if($h->status === 'paid')
            <span style="font-size:11px;color:#34d399">Lunas</span>
          @elseif($h->status === 'cancelled')
            <span style="font-size:11px;color:#94a3b8">Dibatalkan</span>
          @else
            <span style="font-size:11px;color:#f59e0b">Belum Bayar</span>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

@push('scripts')
<script>
var billingPaying = false;

function openBillingSnap() {
  if (billingPaying) return;

  var btn = document.getElementById('btn-bayar');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...';

  fetch('{{ route('billing.snap-token') }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
      'Accept': 'application/json',
    },
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (!data.snap_token) {
      showPayStatus('error', data.message || 'Gagal memuat pembayaran.');
      resetBayarBtn();
      return;
    }

    billingPaying = true;
    var container = document.getElementById('snap-container');
    container.style.display = 'block';
    btn.style.display = 'none';

    snap.embed(data.snap_token, {
      embedId: 'snap-container',
      onSuccess: function(result) {
        billingPaying = false;
        container.style.display = 'none';
        showPayStatus('success', 'Pembayaran berhasil! Memperbarui status tagihan...');

        fetch('{{ route('billing.mark-paid') }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ order_id: result.order_id }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.ok) {
            showPayStatus('success', 'Pembayaran berhasil! Halaman akan diperbarui...');
            setTimeout(function() { window.location.reload(); }, 1500);
          }
        })
        .catch(function() {
          showPayStatus('success', 'Pembayaran berhasil! Silakan refresh halaman.');
        });
      },
      onPending: function(result) {
        billingPaying = false;
        container.style.display = 'none';
        showPayStatus('info', 'Pembayaran tertunda. Silakan selesaikan sesuai instruksi yang dikirim.');
      },
      onError: function(result) {
        billingPaying = false;
        container.style.display = 'none';
        showPayStatus('error', 'Pembayaran gagal. Silakan coba lagi.');
        resetBayarBtn();
      },
      onClose: function() {
        if (billingPaying) {
          billingPaying = false;
          container.style.display = 'none';
          showPayStatus('info', 'Jendela pembayaran ditutup. Klik "Bayar Sekarang" untuk melanjutkan.');
          resetBayarBtn();
        }
      },
    });
  })
  .catch(function(err) {
    showPayStatus('error', 'Terjadi kesalahan jaringan. Coba lagi.');
    resetBayarBtn();
  });
}

function resetBayarBtn() {
  var btn = document.getElementById('btn-bayar');
  if (btn) {
    btn.disabled = false;
    btn.style.display = '';
    btn.innerHTML = '<i class="fa-solid fa-credit-card"></i> Bayar Sekarang';
  }
}

function showPayStatus(type, msg) {
  var el = document.getElementById('pay-status');
  var colors = {
    success: {bg:'rgba(16,185,129,.1)', border:'rgba(16,185,129,.3)', text:'#34d399', icon:'fa-circle-check'},
    info:    {bg:'rgba(99,102,241,.1)',  border:'rgba(99,102,241,.3)',  text:'#818cf8', icon:'fa-circle-info'},
    error:   {bg:'rgba(239,68,68,.1)',   border:'rgba(239,68,68,.3)',   text:'#f87171', icon:'fa-circle-exclamation'},
  };
  var c = colors[type] || colors.info;
  el.style.display = 'block';
  el.style.background = c.bg;
  el.style.border = '1px solid ' + c.border;
  el.style.borderRadius = '10px';
  el.style.padding = '12px 16px';
  el.style.fontSize = '13px';
  el.style.color = c.text;
  el.innerHTML = '<i class="fa-solid ' + c.icon + '" style="margin-right:6px"></i>' + msg;
}
</script>
@endpush

</x-app-layout>
