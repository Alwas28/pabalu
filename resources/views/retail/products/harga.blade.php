<x-retail-layout :outlet="$outlet" :pageTitle="'Ubah Harga — ' . $product->name">

<div class="animate-fadeUp d1">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);margin-bottom:20px">
    <a href="{{ $outlet->route('products.index') }}"
       style="color:var(--muted);text-decoration:none"
       onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      Produk
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
    <span style="color:var(--sub)">{{ $product->name }}</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
    <span style="color:var(--text);font-weight:600">Ubah Harga Jual</span>
  </div>

  <div class="two-col" style="align-items:start">

    {{-- ── KOLOM KIRI: Info + Form ── --}}
    <div style="display:flex;flex-direction:column;gap:20px">

      {{-- Info produk --}}
      <div class="card">
        <div class="card-header">
          <span class="card-title">
            <i class="fa-solid fa-tag" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Harga Saat Ini
          </span>
        </div>
        <div class="card-body" style="padding:20px">
          <div style="display:flex;align-items:center;gap:16px">
            <div style="width:52px;height:52px;border-radius:14px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:20px;flex-shrink:0">
              <i class="fa-solid fa-cubes"></i>
            </div>
            <div style="flex:1">
              <div style="font-size:16px;font-weight:700;color:var(--text)">{{ $product->name }}</div>
              <div style="font-size:12px;color:var(--muted);margin-top:2px">
                {{ $product->category?->name ?? 'Tanpa Kategori' }}
                · {{ $product->unit }}
                @if($product->sku) · SKU: {{ $product->sku }} @endif
              </div>
            </div>
            <div style="text-align:right">
              <div style="font-size:11px;color:var(--muted)">Harga Jual</div>
              <div class="font-display" style="font-size:24px;font-weight:800;color:var(--ac)">
                Rp {{ number_format($product->price) }}
              </div>
              <div style="font-size:11.5px;color:var(--muted);margin-top:2px">
                Stok: <strong style="color:{{ $product->stock > 0 ? 'var(--text)' : '#f87171' }}">{{ number_format($product->stock) }} {{ $product->unit }}</strong>
              </div>
            </div>
          </div>

          @if($product->stock > 0)
          <div style="margin-top:16px;padding:12px 14px;border-radius:12px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);display:flex;align-items:flex-start;gap:10px">
            <i class="fa-solid fa-circle-info" style="color:#fbbf24;font-size:14px;margin-top:1px;flex-shrink:0"></i>
            <div style="font-size:12.5px;color:var(--sub);line-height:1.6">
              Produk ini masih memiliki <strong style="color:var(--text)">{{ number_format($product->stock) }} {{ $product->unit }}</strong> di stok.
              Harga baru akan berlaku untuk semua penjualan berikutnya. Transaksi yang sudah terjadi tidak berubah.
            </div>
          </div>
          @endif
        </div>
      </div>

      {{-- Form ubah harga --}}
      <div class="card">
        <div class="card-header">
          <span class="card-title">
            <i class="fa-solid fa-pen-to-square" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tetapkan Harga Baru
          </span>
        </div>
        <form method="POST" action="{{ $outlet->route('products.price.update', [$product]) }}">
          @csrf
          @method('PATCH')
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px">

            <div>
              <label class="f-label">Harga Jual Baru <span style="color:#f87171">*</span></label>
              <div style="position:relative">
                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:600;color:var(--muted)">Rp</span>
                <input type="number" name="new_price" id="new_price" class="f-input"
                  style="padding-left:36px"
                  value="{{ old('new_price', $product->price) }}"
                  min="0" step="1" required
                  oninput="updatePreview(this.value)">
              </div>
              @error('new_price')
              <div style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</div>
              @enderror
            </div>

            {{-- Preview selisih --}}
            <div id="price-preview" style="padding:12px 14px;border-radius:12px;background:var(--surface2);border:1px solid var(--border);display:none">
              <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Preview Perubahan</div>
              <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span style="font-size:14px;font-weight:600;color:var(--sub)">Rp {{ number_format($product->price) }}</span>
                <i class="fa-solid fa-arrow-right" style="font-size:11px;color:var(--muted)"></i>
                <span id="preview-new" style="font-size:16px;font-weight:800;color:var(--text)">—</span>
                <span id="preview-diff" style="font-size:12px;font-weight:600;padding:2px 9px;border-radius:99px"></span>
              </div>
            </div>

            <div>
              <label class="f-label">Catatan / Alasan Perubahan</label>
              <input type="text" name="notes" class="f-input"
                placeholder="Contoh: Harga supplier naik, penyesuaian pasar, promo..."
                value="{{ old('notes') }}" maxlength="255">
              <div style="font-size:11px;color:var(--muted);margin-top:4px">Opsional. Dicatat di riwayat perubahan harga.</div>
            </div>

          </div>
          <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
            <a href="{{ $outlet->route('products.index') }}"
               style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center">
              Batal
            </a>
            <button type="submit"
              style="padding:9px 22px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px">
              <i class="fa-solid fa-check"></i>Simpan Harga Baru
            </button>
          </div>
        </form>
      </div>

    </div>

    {{-- ── KOLOM KANAN: Riwayat Perubahan Harga ── --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Riwayat Perubahan Harga
        </span>
        <span style="font-size:11.5px;color:var(--muted)">{{ $histories->count() }} catatan</span>
      </div>

      @if($histories->isEmpty())
      <div class="card-body" style="padding:40px 20px;text-align:center">
        <div style="width:48px;height:48px;border-radius:14px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 12px;font-size:20px;color:var(--muted)">
          <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:4px">Belum ada riwayat</div>
        <div style="font-size:12px;color:var(--muted)">Setiap perubahan harga akan tercatat di sini.</div>
      </div>
      @else
      <div style="padding:8px 0">
        @foreach($histories as $h)
        @php
          $diff    = $h->priceDiff();
          $diffPct = $h->priceDiffPct();
          $isUp    = $diff > 0;
        @endphp
        <div style="padding:14px 20px;border-bottom:1px solid var(--border){{ $loop->last ? ';border-bottom:none' : '' }}">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:6px">
            <div style="display:flex;align-items:center;gap:8px">
              <div style="width:28px;height:28px;border-radius:8px;display:grid;place-items:center;flex-shrink:0;background:{{ $isUp ? 'rgba(248,113,113,.12)' : 'rgba(52,211,153,.12)' }};color:{{ $isUp ? '#f87171' : '#34d399' }};font-size:11px">
                <i class="fa-solid {{ $isUp ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
              </div>
              <div>
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                  <span style="font-size:13px;font-weight:600;color:var(--sub)">Rp {{ number_format($h->old_price) }}</span>
                  <i class="fa-solid fa-arrow-right" style="font-size:10px;color:var(--muted)"></i>
                  <span style="font-size:14px;font-weight:700;color:var(--text)">Rp {{ number_format($h->new_price) }}</span>
                  <span style="font-size:11px;font-weight:600;padding:2px 7px;border-radius:99px;background:{{ $isUp ? 'rgba(248,113,113,.12)' : 'rgba(52,211,153,.12)' }};color:{{ $isUp ? '#f87171' : '#34d399' }}">
                    {{ $isUp ? '+' : '' }}{{ number_format(abs($diff)) }} ({{ $isUp ? '+' : '' }}{{ $diffPct }}%)
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted)">
              <i class="fa-solid fa-user" style="font-size:10px"></i>
              {{ $h->changedBy?->name ?? '—' }}
            </div>
            <div style="display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted)">
              <i class="fa-solid fa-calendar" style="font-size:10px"></i>
              {{ $h->created_at->format('d M Y, H:i') }}
            </div>
            @if($h->stock_at_change > 0)
            <div style="display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted)">
              <i class="fa-solid fa-boxes-stacked" style="font-size:10px"></i>
              Stok saat itu: {{ number_format($h->stock_at_change) }}
            </div>
            @endif
          </div>

          @if($h->notes)
          <div style="margin-top:7px;padding:7px 10px;border-radius:8px;background:var(--surface2);font-size:12px;color:var(--sub)">
            <i class="fa-solid fa-quote-left" style="font-size:10px;color:var(--muted);margin-right:4px"></i>{{ $h->notes }}
          </div>
          @endif
        </div>
        @endforeach
      </div>
      @endif
    </div>

  </div>
</div>

@push('scripts')
<script>
const oldPrice = {{ $product->price }};

function updatePreview(val) {
  const newPrice = parseInt(val) || 0;
  const preview  = document.getElementById('price-preview');
  const previewNew  = document.getElementById('preview-new');
  const previewDiff = document.getElementById('preview-diff');

  if (!val || newPrice === oldPrice) { preview.style.display = 'none'; return; }

  preview.style.display = 'block';
  previewNew.textContent = 'Rp ' + newPrice.toLocaleString('id-ID');

  const diff    = newPrice - oldPrice;
  const diffPct = oldPrice > 0 ? (diff / oldPrice * 100).toFixed(1) : 0;
  const isUp    = diff > 0;

  previewDiff.textContent  = (isUp ? '+' : '') + Math.abs(diff).toLocaleString('id-ID') + ' (' + (isUp ? '+' : '') + diffPct + '%)';
  previewDiff.style.background = isUp ? 'rgba(248,113,113,.12)' : 'rgba(52,211,153,.12)';
  previewDiff.style.color      = isUp ? '#f87171' : '#34d399';
  previewNew.style.color       = isUp ? '#f87171' : '#34d399';
}

document.addEventListener('DOMContentLoaded', () => {
  updatePreview(document.getElementById('new_price').value);
});
</script>
@endpush

</x-retail-layout>
