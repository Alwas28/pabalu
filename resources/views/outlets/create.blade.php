<x-app-layout>
<x-slot name="pageTitle">Tambah Outlet Baru</x-slot>

<style>
.wiz-header{display:flex;align-items:center;gap:0;margin-bottom:28px;position:relative}
.wiz-step{display:flex;flex-direction:column;align-items:center;gap:6px;flex:1;position:relative;z-index:1}
.wiz-dot{width:34px;height:34px;border-radius:50%;border:2px solid var(--border);background:var(--surface2);display:grid;place-items:center;font-size:13px;font-weight:700;color:var(--muted);transition:all .2s}
.wiz-dot.active{border-color:var(--ac);background:var(--ac);color:#fff}
.wiz-dot.done{border-color:var(--ac);background:var(--ac-lt);color:var(--ac)}
.wiz-label{font-size:11px;font-weight:600;color:var(--muted);white-space:nowrap;transition:color .2s}
.wiz-label.active,.wiz-label.done{color:var(--text)}

/* ── Type cards ── */
.type-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px;margin-top:10px}
.type-card{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border-radius:14px;border:2px solid var(--border);background:var(--surface2);cursor:pointer;font-family:inherit;transition:border-color .15s,background .15s,box-shadow .15s;text-align:center;outline:none;min-height:96px;justify-content:center}
.type-card:hover{border-color:rgba(var(--ac-rgb),.4);background:var(--ac-lt2)}
.type-card.selected{border-color:var(--ac);background:var(--ac-lt);box-shadow:0 0 0 1px var(--ac-lt)}
.type-card .tc-icon{width:40px;height:40px;border-radius:11px;background:var(--surface2);display:grid;place-items:center;font-size:17px;color:var(--muted);transition:background .15s,color .15s;flex-shrink:0}
.type-card.selected .tc-icon{background:rgba(var(--ac-rgb),.2);color:var(--ac)}
.tc-name{font-size:12px;font-weight:600;color:var(--text);line-height:1.3}
.tc-hint{font-size:10px;color:var(--muted);line-height:1.2}

/* ── Bidang usaha chips ── */
.bu-chips{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px}
.bu-chip{padding:5px 13px;border-radius:99px;font-size:12px;font-weight:600;border:1.5px solid var(--border);background:var(--surface2);color:var(--sub);cursor:pointer;transition:border-color .15s,color .15s,background .15s}
.bu-chip:hover{border-color:rgba(var(--ac-rgb),.4);color:var(--text)}
.bu-chip.active{border-color:var(--ac);background:var(--ac-lt);color:var(--ac)}

/* ── Bidang usaha box ── */
#bidang-usaha-box{margin-top:14px;padding:16px;border-radius:14px;border:1.5px solid rgba(var(--ac-rgb),.3);background:var(--ac-lt2);display:none}

/* ── Mode cards ── */
.mode-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.mode-card{display:flex;align-items:flex-start;gap:12px;padding:16px;border-radius:12px;border:2px solid var(--border);cursor:pointer;transition:border-color .15s,background .15s}
.mode-card.selected{border-color:var(--ac);background:var(--ac-lt)}
.mode-card input[type=radio]{accent-color:var(--ac);margin-top:2px;flex-shrink:0}

/* ── Feature switch ── */
.feat-row{display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--border)}
.feat-row:last-child{border-bottom:none}
.sw-wrap{position:relative;display:inline-block;width:46px;height:25px;flex-shrink:0;cursor:pointer}
.sw-wrap input{opacity:0;width:0;height:0;position:absolute}
.sw-track{position:absolute;inset:0;border-radius:99px;background:var(--border);transition:background .2s}
.sw-thumb{position:absolute;top:3px;left:3px;width:19px;height:19px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.25);transition:transform .2s}
.sw-wrap input:checked~.sw-track{background:var(--ac)}
.sw-wrap input:checked~.sw-thumb{transform:translateX(21px)}

/* ── Buttons ── */
.btn-next{padding:10px 26px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:8px}
.btn-back{padding:10px 20px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;text-decoration:none}

