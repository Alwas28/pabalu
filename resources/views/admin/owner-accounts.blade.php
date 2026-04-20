<x-app-layout title="Akun Owner">

@push('styles')
<style>
.toggle-wrap{display:flex;align-items:center;gap:10px}
.toggle{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-slider{position:absolute;inset:0;border-radius:99px;background:var(--border);cursor:pointer;transition:background .2s}
.toggle-slider:before{content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:transform .2s}
.toggle input:checked + .toggle-slider{background:var(--ac)}
.toggle input:checked + .toggle-slider:before{transform:translateX(20px)}
</style>
@endpush

  {{-- Stats --}}
  <div class="stat-grid" style="grid-template-columns:repeat(5,1fr)">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(148,163,184,.1);color:#94a3b8"><i class="fa-solid fa-users"></i></div>
      <div><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-label">Total Owner</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="fa-solid fa-hourglass-half"></i></div>
      <div><div class="stat-num">{{ $stats['trial'] }}</div><div class="stat-label">Trial Aktif</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(99,102,241,.12);color:#818cf8"><i class="fa-solid fa-crown"></i></div>
      <div><div class="stat-num">{{ $stats['premium'] }}</div><div class="stat-label">Premium</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(239,68,68,.12);color:#f87171"><i class="fa-solid fa-clock-rotate-left"></i></div>
      <div><div class="stat-num">{{ $stats['expired'] }}</div><div class="stat-label">Trial Expired</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(100,116,139,.12);color:#64748b"><i class="fa-solid fa-ban"></i></div>
      <div><div class="stat-num">{{ $stats['inactive'] }}</div><div class="stat-label">Nonaktif</div></div>
    </div>
  </div>

  {{-- Filter --}}
  <form method="GET" action="{{ route('admin.owner-accounts.index') }}">
    <div style="display:grid;grid-template-columns:1fr auto auto;gap:10px;align-items:end">
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Cari Owner</label>
        <div style="position:relative">
          <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
          <input type="text" name="q" class="f-input" style="padding-left:36px" placeholder="Nama atau email..." value="{{ request('q') }}">
        </div>
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Status</label>
        <select name="status" class="f-input" style="width:auto">
          <option value="">Semua Status</option>
          <option value="trial"    @selected(request('status')==='trial')>Trial Aktif</option>
          <option value="premium"  @selected(request('status')==='premium')>Premium</option>
          <option value="expired"  @selected(request('status')==='expired')>Trial Expired</option>
          <option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="padding:9px 16px">
        <i class="fa-solid fa-filter"></i> Filter
      </button>
    </div>
  </form>

  {{-- Table --}}
  <div class="card animate-fadeUp">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-user-tie" style="color:var(--ac);margin-right:8px"></i>Daftar Akun Owner
      </div>
      <div style="font-size:12px;color:var(--muted)">{{ $owners->total() }} owner</div>
    </div>

    @if($owners->isEmpty())
    <div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
      <i class="fa-solid fa-user-slash" style="font-size:36px;margin-bottom:12px;display:block;opacity:.4"></i>
      <div style="font-size:14px">Tidak ada owner ditemukan.</div>
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Owner</th>
            <th>Outlet</th>
            <th>Jenis Akun</th>
            <th>Status Trial</th>
            @if(\App\Models\Setting::get('midtrans_enabled') === '1')
            <th>Midtrans</th>
            @endif
            <th>Bergabung</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($owners as $owner)
          @php
            $isExpired  = $owner->isTrialExpired();
            $daysLeft   = $owner->trialDaysLeft();
            $isInactive = $owner->account_type === 'inactive';
            $isPremium  = $owner->account_type === 'premium';
            $ownerMidtransEnabled = \App\Models\OwnerSetting::get('midtrans_enabled', $owner->id) === '1';
            $ownerHasKey          = (bool) \App\Models\OwnerSetting::get('midtrans_server_key', $owner->id);
            $ownerMidtransOk      = $ownerMidtransEnabled && $ownerHasKey;
          @endphp
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:10px;flex-shrink:0;
                  background:{{ $isInactive ? 'rgba(100,116,139,.15)' : ($isPremium ? 'rgba(99,102,241,.15)' : 'rgba(245,158,11,.15)') }};
                  color:{{ $isInactive ? '#64748b' : ($isPremium ? '#818cf8' : '#f59e0b') }};
                  display:grid;place-items:center;font-size:15px;font-weight:700;font-family:\'Clash Display\',sans-serif">
                  {{ strtoupper(mb_substr($owner->name, 0, 1)) }}
                </div>
                <div>
                  <div style="font-weight:600;color:var(--text);font-size:13px">{{ $owner->name }}</div>
                  <div style="font-size:11.5px;color:var(--muted)">{{ $owner->email }}</div>
                </div>
              </div>
            </td>
            <td>
              <span style="font-size:13px;color:var(--sub)">
                {{ $owner->ownedOutlets->count() }} outlet
              </span>
            </td>
            <td>
              @if($isPremium)
                <span class="badge" style="background:rgba(99,102,241,.12);color:#818cf8">
                  <i class="fa-solid fa-crown"></i> Premium
                </span>
              @elseif($isInactive)
                <span class="badge badge-gray">
                  <i class="fa-solid fa-ban"></i> Nonaktif
                </span>
              @elseif($isExpired)
                <span class="badge badge-red">
                  <i class="fa-solid fa-hourglass-end"></i> Trial Expired
                </span>
              @else
                <span class="badge badge-amber">
                  <i class="fa-solid fa-hourglass-half"></i> Trial
                </span>
              @endif
            </td>
            <td>
              @if($isPremium)
                <span style="font-size:12.5px;color:#34d399"><i class="fa-solid fa-infinity"></i> Tidak terbatas</span>
              @elseif($isInactive)
                <span style="font-size:12.5px;color:#64748b">—</span>
              @elseif($isExpired)
                <span style="font-size:12.5px;color:#f87171">
                  <i class="fa-solid fa-circle-xmark"></i>
                  Berakhir {{ $owner->trial_ends_at->diffForHumans() }}
                </span>
              @else
                <div style="font-size:12.5px;color:{{ $daysLeft <= 5 ? '#f87171' : ($daysLeft <= 10 ? '#fbbf24' : '#34d399') }}">
                  <i class="fa-solid fa-clock"></i> {{ $daysLeft }} hari lagi
                </div>
                <div style="font-size:11px;color:var(--muted);margin-top:1px">
                  s/d {{ $owner->trial_ends_at->translatedFormat('d M Y') }}
                </div>
              @endif
            </td>
            @if(\App\Models\Setting::get('midtrans_enabled') === '1')
            <td>
              @if($ownerMidtransOk)
                <span class="badge badge-green" style="font-size:11px">
                  <i class="fa-solid fa-circle" style="font-size:6px"></i> Terhubung
                </span>
              @elseif($ownerHasKey)
                <span class="badge badge-amber" style="font-size:11px">
                  <i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i> Nonaktif
                </span>
              @else
                <span class="badge badge-gray" style="font-size:11px">Belum</span>
              @endif
            </td>
            @endif
            <td style="font-size:12px;color:var(--muted);white-space:nowrap">
              {{ $owner->created_at->translatedFormat('d M Y') }}
            </td>
            <td style="text-align:right">
              <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap">
                {{-- Detail --}}
                <a href="{{ route('users.owner-detail', $owner) }}"
                  style="padding:6px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:color .15s"
                  onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">
                  <i class="fa-solid fa-chart-line"></i> Detail
                </a>

                @if(\App\Models\Setting::get('midtrans_enabled') === '1')
                {{-- Konfigurasi Midtrans --}}
                <button onclick="openMidtrans({{ $owner->id }}, '{{ addslashes($owner->name) }}')"
                  style="padding:6px 10px;border-radius:8px;border:1px solid rgba(52,211,153,.3);background:rgba(52,211,153,.08);color:#34d399;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px">
                  <i class="fa-solid fa-credit-card"></i>
                </button>
                @endif

                @if(!$isPremium && !$isInactive)
                {{-- Set Premium --}}
                <button onclick="openPremium({{ $owner->id }}, '{{ addslashes($owner->name) }}')"
                  style="padding:6px 10px;border-radius:8px;border:1px solid rgba(99,102,241,.3);background:rgba(99,102,241,.1);color:#818cf8;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px">
                  <i class="fa-solid fa-crown"></i> Premium
                </button>
                @endif

                @if(!$isInactive)
                {{-- Perpanjang Trial --}}
                <button onclick="openExtend({{ $owner->id }}, '{{ addslashes($owner->name) }}')"
                  style="padding:6px 10px;border-radius:8px;border:1px solid rgba(245,158,11,.3);background:rgba(245,158,11,.08);color:#f59e0b;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px">
                  <i class="fa-solid fa-rotate-right"></i> Trial
                </button>

                {{-- Nonaktifkan --}}
                <button onclick="openDeactivate({{ $owner->id }}, '{{ addslashes($owner->name) }}')"
                  style="padding:6px 10px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);color:#f87171;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px">
                  <i class="fa-solid fa-ban"></i>
                </button>
                @else
                {{-- Aktifkan kembali --}}
                <button onclick="openActivate({{ $owner->id }}, '{{ addslashes($owner->name) }}')"
                  style="padding:6px 10px;border-radius:8px;border:1px solid rgba(52,211,153,.3);background:rgba(52,211,153,.1);color:#34d399;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px">
                  <i class="fa-solid fa-circle-check"></i> Aktifkan
                </button>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($owners->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
      {{ $owners->links() }}
    </div>
    @endif
    @endif
  </div>

  {{-- Modal Perpanjang Trial --}}
  <div class="modal-backdrop" id="modal-extend" onclick="if(event.target===this)closeModal('modal-extend')">
    <div class="modal-box">
      <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div>
          <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Perpanjang / Atur Trial</div>
          <div id="extend-name" style="font-size:12px;color:var(--muted);margin-top:1px"></div>
        </div>
        <button onclick="closeModal('modal-extend')" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="extend-form" method="POST">
        @csrf
        <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
          <div class="f-group" style="margin-bottom:0">
            <label class="f-label">Durasi Trial (hari)</label>
            <input type="number" name="days" class="f-input" value="30" min="1" max="365" required>
            <div style="font-size:11.5px;color:var(--muted);margin-top:5px">Trial akan dihitung dari sekarang.</div>
          </div>
        </div>
        <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
          <button type="button" onclick="closeModal('modal-extend')" class="btn">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Set Premium --}}
  <div class="modal-backdrop" id="modal-premium" onclick="if(event.target===this)closeModal('modal-premium')">
    <div class="modal-box" style="max-width:420px">
      <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(99,102,241,.15);color:#818cf8;display:grid;place-items:center;font-size:16px;flex-shrink:0">
            <i class="fa-solid fa-crown"></i>
          </div>
          <div>
            <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Upgrade ke Premium</div>
            <div id="premium-name" style="font-size:12px;color:var(--muted);margin-top:1px"></div>
          </div>
        </div>
        <button onclick="closeModal('modal-premium')" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px">
        <p style="font-size:13.5px;color:var(--sub);line-height:1.7">
          Akun <strong id="premium-name2" style="color:var(--text)"></strong> akan diubah menjadi
          <strong style="color:#818cf8">Premium</strong> dengan akses tidak terbatas.
          Tindakan ini tidak dapat dibatalkan secara otomatis.
        </p>
      </div>
      <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-premium')" class="btn">Batal</button>
        <form id="premium-form" method="POST" style="display:inline">
          @csrf
          <button type="submit" style="padding:9px 18px;border-radius:10px;border:none;background:rgba(99,102,241,.85);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px">
            <i class="fa-solid fa-crown"></i> Ya, Jadikan Premium
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal Nonaktifkan --}}
  <div class="modal-backdrop" id="modal-deactivate" onclick="if(event.target===this)closeModal('modal-deactivate')">
    <div class="modal-box" style="max-width:420px">
      <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,.12);color:#f87171;display:grid;place-items:center;font-size:16px;flex-shrink:0">
            <i class="fa-solid fa-ban"></i>
          </div>
          <div>
            <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Nonaktifkan Akun</div>
            <div id="deactivate-name" style="font-size:12px;color:var(--muted);margin-top:1px"></div>
          </div>
        </div>
        <button onclick="closeModal('modal-deactivate')" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px">
        <p style="font-size:13.5px;color:var(--sub);line-height:1.7">
          Akun <strong id="deactivate-name2" style="color:var(--text)"></strong> akan dinonaktifkan.
          Semua akses untuk owner dan kasir yang terkait akan <strong style="color:#f87171">diblokir sepenuhnya</strong>.
        </p>
        <div style="margin-top:12px;padding:10px 14px;border-radius:10px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);font-size:12px;color:#f87171;display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-triangle-exclamation"></i>
          Pastikan Anda yakin sebelum melanjutkan.
        </div>
      </div>
      <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-deactivate')" class="btn">Batal</button>
        <form id="deactivate-form" method="POST" style="display:inline">
          @csrf
          <button type="submit" class="btn btn-danger">
            <i class="fa-solid fa-ban"></i> Ya, Nonaktifkan
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal Aktifkan Kembali --}}
  <div class="modal-backdrop" id="modal-activate" onclick="if(event.target===this)closeModal('modal-activate')">
    <div class="modal-box" style="max-width:420px">
      <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(52,211,153,.12);color:#34d399;display:grid;place-items:center;font-size:16px;flex-shrink:0">
            <i class="fa-solid fa-circle-check"></i>
          </div>
          <div>
            <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Aktifkan Kembali</div>
            <div id="activate-name" style="font-size:12px;color:var(--muted);margin-top:1px"></div>
          </div>
        </div>
        <button onclick="closeModal('modal-activate')" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px">
        <p style="font-size:13.5px;color:var(--sub);line-height:1.7">
          Akun <strong id="activate-name2" style="color:var(--text)"></strong> akan diaktifkan kembali sebagai
          <strong style="color:#818cf8">Premium</strong> dengan akses penuh.
        </p>
      </div>
      <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-activate')" class="btn">Batal</button>
        <form id="activate-form" method="POST" style="display:inline">
          @csrf
          <button type="submit" style="padding:9px 18px;border-radius:10px;border:none;background:rgba(52,211,153,.85);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px">
            <i class="fa-solid fa-circle-check"></i> Ya, Aktifkan
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal Konfigurasi Midtrans (admin) --}}
  <div class="modal-backdrop" id="modal-midtrans" onclick="if(event.target===this)closeModal('modal-midtrans')">
    <div class="modal-box" style="max-width:500px">
      <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(52,211,153,.12);color:#34d399;display:grid;place-items:center;font-size:15px;flex-shrink:0">
            <i class="fa-solid fa-credit-card"></i>
          </div>
          <div>
            <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Konfigurasi Midtrans</div>
            <div id="midtrans-owner-name" style="font-size:12px;color:var(--muted);margin-top:1px"></div>
          </div>
        </div>
        <button onclick="closeModal('modal-midtrans')" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="midtrans-form" method="POST">
        @csrf
        <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px">

          {{-- Toggle aktif --}}
          <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Aktifkan Midtrans</div>
              <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Pembayaran online aktif untuk outlet owner ini</div>
            </div>
            <div class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" name="midtrans_enabled" value="1" id="modal-midtrans-enabled"
                  onchange="document.getElementById('lbl-midtrans-enabled').textContent=this.checked?'Aktif':'Nonaktif'">
                <span class="toggle-slider"></span>
              </label>
              <span style="font-size:12.5px;font-weight:600;color:var(--text);min-width:52px" id="lbl-midtrans-enabled">Nonaktif</span>
            </div>
          </div>

          {{-- Mode --}}
          <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Mode Produksi</div>
              <div style="font-size:11.5px;color:var(--muted);margin-top:2px">
                <span style="color:#f59e0b">Sandbox</span> = testing &nbsp;·&nbsp;
                <span style="color:#34d399">Production</span> = uang sungguhan
              </div>
            </div>
            <div class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" name="midtrans_is_production" value="1" id="modal-midtrans-prod"
                  onchange="var l=document.getElementById('lbl-midtrans-prod');l.textContent=this.checked?'Production':'Sandbox';l.style.color=this.checked?'#34d399':'#f59e0b'">
                <span class="toggle-slider"></span>
              </label>
              <span style="font-size:12.5px;font-weight:600;color:#f59e0b;min-width:72px" id="lbl-midtrans-prod">Sandbox</span>
            </div>
          </div>

          <div style="height:1px;background:var(--border)"></div>

          {{-- Server Key --}}
          <div class="f-group" style="margin-bottom:0">
            <label class="f-label">
              Server Key
              <span style="font-size:11px;color:#f87171;font-weight:400;margin-left:4px">Rahasia — jangan dibagikan</span>
            </label>
            <div style="position:relative">
              <input type="password" name="midtrans_server_key" id="modal-server-key" class="f-input"
                placeholder="SB-Mid-server-xxxx (Sandbox) / Mid-server-xxxx (Production)" autocomplete="off">
              <button type="button" onclick="toggleVis2('modal-server-key','eye2')"
                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px">
                <i class="fa-solid fa-eye" id="eye2"></i>
              </button>
            </div>
            <div style="font-size:11px;color:var(--muted);margin-top:4px">
              <i class="fa-solid fa-circle-info" style="margin-right:3px"></i>
              Dimulai dengan <code style="background:var(--surface2);padding:1px 5px;border-radius:4px;color:var(--ac)">Mid-server-</code> atau <code style="background:var(--surface2);padding:1px 5px;border-radius:4px;color:var(--ac)">SB-Mid-server-</code>
            </div>
          </div>

          {{-- Client Key --}}
          <div class="f-group" style="margin-bottom:0">
            <label class="f-label">Client Key <span style="font-size:11px;color:var(--muted);font-weight:400;margin-left:4px">Untuk frontend</span></label>
            <input type="text" name="midtrans_client_key" id="modal-client-key" class="f-input"
              placeholder="SB-Mid-client-xxxx (Sandbox) / Mid-client-xxxx (Production)" autocomplete="off">
            <div style="font-size:11px;color:var(--muted);margin-top:4px">
              <i class="fa-solid fa-circle-info" style="margin-right:3px"></i>
              Dimulai dengan <code style="background:var(--surface2);padding:1px 5px;border-radius:4px;color:var(--ac)">Mid-client-</code> atau <code style="background:var(--surface2);padding:1px 5px;border-radius:4px;color:var(--ac)">SB-Mid-client-</code>
            </div>
          </div>

        </div>
        <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
          <button type="button" onclick="closeModal('modal-midtrans')" class="btn">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  @push('scripts')
  <script>
  function openExtend(userId, name) {
    document.getElementById('extend-name').textContent = name;
    document.getElementById('extend-form').action = '/admin/owner-accounts/' + userId + '/set-trial';
    openModal('modal-extend');
  }
  function openPremium(userId, name) {
    document.getElementById('premium-name').textContent = name;
    document.getElementById('premium-name2').textContent = name;
    document.getElementById('premium-form').action = '/admin/owner-accounts/' + userId + '/set-premium';
    openModal('modal-premium');
  }
  function openDeactivate(userId, name) {
    document.getElementById('deactivate-name').textContent = name;
    document.getElementById('deactivate-name2').textContent = name;
    document.getElementById('deactivate-form').action = '/admin/owner-accounts/' + userId + '/deactivate';
    openModal('modal-deactivate');
  }
  function openActivate(userId, name) {
    document.getElementById('activate-name').textContent = name;
    document.getElementById('activate-name2').textContent = name;
    document.getElementById('activate-form').action = '/admin/owner-accounts/' + userId + '/activate';
    openModal('modal-activate');
  }

  // Midtrans config — data di-load dari dataset tombol
  var ownerMidtransData = @json($owners->keyBy('id')->map(fn($o) => \App\Models\OwnerSetting::getForOwner($o->id)));
  function openMidtrans(userId, name) {
    var data    = ownerMidtransData[userId] || {};
    var enabled = data['midtrans_enabled'] === '1';
    var isProd  = data['midtrans_is_production'] === '1';
    document.getElementById('midtrans-owner-name').textContent = name;
    document.getElementById('midtrans-form').action = '/admin/owner-accounts/' + userId + '/payment-settings';
    document.getElementById('modal-midtrans-enabled').checked = enabled;
    document.getElementById('modal-midtrans-prod').checked    = isProd;
    document.getElementById('modal-server-key').value  = data['midtrans_server_key'] || '';
    document.getElementById('modal-client-key').value  = data['midtrans_client_key'] || '';
    document.getElementById('lbl-midtrans-enabled').textContent = enabled ? 'Aktif' : 'Nonaktif';
    var lp = document.getElementById('lbl-midtrans-prod');
    lp.textContent = isProd ? 'Production' : 'Sandbox';
    lp.style.color = isProd ? '#34d399' : '#f59e0b';
    openModal('modal-midtrans');
  }
  function toggleVis2(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon  = document.getElementById(iconId);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
  }
  </script>
  @endpush

</x-app-layout>
