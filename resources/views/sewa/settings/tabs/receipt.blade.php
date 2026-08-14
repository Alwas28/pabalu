<div class="card animate-fadeUp">
  <div class="card-header">
    <span class="card-title">Jenis Struk yang Dicetak</span>
  </div>
  <div class="card-body">
    <p style="font-size:12.5px;color:var(--muted);margin-bottom:16px;line-height:1.65">
      Pilih format yang dicetak saat kasir mencetak bukti transaksi sewa dari halaman Detail Sewa.
    </p>

    <form method="POST" action="{{ $outlet->route('settings.receipt.update') }}">
      @csrf
      @method('PATCH')

      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px">
        <label class="req-chip" style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;text-align:left">
          <input type="radio" name="rental_receipt_type" value="thermal" style="margin-top:3px" {{ $outlet->rental_receipt_type === 'thermal' ? 'checked' : '' }}>
          <span>
            <span style="display:block;font-size:13.5px;font-weight:700;color:var(--text)">
              <i class="fa-solid fa-receipt" style="margin-right:6px;color:var(--ac)"></i>Struk (Printer Thermal)
            </span>
            <span style="display:block;font-size:12px;color:var(--muted);margin-top:4px;font-weight:400">
              Format ringkas lebar 80mm, cocok untuk printer kasir thermal.
            </span>
          </span>
        </label>

        <label class="req-chip" style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;text-align:left">
          <input type="radio" name="rental_receipt_type" value="invoice" style="margin-top:3px" {{ $outlet->rental_receipt_type === 'invoice' ? 'checked' : '' }}>
          <span>
            <span style="display:block;font-size:13.5px;font-weight:700;color:var(--text)">
              <i class="fa-solid fa-file-invoice" style="margin-right:6px;color:var(--ac)"></i>Invoice (A4)
            </span>
            <span style="display:block;font-size:12px;color:var(--muted);margin-top:4px;font-weight:400">
              Format surat ukuran A4 lengkap dengan rincian sewa, cocok dicetak dengan printer biasa.
            </span>
          </span>
        </label>
      </div>

      <button type="submit" class="btn-save">
        <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan
      </button>
    </form>
  </div>
</div>