.step-panel{display:none}
.step-panel.active{display:block}
.f-err{font-size:12px;color:#f87171;margin-top:5px;display:flex;align-items:center;gap:4px}

/* ── Searchable Select ── */
.ss-wrap{position:relative}
.ss-box{position:relative;display:flex;align-items:center}
.ss-input{padding-right:32px!important;cursor:pointer;user-select:none}
.ss-input.typing{cursor:text;user-select:auto}
.ss-caret{position:absolute;right:11px;font-size:10px;color:var(--muted);pointer-events:none;transition:transform .2s}
.ss-wrap.open .ss-caret{transform:rotate(180deg)}
.ss-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:300;background:var(--surface);border:1px solid var(--border);border-radius:12px;max-height:210px;overflow-y:auto;display:none;box-shadow:0 8px 28px rgba(0,0,0,.4);scrollbar-width:thin;scrollbar-color:var(--border) transparent}
.ss-wrap.open .ss-dropdown{display:block}
.ss-option{padding:9px 13px;font-size:13px;color:var(--sub);cursor:pointer;transition:background .1s,color .1s;border-radius:0}
.ss-option:first-child{border-radius:12px 12px 0 0}
.ss-option:last-child{border-radius:0 0 12px 12px}
.ss-option:hover,.ss-option.focused{background:var(--surface2);color:var(--text)}
.ss-option.selected{color:var(--ac);background:var(--ac-lt);font-weight:600}
.ss-empty{padding:14px 13px;font-size:12px;color:var(--muted);text-align:center}
.ss-disabled .ss-input{opacity:.5;pointer-events:none}
</style>

