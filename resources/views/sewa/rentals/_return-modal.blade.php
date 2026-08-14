{{-- ══ MODAL PENGEMBALIAN ══ --}}
{{-- Catatan: partial ini mengasumsikan `outletId`, `outletRp`, `fmtRp()`, `openModal()`, `closeModal()`
     sudah dideklarasikan lebih dulu di halaman yang meng-include-nya (hindari redeklarasi const/function ganda). --}}
<div class="modal-backdrop" id="modal-return" onclick="if(event.target===this)closeModal('modal-return')">
  <div class="modal-box" style="max-width:460px">
    <form id="form-return" method="POST" action="">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Proses Pengembalian</h3>
        <button type="button" onclick="closeModal('modal-return')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div style="font-size:13px;color:var(--sub)">Transaksi: <strong id="ret-order" style="color:var(--text)"></strong></div>
        <div id="ret-unpaid-warning" style="display:none;padding:10px 12px;border-radius:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);font-size:12px;color:#f87171">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right:5px"></i>
          Biaya sewa belum lunas — sisa tagihan <strong id="ret-unpaid-amount"></strong>.
        </div>

        <div>
          <label class="f-label">Catatan Kondisi Barang <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="condition_note" class="f-input" rows="2" maxlength="1000" placeholder="cth: Lengkap dan baik, ada goresan di body..."></textarea>
        </div>

        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--sub)">
          <input type="checkbox" name="is_damaged" id="ret-damaged" value="1" style="accent-color:var(--ac);width:16px;height:16px">
          Barang rusak saat dikembalikan (unit akan ditandai "Rusak")
        </label>

        <div>
          <label class="f-label">Denda <span style="color:var(--muted);font-weight:400">(opsional — keterlambatan/kerusakan)</span></label>
          <div style="position:relative">
            <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
            <input type="text" inputmode="numeric" id="ret-fine-display" class="f-input" placeholder="0" style="padding-left:30px" oninput="formatRetFineInput()">
            <input type="hidden" name="fine_amount" id="ret-fine" value="{{ old('fine_amount') }}">
          </div>
        </div>

        <div style="padding:12px 14px;border-radius:10px;background:var(--surface2);font-size:12.5px;color:var(--sub);display:flex;justify-content:space-between">
          <span>Deposit: <strong id="ret-deposit-display" style="color:var(--text)"></strong></span>
          <span>Sisa dikembalikan: <strong id="ret-balance" style="color:var(--text)"></strong></span>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-return')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit" class="btn-save">Selesaikan Pengembalian</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
let retDeposit = 0;

function updateBalance() {
  const fine = parseInt(document.getElementById('ret-fine').value || '0', 10);
  document.getElementById('ret-deposit-display').textContent = fmtRp(retDeposit);
  document.getElementById('ret-balance').textContent = fmtRp(retDeposit - fine);
}

function formatRetFineInput() {
  const el = document.getElementById('ret-fine-display');
  const before = el.value.substring(0, el.selectionStart);
  const digitsBeforeCursor = (before.match(/\d/g) || []).length;
  const raw = el.value.replace(/\D/g, '');
  document.getElementById('ret-fine').value = raw;
  const formatted = raw ? Number(raw).toLocaleString('id-ID') : '';
  el.value = formatted;
  let seen = 0, newPos = formatted.length;
  for (let i = 0; i < formatted.length; i++) {
    if (/\d/.test(formatted[i])) seen++;
    if (seen === digitsBeforeCursor) { newPos = i + 1; break; }
  }
  try { el.setSelectionRange(newPos, newPos); } catch (_) {}
  updateBalance();
}

function openReturn(id, orderNumber, deposit, remaining) {
  document.getElementById('form-return').action = `/${outletRp}/${outletId}/rentals/${id}/return`;
  document.getElementById('ret-order').textContent = orderNumber;
  document.getElementById('ret-damaged').checked = false;
  document.getElementById('ret-fine-display').value = '';
  document.getElementById('ret-fine').value = '';
  retDeposit = deposit;
  updateBalance();

  const warn = document.getElementById('ret-unpaid-warning');
  if (remaining > 0) {
    document.getElementById('ret-unpaid-amount').textContent = fmtRp(remaining);
    warn.style.display = 'block';
  } else {
    warn.style.display = 'none';
  }

  openModal('modal-return');
}

// restore formatted display on validation error
(function() {
  const fineRaw = document.getElementById('ret-fine')?.value;
  if (fineRaw) document.getElementById('ret-fine-display').value = Number(fineRaw).toLocaleString('id-ID');
})();
</script>
@endpush
