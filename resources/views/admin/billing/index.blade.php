<x-app-layout title="Manajemen Tagihan">

  {{-- Stats --}}
  <div class="stat-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(148,163,184,.1);color:#94a3b8"><i class="fa-solid fa-file-invoice"></i></div>
      <div><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-label">Total Tagihan</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="fa-solid fa-hourglass-half"></i></div>
      <div><div class="stat-num">{{ $stats['unpaid'] }}</div><div class="stat-label">Belum Dibayar</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,.12);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
      <div><div class="stat-num">{{ $stats['paid'] }}</div><div class="stat-label">Lunas</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(239,68,68,.12);color:#f87171"><i class="fa-solid fa-circle-exclamation"></i></div>
      <div><div class="stat-num">{{ $stats['overdue'] }}</div><div class="stat-label">Overdue</div></div>
    </div>
  </div>

  <div class="two-col">

    {{-- Form Buat Tagihan --}}
    <div class="card animate-fadeUp d1">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-plus-circle" style="color:var(--ac);margin-right:8px"></i>
          Buat Tagihan Baru
        </div>
      </div>
      <div class="card-body">
        @if(session('success'))
        <div style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);border-radius:10px;padding:10px 14px;font-size:13px;color:#34d399;margin-bottom:16px">
          <i class="fa-solid fa-circle-check" style="margin-right:6px"></i>{{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:10px 14px;font-size:13px;color:#f87171;margin-bottom:16px">
          <i class="fa-solid fa-circle-exclamation" style="margin-right:6px"></i>
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.billing.store') }}">
          @csrf

          <div class="f-group">
            <label class="f-label">Owner <span style="color:#f87171">*</span></label>
            <select name="user_id" class="f-input" required>
              <option value="">— Pilih Owner —</option>
              @foreach($owners as $o)
              <option value="{{ $o->id }}" @selected(old('user_id') == $o->id)>{{ $o->name }} ({{ $o->email }})</option>
              @endforeach
            </select>
            @error('user_id')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
          </div>

          <div class="f-group">
            <label class="f-label">Deskripsi Tagihan <span style="color:#f87171">*</span></label>
            <input type="text" name="description" class="f-input" placeholder="Contoh: Biaya Langganan Pabalu" value="{{ old('description') }}" required>
            @error('description')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="f-group">
              <label class="f-label">Periode <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
              <input type="text" name="period_label" class="f-input" placeholder="Apr 2026" value="{{ old('period_label') }}">
            </div>
            <div class="f-group">
              <label class="f-label">Jatuh Tempo <span style="color:#f87171">*</span></label>
              <input type="date" name="due_date" class="f-input" value="{{ old('due_date', today()->addDays(7)->toDateString()) }}" required>
              @error('due_date')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="f-group">
            <label class="f-label">Jumlah (Rp) <span style="color:#f87171">*</span></label>
            <input type="number" name="amount" class="f-input" placeholder="100000" min="1000" step="1000" value="{{ old('amount') }}" required>
            @error('amount')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%">
            <i class="fa-solid fa-paper-plane"></i> Kirim Tagihan
          </button>
        </form>
      </div>
    </div>

    {{-- List Tagihan --}}
    <div class="card animate-fadeUp d2">
      <div class="card-header" style="flex-wrap:wrap;gap:10px">
        <div class="card-title">
          <i class="fa-solid fa-list" style="color:var(--ac);margin-right:8px"></i>
          Riwayat Tagihan
        </div>
        <form method="GET" action="{{ route('admin.billing.index') }}" style="display:flex;gap:8px;flex-wrap:wrap">
          <select name="owner_id" class="f-input" style="padding:6px 10px;font-size:12px;width:auto" onchange="this.form.submit()">
            <option value="">— Semua Owner —</option>
            @foreach($owners as $o)
            <option value="{{ $o->id }}" @selected(request('owner_id') == $o->id)>{{ $o->name }}</option>
            @endforeach
          </select>
          <select name="status" class="f-input" style="padding:6px 10px;font-size:12px;width:auto" onchange="this.form.submit()">
            <option value="">— Semua Status —</option>
            <option value="unpaid"    @selected(request('status')==='unpaid')>Belum Bayar</option>
            <option value="paid"      @selected(request('status')==='paid')>Lunas</option>
            <option value="cancelled" @selected(request('status')==='cancelled')>Dibatalkan</option>
          </select>
        </form>
      </div>
      <div class="card-body" style="padding:0">
        @forelse($invoices as $inv)
        @php
          $isOverdue = $inv->status === 'unpaid' && $inv->due_date->isPast();
          $isDueSoon = $inv->isDueSoon();
        @endphp
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;gap:14px;align-items:flex-start">
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
              <span style="font-weight:600;font-size:13.5px">{{ $inv->owner->name ?? '—' }}</span>
              @if($inv->period_label)
              <span style="font-size:11px;padding:2px 7px;border-radius:99px;background:var(--surface2);color:var(--sub)">{{ $inv->period_label }}</span>
              @endif
              @if($inv->status === 'paid')
                <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:rgba(16,185,129,.15);color:#34d399">Lunas</span>
              @elseif($inv->status === 'cancelled')
                <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:rgba(100,116,139,.15);color:#94a3b8">Dibatalkan</span>
              @elseif($isOverdue)
                <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:rgba(239,68,68,.15);color:#f87171">Overdue</span>
              @elseif($isDueSoon)
                <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:rgba(245,158,11,.15);color:#f59e0b">Jatuh Tempo Segera</span>
              @else
                <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:rgba(148,163,184,.1);color:#94a3b8">Unpaid</span>
              @endif
            </div>
            <div style="font-size:12.5px;color:var(--sub)">{{ $inv->description }}</div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px">
              Jatuh tempo: {{ $inv->due_date->isoFormat('D MMM YYYY') }}
              @if($inv->paid_at) · Dibayar: {{ $inv->paid_at->isoFormat('D MMM YYYY HH:mm') }} @endif
            </div>
          </div>
          <div style="text-align:right;flex-shrink:0">
            <div style="font-weight:700;font-size:14px;color:var(--ac)">Rp {{ number_format($inv->amount, 0, ',', '.') }}</div>
            @if($inv->status === 'unpaid')
            <button onclick="openCancelModal({{ $inv->id }}, '{{ addslashes($inv->owner->name ?? '—') }}', '{{ addslashes($inv->description) }}', 'Rp {{ number_format($inv->amount, 0, ',', '.') }}')"
              style="margin-top:6px;font-size:11px;padding:3px 10px;border-radius:8px;border:1px solid rgba(239,68,68,.4);background:rgba(239,68,68,.1);color:#f87171;cursor:pointer">
              <i class="fa-solid fa-ban"></i> Batalkan
            </button>
            @elseif($inv->status === 'cancelled')
            <button onclick="openDeleteModal({{ $inv->id }}, '{{ addslashes($inv->owner->name ?? '—') }}', '{{ addslashes($inv->description) }}', 'Rp {{ number_format($inv->amount, 0, ',', '.') }}')"
              style="margin-top:6px;font-size:11px;padding:3px 10px;border-radius:8px;border:1px solid rgba(100,116,139,.4);background:rgba(100,116,139,.1);color:#94a3b8;cursor:pointer">
              <i class="fa-solid fa-trash"></i> Hapus
            </button>
            @endif
            {{-- paid: tidak ada aksi --}}
          </div>
        </div>
        @empty
        <div style="text-align:center;padding:40px 20px;color:var(--muted);font-size:13px">
          <i class="fa-solid fa-file-invoice" style="font-size:28px;margin-bottom:10px;display:block;opacity:.4"></i>
          Belum ada tagihan.
        </div>
        @endforelse
      </div>
      @if($invoices->hasPages())
      <div style="padding:14px 20px;border-top:1px solid var(--border)">
        {{ $invoices->links() }}
      </div>
      @endif
    </div>

  </div>

  {{-- Form tersembunyi untuk cancel & delete --}}
  <form id="form-cancel" method="POST" style="display:none">
    @csrf
  </form>
  <form id="form-delete" method="POST" style="display:none">
    @csrf @method('DELETE')
  </form>

  {{-- Modal: Batalkan Tagihan --}}
  <div class="modal-backdrop" id="modal-cancel" onclick="if(event.target===this)closeModal('modal-cancel')">
    <div class="modal-box" style="max-width:420px">
      <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,.15);color:#f87171;display:grid;place-items:center;font-size:16px;flex-shrink:0">
            <i class="fa-solid fa-ban"></i>
          </div>
          <div>
            <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Batalkan Tagihan</div>
            <div style="font-size:12px;color:var(--muted);margin-top:1px">Tagihan dapat dihapus setelah dibatalkan</div>
          </div>
        </div>
        <button onclick="closeModal('modal-cancel')" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px">
        <div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:14px 16px;display:flex;flex-direction:column;gap:6px">
          <div style="font-size:12px;color:var(--muted)">Owner</div>
          <div id="cancel-owner" style="font-size:14px;font-weight:600;color:var(--text)"></div>
          <div id="cancel-desc"  style="font-size:12.5px;color:var(--sub)"></div>
          <div id="cancel-amount" style="font-size:15px;font-weight:700;color:#f87171;margin-top:2px"></div>
        </div>
        <div style="margin-top:14px;font-size:13px;color:var(--muted)">
          <i class="fa-solid fa-circle-info" style="color:var(--ac);margin-right:5px"></i>
          Tagihan ini akan ditandai sebagai <b>Dibatalkan</b>. Owner tidak akan bisa membayarnya lagi.
        </div>
      </div>
      <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-cancel')" class="btn">Kembali</button>
        <button type="button" onclick="submitCancel()"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 20px;border-radius:10px;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.4);color:#f87171;font-weight:600;font-size:13.5px;cursor:pointer;transition:background .15s"
          onmouseover="this.style.background='rgba(239,68,68,.25)'" onmouseout="this.style.background='rgba(239,68,68,.15)'">
          <i class="fa-solid fa-ban"></i> Ya, Batalkan
        </button>
      </div>
    </div>
  </div>

  {{-- Modal: Hapus Tagihan --}}
  <div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
    <div class="modal-box" style="max-width:420px">
      <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,.15);color:#f87171;display:grid;place-items:center;font-size:16px;flex-shrink:0">
            <i class="fa-solid fa-trash"></i>
          </div>
          <div>
            <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Hapus Tagihan</div>
            <div style="font-size:12px;color:var(--muted);margin-top:1px">Tindakan ini tidak dapat dibatalkan</div>
          </div>
        </div>
        <button onclick="closeModal('modal-delete')" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px">
        <div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:14px 16px;display:flex;flex-direction:column;gap:6px">
          <div style="font-size:12px;color:var(--muted)">Owner</div>
          <div id="delete-owner"  style="font-size:14px;font-weight:600;color:var(--text)"></div>
          <div id="delete-desc"   style="font-size:12.5px;color:var(--sub)"></div>
          <div id="delete-amount" style="font-size:15px;font-weight:700;color:#f87171;margin-top:2px"></div>
        </div>
        <div style="margin-top:14px;font-size:13px;color:var(--muted)">
          <i class="fa-solid fa-triangle-exclamation" style="color:#f87171;margin-right:5px"></i>
          Data tagihan akan <b style="color:#f87171">dihapus permanen</b> dan tidak bisa dipulihkan.
        </div>
      </div>
      <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-delete')" class="btn">Kembali</button>
        <button type="button" onclick="submitDelete()"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 20px;border-radius:10px;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-weight:600;font-size:13.5px;border:none;cursor:pointer;transition:opacity .15s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
          <i class="fa-solid fa-trash"></i> Ya, Hapus Permanen
        </button>
      </div>
    </div>
  </div>

@push('scripts')
<script>
var cancelUrl = null;
var deleteUrl = null;

function openCancelModal(id, owner, desc, amount) {
  cancelUrl = '/admin/billing/' + id + '/cancel';
  document.getElementById('cancel-owner').textContent  = owner;
  document.getElementById('cancel-desc').textContent   = desc;
  document.getElementById('cancel-amount').textContent = amount;
  openModal('modal-cancel');
}

function submitCancel() {
  var form = document.getElementById('form-cancel');
  form.action = cancelUrl;
  form.submit();
}

function openDeleteModal(id, owner, desc, amount) {
  deleteUrl = '/admin/billing/' + id;
  document.getElementById('delete-owner').textContent  = owner;
  document.getElementById('delete-desc').textContent   = desc;
  document.getElementById('delete-amount').textContent = amount;
  openModal('modal-delete');
}

function submitDelete() {
  var form = document.getElementById('form-delete');
  form.action = deleteUrl;
  form.submit();
}
</script>
@endpush

</x-app-layout>