<div style="max-width:680px;margin:0 auto">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:24px;font-size:13px;color:var(--muted)">
    <a href="{{ route('outlets.index') }}" style="color:var(--muted);text-decoration:none"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-shop"></i> Kelola Outlet
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">Tambah Outlet</span>
  </div>

  {{-- Step Indicator --}}
  <div style="display:flex;align-items:center;margin-bottom:28px">
    <div class="wiz-step">
      <div class="wiz-dot active" id="sd-1">1</div>
      <div class="wiz-label active" id="sl-1">Jenis & Info</div>
    </div>
    <div style="flex:1;height:2px;background:var(--border);margin-bottom:18px;transition:background .3s" id="wl-1"></div>
    <div class="wiz-step">
      <div class="wiz-dot" id="sd-2">2</div>
      <div class="wiz-label" id="sl-2">Operasional</div>
    </div>
    <div style="flex:1;height:2px;background:var(--border);margin-bottom:18px;transition:background .3s" id="wl-2"></div>
    <div class="wiz-step">
      <div class="wiz-dot" id="sd-3">3</div>
      <div class="wiz-label" id="sl-3">Fitur</div>
    </div>
  </div>

  <form method="POST" action="{{ route('outlets.store') }}" id="create-form" novalidate>
    @csrf
    <input type="hidden" name="outlet_type_id" id="outlet_type_id" value="{{ old('outlet_type_id') }}">

    {{-- ══════════════════════════════════════════ --}}
    {{-- STEP 1: Jenis Usaha & Info Dasar         --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="step-panel active" id="step-1">

      {{-- Pilih Jenis Usaha --}}
      <div class="card animate-fadeUp" style="margin-bottom:16px">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:14px;flex-shrink:0">
            <i class="fa-solid fa-store"></i>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:var(--text)">Jenis Usaha</div>
            <div style="font-size:12px;color:var(--muted)">Pilih jenis outlet Anda</div>
          </div>
        </div>
        <div style="padding:18px 22px" id="type-section-content">
          <div class="type-grid" id="type-cards">
            @foreach($outletTypes as $type)
            <button type="button"
              class="type-card {{ old('outlet_type_id') == $type->id ? 'selected' : '' }}"
              data-id="{{ $type->id }}"
              data-prefix="{{ $type->route_prefix }}"
              data-opening="{{ $type->requires_opening_stock ? '1' : '0' }}"
              onclick="pickType(this)">
              <div class="tc-icon"><i class="fa-solid {{ $type->icon }}"></i></div>
              <div class="tc-name">{{ $type->name }}</div>
              @if($type->requires_opening_stock)
              <div class="tc-hint">Opening stok</div>
              @endif
            </button>
            @endforeach
          </div>
          <div id="err-type" class="f-err" style="display:none">
            <i class="fa-solid fa-circle-exclamation"></i> Pilih jenis usaha terlebih dahulu
          </div>
          @error('outlet_type_id')
          <div class="f-err"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
          @enderror

          {{-- Bidang Usaha — muncul ketika Retail dipilih --}}
          <div id="bidang-usaha-box">
            <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px">
              <i class="fa-solid fa-tag" style="color:var(--ac);margin-right:5px;font-size:11px"></i>
              Bidang Usaha Toko
            </div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:10px">
              Pilih atau ketik bidang usaha toko Anda
            </div>
            <div class="bu-chips" id="bu-chips">
              @foreach(['Toko Kelontong','Toko Sembako','Minimarket','Toko Pakaian','Butik / Distro','Toko Elektronik','Toko Handphone','Toko Bangunan','Toko Sepatu','Toko Tani','Toko Kosmetik','Toko Mainan'] as $sug)
              <button type="button" class="bu-chip" onclick="pickBidang('{{ $sug }}', this)">{{ $sug }}</button>
              @endforeach
            </div>
            <div style="margin-top:12px">
              <label class="f-label" style="font-size:11.5px">Atau ketik sendiri <span style="color:#f87171">*</span></label>
              <input type="text" name="bidang_usaha" id="bidang_usaha_input" class="f-input"
                style="margin-top:4px"
                placeholder="Contoh: Toko Bangunan, Toko Tas..."
                value="{{ old('bidang_usaha') }}"
                oninput="syncChips(this.value)">
              <div id="err-bidang" class="f-err" style="display:none">
                <i class="fa-solid fa-circle-exclamation"></i> Bidang usaha wajib diisi
              </div>
              @error('bidang_usaha')
              <div class="f-err"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
              @enderror
            </div>
          </div>

        </div>
      </div>

      {{-- ── Card Lokasi Outlet ── --}}
      <div class="card animate-fadeUp d1" style="margin-bottom:16px;position:relative;z-index:2">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:9px;background:rgba(16,185,129,.14);color:#34d399;display:grid;place-items:center;font-size:14px;flex-shrink:0">
            <i class="fa-solid fa-map-location-dot"></i>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:var(--text)">Lokasi Outlet</div>
            <div style="font-size:12px;color:var(--muted)">Provinsi, kabupaten, kecamatan, dan desa/kelurahan</div>
          </div>
        </div>
        <div style="padding:18px 22px;display:flex;flex-direction:column;gap:14px">

          {{-- Baris 1: Provinsi + Kabupaten --}}
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
              <label class="f-label">Provinsi</label>
              <div class="ss-wrap" id="prov-wrap">
                <div class="ss-box">
                  <input type="text" class="ss-input f-input" id="prov-input"
                    placeholder="— Cari provinsi —" autocomplete="off">
                  <i class="fa-solid fa-chevron-down ss-caret"></i>
                </div>
                <input type="hidden" name="province_id" id="province_id" value="{{ old('province_id') }}">
                <div class="ss-dropdown" id="prov-dropdown"></div>
              </div>
            </div>
            <div>
              <label class="f-label">Kabupaten / Kota</label>
              <div class="ss-wrap ss-disabled" id="reg-wrap">
                <div class="ss-box">
                  <input type="text" class="ss-input f-input" id="reg-input"
                    placeholder="— Pilih provinsi dulu —" autocomplete="off">
                  <i class="fa-solid fa-chevron-down ss-caret"></i>
                </div>
                <input type="hidden" name="regency_id" id="regency_id" value="{{ old('regency_id') }}">
                <div class="ss-dropdown" id="reg-dropdown"></div>
              </div>
            </div>
          </div>

          {{-- Baris 2: Kecamatan + Desa/Kelurahan --}}
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
              <label class="f-label">Kecamatan</label>
              <div class="ss-wrap ss-disabled" id="dist-wrap">
                <div class="ss-box">
                  <input type="text" class="ss-input f-input" id="dist-input"
                    placeholder="— Pilih kab/kota dulu —" autocomplete="off">
                  <i class="fa-solid fa-chevron-down ss-caret"></i>
                </div>
                <input type="hidden" name="district_id" id="district_id" value="{{ old('district_id') }}">
                <div class="ss-dropdown" id="dist-dropdown"></div>
              </div>
            </div>
            <div>
              <label class="f-label">Desa / Kelurahan</label>
              <input type="text" name="kelurahan" class="f-input"
                placeholder="Nama desa atau kelurahan"
                value="{{ old('kelurahan') }}">
            </div>
          </div>

        </div>
      </div>

      {{-- Informasi Outlet --}}
      <div class="card animate-fadeUp d1" style="position:relative;z-index:1">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:9px;background:rgba(99,102,241,.14);color:#818cf8;display:grid;place-items:center;font-size:14px;flex-shrink:0">
            <i class="fa-solid fa-pen-to-square"></i>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:var(--text)">Informasi Outlet</div>
            <div style="font-size:12px;color:var(--muted)">Nama, nomor, dan alamat outlet</div>
          </div>
        </div>
        <div style="padding:18px 22px;display:flex;flex-direction:column;gap:14px">
          <div>
            <label class="f-label" for="name">Nama Outlet <span style="color:#f87171">*</span></label>
            <input id="name" name="name" type="text" class="f-input"
              placeholder="Contoh: Warung Makan Bu Sari – Cabang Utama"
              value="{{ old('name') }}" autocomplete="off">
            <div id="err-name" class="f-err" style="display:none">
              <i class="fa-solid fa-circle-exclamation"></i> Nama outlet wajib diisi
            </div>
            @error('name')
            <div class="f-err"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
            @enderror
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
              <label class="f-label" for="phone">No. Telepon <span style="font-weight:400;color:var(--muted)">(opsional)</span></label>
              <input id="phone" name="phone" type="tel" class="f-input"
                placeholder="08xx-xxxx-xxxx" value="{{ old('phone') }}">
            </div>
            <div>
              <label class="f-label" for="address">Alamat <span style="font-weight:400;color:var(--muted)">(opsional)</span></label>
              <input id="address" name="address" type="text" class="f-input"
                placeholder="Jl. Contoh No. 1..." value="{{ old('address') }}">
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;margin-top:20px;align-items:center">
        <a href="{{ route('outlets.index') }}" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Batal
        </a>
        <button type="button" class="btn-next" onclick="goStep(2)">
          Lanjutkan <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- STEP 2: Mode Transaksi                    --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="step-panel" id="step-2">
      <div class="card animate-fadeUp" style="margin-bottom:16px">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:9px;background:rgba(139,92,246,.14);color:#a78bfa;display:grid;place-items:center;font-size:14px;flex-shrink:0">
            <i class="fa-solid fa-cash-register"></i>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:var(--text)">Mode Transaksi</div>
            <div style="font-size:12px;color:var(--muted)">Cara pelanggan melakukan pembayaran</div>
          </div>
        </div>

        {{-- F&B — pilih mode (quick / kitchen) --}}
        <div id="fnb-mode-section" style="display:none;padding:18px 22px">
          <div class="mode-grid">
            <label class="mode-card {{ old('order_mode','quick') === 'quick' ? 'selected' : '' }}" id="mc-quick">
              <input type="radio" name="order_mode" value="quick"
                {{ old('order_mode','quick') === 'quick' ? 'checked' : '' }}
                onchange="highlightMode('quick')">
              <div>
                <div style="font-size:13.5px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:6px">
                  <i class="fa-solid fa-bolt" style="color:#fbbf24;font-size:11px"></i>Quick Pay
                </div>
                <div style="font-size:12px;color:var(--muted);margin-top:4px;line-height:1.5">
                  Langsung bayar saat order. Cocok untuk warung, kasir tunggal.
                </div>
              </div>
            </label>
            <label class="mode-card {{ old('order_mode') === 'kitchen' ? 'selected' : '' }}" id="mc-kitchen">
              <input type="radio" name="order_mode" value="kitchen"
                {{ old('order_mode') === 'kitchen' ? 'checked' : '' }}
                onchange="highlightMode('kitchen')">
              <div>
                <div style="font-size:13.5px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:6px">
                  <i class="fa-solid fa-kitchen-set" style="color:#34d399;font-size:11px"></i>Kitchen Order
                </div>
                <div style="font-size:12px;color:var(--muted);margin-top:4px;line-height:1.5">
                  Pesan dulu, bayar nanti. Cocok untuk restoran dengan dapur.
                </div>
              </div>
            </label>
          </div>
        </div>

        {{-- Non-F&B — selalu Quick Pay --}}
        <div id="retail-mode-section" style="display:none;padding:18px 22px">
          <input type="hidden" name="order_mode" value="quick">
          <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;background:var(--ac-lt2);border:1px solid rgba(var(--ac-rgb),.2)">
            <div style="width:38px;height:38px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:15px;flex-shrink:0">
              <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
              <div style="font-size:13.5px;font-weight:700;color:var(--text)">Quick Pay</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">
                Jenis outlet ini menggunakan mode <strong>Quick Pay</strong> — pembayaran langsung di kasir.
              </div>
            </div>
          </div>
        </div>

        {{-- Placeholder sebelum jenis dipilih --}}
        <div id="no-type-section" style="padding:18px 22px">
          <div style="padding:14px 16px;border-radius:12px;background:var(--surface2);border:1px solid var(--border);text-align:center;color:var(--muted);font-size:13px">
            <i class="fa-solid fa-arrow-up-long" style="margin-right:6px;font-size:11px"></i>
            Pilih jenis usaha di langkah 1 terlebih dahulu
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;margin-top:20px;align-items:center">
        <button type="button" class="btn-back" onclick="goStep(1)">
          <i class="fa-solid fa-arrow-left"></i> Kembali
        </button>
        <button type="button" class="btn-next" onclick="goStep(3)">
          Lanjutkan <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- STEP 3: Fitur Aktif                       --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="step-panel" id="step-3">
      <div class="card animate-fadeUp" style="margin-bottom:24px">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:9px;background:rgba(20,184,166,.14);color:#2dd4bf;display:grid;place-items:center;font-size:14px;flex-shrink:0">
            <i class="fa-solid fa-sliders"></i>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:var(--text)">Fitur Aktif</div>
            <div style="font-size:12px;color:var(--muted)">Aktifkan fitur sesuai kebutuhan operasional</div>
          </div>
        </div>
        <div style="padding:4px 22px 0">

          <div class="feat-row">
            <div style="display:flex;align-items:center;gap:12px;padding-right:20px">
              <div style="width:38px;height:38px;border-radius:10px;background:rgba(251,191,36,.12);color:#fbbf24;display:grid;place-items:center;font-size:15px;flex-shrink:0">
                <i class="fa-solid fa-lock-open"></i>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:700;color:var(--text)">Opening Shift</div>
                <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">
                  Kasir wajib input kas awal sebelum mulai beroperasi
                </div>
              </div>
            </div>
            <label class="sw-wrap">
              <input type="checkbox" name="enable_opening_shift" id="sw-opening" value="1"
                {{ old('enable_opening_shift') ? 'checked' : '' }}>
              <span class="sw-track"></span>
              <span class="sw-thumb"></span>
            </label>
          </div>

          <div class="feat-row">
            <div style="display:flex;align-items:center;gap:12px;padding-right:20px">
              <div style="width:38px;height:38px;border-radius:10px;background:rgba(99,102,241,.12);color:#818cf8;display:grid;place-items:center;font-size:15px;flex-shrink:0">
                <i class="fa-solid fa-barcode"></i>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:700;color:var(--text)">Barcode Scanner</div>
                <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">
                  Aktifkan input barcode di halaman POS
                </div>
              </div>
            </div>
            <label class="sw-wrap">
              <input type="checkbox" name="enable_barcode_scanner" id="sw-barcode" value="1"
                {{ old('enable_barcode_scanner') ? 'checked' : '' }}>
              <span class="sw-track"></span>
              <span class="sw-thumb"></span>
            </label>
          </div>

        </div>
        <div style="height:10px"></div>
      </div>

      <div style="display:flex;justify-content:space-between;align-items:center">
        <button type="button" class="btn-back" onclick="goStep(2)">
          <i class="fa-solid fa-arrow-left"></i> Kembali
        </button>
        <button type="submit" class="btn-next">
          <i class="fa-solid fa-shop"></i> Buat Outlet
        </button>
      </div>
    </div>

  </form>
</div>

@push('scripts')
<script>
// ══════════════════════════════════════════════════════
// Searchable Select — lightweight combobox tanpa library
// ══════════════════════════════════════════════════════
class SearchableSelect {
  constructor({ wrap, input, hidden, dropdown, placeholder, onSelect }) {
    this.wrap       = document.getElementById(wrap);
    this.input      = document.getElementById(input);
    this.hidden     = document.getElementById(hidden);
    this.dropdown   = document.getElementById(dropdown);
    this.placeholder = placeholder;
    this.onSelect   = onSelect;
    this.options    = [];
    this.value      = '';
    this.label      = '';
    this._bind();
  }

  _bind() {
    this.input.addEventListener('click',   () => this._open());
    this.input.addEventListener('focus',   () => this._open());
    this.input.addEventListener('input',   () => this._filter(this.input.value));
    this.input.addEventListener('keydown', e  => { if (e.key === 'Escape') this._close(); });
    document.addEventListener('click', e => {
      if (!this.wrap.contains(e.target)) this._close();
    });
  }

  setOptions(opts) { // [{value, label}]
    this.options = opts;
    this.value = ''; this.label = '';
    this.hidden.value = '';
    this.input.value  = '';
    this.input.placeholder = this.placeholder;
    this.wrap.classList.remove('ss-disabled');
    this._render(opts);
  }

  setValue(val, lbl) {
    this.value = val; this.label = lbl;
    this.hidden.value = val;
    this.input.value  = lbl;
    this._render(this.options);
  }

  disable(ph) {
    this.wrap.classList.add('ss-disabled');
    this.value = ''; this.label = '';
    this.hidden.value = '';
    this.input.value  = '';
    this.input.placeholder = ph || this.placeholder;
    this.options = [];
    this.dropdown.innerHTML = '';
    this._close();
  }

  _open() {
    if (this.wrap.classList.contains('ss-disabled')) return;
    this.wrap.classList.add('open');
    this.input.classList.add('typing');
    this._filter(this.input.value);
  }

  _close() {
    this.wrap.classList.remove('open');
    this.input.classList.remove('typing');
    this.input.value = this.label; // restore selected label
  }

  _filter(q) {
    const filtered = q.trim()
      ? this.options.filter(o => o.label.toLowerCase().includes(q.toLowerCase()))
      : this.options;
    this._render(filtered);
  }

  _render(opts) {
    if (!opts.length) {
      this.dropdown.innerHTML = '<div class="ss-empty"><i class="fa-solid fa-magnifying-glass" style="margin-right:6px;opacity:.4"></i>Tidak ditemukan</div>';
      return;
    }
    const cur = this.value;
    this.dropdown.innerHTML = opts.map(o =>
      `<div class="ss-option${o.value == cur ? ' selected' : ''}"
        data-v="${o.value}" data-l="${o.label.replace(/"/g, '&quot;')}">${o.label}</div>`
    ).join('');
    this.dropdown.querySelectorAll('.ss-option').forEach(el => {
      el.addEventListener('mousedown', e => {
        e.preventDefault();
        this.setValue(el.dataset.v, el.dataset.l);
        this._close();
        if (this.onSelect) this.onSelect(el.dataset.v);
      });
    });
  }
}

// ── Inisialisasi tiga searchable select ─────────────────
const provSS = new SearchableSelect({
  wrap: 'prov-wrap', input: 'prov-input', hidden: 'province_id',
  dropdown: 'prov-dropdown', placeholder: '— Cari provinsi —',
  onSelect: val => loadRegencies(val),
});

const regSS = new SearchableSelect({
  wrap: 'reg-wrap', input: 'reg-input', hidden: 'regency_id',
  dropdown: 'reg-dropdown', placeholder: '— Pilih provinsi dulu —',
  onSelect: val => loadDistricts(val),
});

const distSS = new SearchableSelect({
  wrap: 'dist-wrap', input: 'dist-input', hidden: 'district_id',
  dropdown: 'dist-dropdown', placeholder: '— Pilih kab/kota dulu —',
  onSelect: () => {},
});

// ── Populate province options dari server ────────────────
const provinceData = @json($provinces->map(fn($p) => ['value' => $p->id, 'label' => $p->name]));
provSS.setOptions(provinceData);

// ── Cascading API ────────────────────────────────────────
const apiBase = '{{ route("wilayah.api.regencies") }}';
const apiDist = '{{ route("wilayah.api.districts") }}';

async function loadRegencies(provinceId) {
  regSS.disable('Memuat...');
  distSS.disable('— Pilih kab/kota dulu —');
  if (!provinceId) { regSS.disable('— Pilih provinsi dulu —'); return; }
  const res  = await fetch(apiBase + '?province_id=' + provinceId);
  const data = await res.json();
  regSS.setOptions(data.map(r => ({ value: r.id, label: r.name })));
  regSS.input.placeholder = '— Pilih kabupaten/kota —';
}

async function loadDistricts(regencyId) {
  distSS.disable('Memuat...');
  if (!regencyId) { distSS.disable('— Pilih kab/kota dulu —'); return; }
  const res  = await fetch(apiDist + '?regency_id=' + regencyId);
  const data = await res.json();
  if (data.length) {
    distSS.setOptions(data.map(d => ({ value: d.id, label: d.name })));
    distSS.input.placeholder = '— Pilih kecamatan —';
  } else {
    distSS.setOptions([]);
    distSS.input.placeholder = '(data kecamatan belum tersedia)';
  }
}

// ── Restore nilai lama saat validation error ─────────────
(async () => {
  const oldProvId   = '{{ old('province_id') }}';
  const oldRegId    = '{{ old('regency_id') }}';
  const oldDistId   = '{{ old('district_id') }}';

  if (!oldProvId) return;

  // Restore provinsi label
  const prov = provinceData.find(p => p.value == oldProvId);
  if (prov) provSS.setValue(prov.value, prov.label);

  // Load & restore kab/kota
  const resReg = await fetch(apiBase + '?province_id=' + oldProvId);
  const regData = await resReg.json();
  regSS.setOptions(regData.map(r => ({ value: r.id, label: r.name })));
  regSS.input.placeholder = '— Pilih kabupaten/kota —';
  if (oldRegId) {
    const reg = regData.find(r => r.id == oldRegId);
    if (reg) regSS.setValue(reg.id, reg.name);

    // Load & restore kecamatan
    const resDist = await fetch(apiDist + '?regency_id=' + oldRegId);
    const distData = await resDist.json();
    if (distData.length) {
      distSS.setOptions(distData.map(d => ({ value: d.id, label: d.name })));
      distSS.input.placeholder = '— Pilih kecamatan —';
      if (oldDistId) {
        const dist = distData.find(d => d.id == oldDistId);
        if (dist) distSS.setValue(dist.id, dist.name);
      }
    }
  }
})();

let currentStep  = 1;
let selectedType = null; // {id, prefix, opening}

// ── Step navigation ──────────────────────────────────────
function goStep(n) {
  if (n > currentStep) {
    if (!validateStep(currentStep)) return;
  }
  document.getElementById('step-' + currentStep).classList.remove('active');
  document.getElementById('step-' + n).classList.add('active');
  currentStep = n;
  updateWizard();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateWizard() {
  for (let i = 1; i <= 3; i++) {
    const dot   = document.getElementById('sd-' + i);
    const label = document.getElementById('sl-' + i);
    dot.classList.remove('active','done');
    label.classList.remove('active','done');
    if (i < currentStep)       { dot.classList.add('done');   label.classList.add('done'); }
    else if (i === currentStep){ dot.classList.add('active');  label.classList.add('active'); }
  }
  for (let i = 1; i <= 2; i++) {
    const line = document.getElementById('wl-' + i);
    line.style.background = i < currentStep ? 'var(--ac)' : 'var(--border)';
  }
}

// ── Validation per step ──────────────────────────────────
function validateStep(step) {
  if (step === 1) {
    let ok = true;
    if (!document.getElementById('outlet_type_id').value) {
      document.getElementById('err-type').style.display = 'flex';
      ok = false;
    } else {
      document.getElementById('err-type').style.display = 'none';
    }
    if (!document.getElementById('name').value.trim()) {
      document.getElementById('err-name').style.display = 'flex';
      document.getElementById('name').focus();
      ok = false;
    } else {
      document.getElementById('err-name').style.display = 'none';
    }
    // Bidang usaha wajib untuk Retail
    const errBidang = document.getElementById('err-bidang');
    if (errBidang) {
      const isRetail = selectedType && selectedType.prefix === 'retail';
      const buFilled = document.getElementById('bidang_usaha_input').value.trim();
      if (isRetail && !buFilled) {
        errBidang.style.display = 'flex';
        document.getElementById('bidang-usaha-box').scrollIntoView({ behavior: 'smooth', block: 'center' });
        ok = false;
      } else {
        errBidang.style.display = 'none';
      }
    }
    return ok;
  }
  return true;
}

// ── Type card picker ─────────────────────────────────────
function pickType(el) {
  document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');

  const id      = el.dataset.id;
  const prefix  = el.dataset.prefix;
  const opening = el.dataset.opening === '1';

  document.getElementById('outlet_type_id').value = id;
  document.getElementById('err-type').style.display = 'none';
  selectedType = { id, prefix, opening };

  // Bidang usaha hanya muncul untuk Retail
  const isRetail = prefix === 'retail';
  document.getElementById('bidang-usaha-box').style.display = isRetail ? 'block' : 'none';
  if (!isRetail) {
    document.getElementById('bidang_usaha_input').value = '';
    document.querySelectorAll('.bu-chip').forEach(c => c.classList.remove('active'));
  }

  // Mode transaksi
  const isFnb = prefix === 'fnb';
  document.getElementById('fnb-mode-section').style.display    = isFnb ? '' : 'none';
  document.getElementById('retail-mode-section').style.display = !isFnb ? '' : 'none';
  document.getElementById('no-type-section').style.display     = 'none';

  // Auto-set opening shift berdasarkan tipe
  document.getElementById('sw-opening').checked = opening;
}

// ── Bidang usaha chip picker ─────────────────────────────
function pickBidang(value, el) {
  document.querySelectorAll('.bu-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('bidang_usaha_input').value = value;
}

function syncChips(typed) {
  const chips = document.querySelectorAll('.bu-chip');
  chips.forEach(c => {
    c.classList.toggle('active', c.textContent.trim() === typed.trim());
  });
}

// ── Mode card highlight ──────────────────────────────────
function highlightMode(mode) {
  document.getElementById('mc-quick').classList.toggle('selected',   mode === 'quick');
  document.getElementById('mc-kitchen').classList.toggle('selected', mode === 'kitchen');
}

// ── Init ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const typeId = document.getElementById('outlet_type_id').value;
  if (typeId) {
    const el = document.querySelector(`.type-card[data-id="${typeId}"]`);
    if (el) pickType(el);
  }

  const checkedMode = document.querySelector('input[name="order_mode"]:checked');
  if (checkedMode) highlightMode(checkedMode.value);

  document.querySelectorAll('input[name="order_mode"]').forEach(r => {
    r.addEventListener('change', () => highlightMode(r.value));
  });

  // Restore bidang_usaha chip highlight on validation error
  const buVal = document.getElementById('bidang_usaha_input').value;
  if (buVal) syncChips(buVal);

  @if($errors->any())
    @if($errors->has('outlet_type_id') || $errors->has('name'))
      // already on step 1
    @elseif($errors->has('order_mode'))
      goStep(2);
    @else
      goStep(3);
    @endif
  @endif
});
</script>
@endpush

</x-app-layout>
