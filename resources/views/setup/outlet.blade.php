<x-setup-layout :step="2" pageTitle="Outlet Pertama">

<div class="card animate-fadeUp" style="width:100%;max-width:600px">

  <div style="padding:24px 28px 0">
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Outlet Pertama Anda</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:6px;line-height:1.6">
      Outlet adalah lokasi fisik toko Anda. Anda bisa menambah outlet lain nanti dari menu Kelola Outlet.
    </p>
  </div>

  <form method="POST" action="{{ route('setup.outlet.save') }}" style="padding:24px 28px;display:flex;flex-direction:column;gap:20px">
    @csrf

    {{-- Nama Outlet --}}
    <div>
      <label class="f-label" for="outlet_name">
        Nama Outlet <span style="color:#f87171">*</span>
      </label>
      <input id="outlet_name" name="outlet_name" type="text" class="f-input"
        value="{{ old('outlet_name', session('setup_business_name')) }}"
        placeholder="Contoh: Toko Pak Budi – Cabang Utama"
        autofocus autocomplete="off">
      @error('outlet_name')
        <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
      @enderror
    </div>

    {{-- Jenis Outlet --}}
    <div>
      <label class="f-label">Jenis Usaha <span style="color:#f87171">*</span></label>
      <input type="hidden" name="outlet_type_id" id="outlet_type_id" value="{{ old('outlet_type_id') }}">

      <div class="type-cards" style="margin-top:8px">
        @foreach($outletTypes as $type)
        <div class="type-card {{ old('outlet_type_id') == $type->id ? 'selected' : '' }}"
          onclick="selectType(this, {{ $type->id }})"
          data-id="{{ $type->id }}">
          <div class="type-icon">
            <i class="fa-solid {{ $type->icon }}"></i>
          </div>
          <div class="type-name">{{ $type->name }}</div>
          @if($type->requires_opening_stock || $type->track_cogs)
          <div class="type-desc">
            {{ $type->requires_opening_stock ? '📋 Opening stok' : '' }}
            {{ $type->requires_opening_stock && $type->track_cogs ? ' · ' : '' }}
            {{ $type->track_cogs ? '💰 Tracking HPP' : '' }}
          </div>
          @endif
        </div>
        @endforeach
      </div>

      @error('outlet_type_id')
        <p style="font-size:12px;color:#f87171;margin-top:8px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
      @enderror
    </div>

    {{-- Alamat --}}
    <div>
      <label class="f-label" for="outlet_address">Alamat <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
      <textarea id="outlet_address" name="outlet_address" class="f-input" rows="2"
        placeholder="Jl. Contoh No. 1, Kota..."
        style="resize:vertical">{{ old('outlet_address') }}</textarea>
      @error('outlet_address')
        <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
      @enderror
    </div>

    <div style="display:flex;gap:10px">
      <a href="{{ route('setup.index') }}"
        style="padding:11px 20px;border-radius:12px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:14px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:7px">
        <i class="fa-solid fa-arrow-left"></i> Kembali
      </a>
      <button type="submit" class="a-grad"
        style="flex:1;padding:11px;border-radius:12px;border:none;color:#fff;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px">
        Selesaikan Setup <i class="fa-solid fa-arrow-right"></i>
      </button>
    </div>
  </form>

</div>

@push('scripts')
<script>
function selectType(el, id) {
  document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('outlet_type_id').value = id;
}
// Restore selection on validation error
document.addEventListener('DOMContentLoaded', () => {
  const val = document.getElementById('outlet_type_id').value;
  if (val) {
    const el = document.querySelector(`.type-card[data-id="${val}"]`);
    if (el) el.classList.add('selected');
  }
});
</script>
@endpush

</x-setup-layout>
