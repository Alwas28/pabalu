<div class="card animate-fadeUp">
  <div class="card-header">
    <span class="card-title">Fitur Booking</span>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ $outlet->route('settings.update') }}">
      @csrf
      @method('PATCH')

      <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding-bottom:18px">
        <div>
          <div style="font-size:13.5px;font-weight:700;color:var(--text)">Aktifkan Booking</div>
          <div style="font-size:12px;color:var(--muted);margin-top:4px;max-width:480px;line-height:1.65">
            Jika aktif, pelanggan bisa memesan barang/unit lebih dulu untuk tanggal sewa di masa depan (Booking).
            Jika nonaktif, menu Booking tetap tersedia di sidebar namun kasir hanya melayani transaksi Sewa langsung.
          </div>
        </div>
        <label class="sw-wrap">
          <input type="checkbox" name="enable_booking" value="1" {{ $outlet->enable_booking ? 'checked' : '' }}>
          <span class="sw-track"></span>
          <span class="sw-thumb"></span>
        </label>
      </div>

      <button type="submit" class="btn-save">
        <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan
      </button>
    </form>
  </div>
</div>
