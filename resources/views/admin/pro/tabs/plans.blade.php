{{-- Tab: Paket --}}
@php
  $featuresByType = collect($allFeatures)->groupBy('outlet_type');
@endphp
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-crown a-text" style="margin-right:8px"></i>Daftar Paket</div>
    <button onclick="openModal('modal-add-plan')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Tambah Paket
    </button>
  </div>

  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Nama Paket</th>
          <th>Harga</th>
          <th>Maks. Jumlah Outlet</th>
          <th>Maks. Kasir</th>
          <th>Jumlah Fitur</th>
          <th style="text-align:center;width:100px">Status</th>
          <th style="text-align:center;width:110px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($plans as $plan)
        <tr>
          <td class="td-main">
            {{ $plan->name }}
            @if($plan->is_default)
            <span class="badge badge-gray" style="margin-left:6px">Bawaan</span>
            @endif
            @if($plan->is_self_activatable)
            <span class="badge badge-green" style="margin-left:6px" title="Owner bisa aktifkan sendiri tanpa kode">Self-Activate</span>
            @endif
            <div style="font-size:11.5px;color:var(--muted);margin-top:2px;max-width:320px">{{ $plan->description }}</div>
            @if($plan->allowedOutletTypes->isNotEmpty())
            <div style="font-size:10.5px;color:var(--muted);margin-top:2px">Jenis outlet: {{ $plan->allowedOutletTypes->pluck('name')->join(', ') }}</div>
            @endif
          </td>
          <td style="font-size:13px;color:var(--sub)">
            {{ $plan->price > 0 ? 'Rp' . number_format($plan->price, 0, ',', '.') . '/bulan' : 'Gratis' }}
          </td>
          <td style="font-size:13px;color:var(--sub)">
            {{ $plan->max_outlet_types === null ? 'Tanpa batas' : $plan->max_outlet_types . ' outlet' }}
          </td>
          <td style="font-size:13px;color:var(--sub)">
            {{ $plan->max_kasir === null ? 'Tanpa batas' : $plan->max_kasir . ' akun' }}
          </td>
          <td style="font-size:13px;color:var(--sub)">{{ $plan->features->count() }} fitur</td>
          <td style="text-align:center">
            @if($plan->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              <button title="Edit" onclick='openEditPlan({{ $plan->id }}, {{ json_encode([
                  "name" => $plan->name, "description" => $plan->description, "price" => $plan->price,
                  "max_outlet_types" => $plan->max_outlet_types, "max_kasir" => $plan->max_kasir,
                  "is_active" => $plan->is_active, "features" => $plan->features->pluck("slug"),
                  "is_self_activatable" => $plan->is_self_activatable,
                  "allowed_outlet_types" => $plan->allowedOutletTypes->pluck("id"),
                ]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px">
                <i class="fa-solid fa-pen"></i>
              </button>
              @if(!($plan->is_default && $plan->is_active))
              <form method="POST" action="{{ route('admin.pro.plans.toggle-active', $plan) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $plan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $plan->is_active ? '#fbbf24' : '#34d399' }};transition:all .15s"
                  onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                  <i class="fa-solid {{ $plan->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              @endif
              @if(!$plan->is_default)
              <form method="POST" action="{{ route('admin.pro.plans.destroy', $plan) }}" style="margin:0"
                    onsubmit="return confirm('Hapus paket &quot;{{ $plan->name }}&quot;? Tindakan ini tidak bisa dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" title="Hapus"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Modal Tambah Paket --}}
<div class="modal-backdrop" id="modal-add-plan" onclick="if(event.target===this)closeModal('modal-add-plan')">
  <div class="modal-box" style="max-width:560px">
    <form method="POST" action="{{ route('admin.pro.plans.store') }}">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Paket</h3>
        <button type="button" onclick="closeModal('modal-add-plan')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        <div>
          <label class="f-label">Nama Paket <span style="color:var(--ac)">*</span></label>
          <input name="name" class="f-input" required maxlength="60" placeholder="cth: Max">
        </div>
        <div>
          <label class="f-label">Deskripsi Singkat</label>
          <textarea name="desc" class="f-input" rows="2" maxlength="200" placeholder="Ditampilkan ke owner saat memilih paket"></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Harga per Bulan (Rp)</label>
            <input name="price" type="number" class="f-input" min="0" step="1000" placeholder="0 = gratis">
          </div>
          <div>
            <label class="f-label">Maks. Jumlah Outlet</label>
            <input name="max_outlet_types" type="number" class="f-input" min="1" placeholder="tanpa batas">
          </div>
          <div>
            <label class="f-label">Maks. Akun Kasir</label>
            <input name="max_kasir" type="number" class="f-input" min="1" placeholder="tanpa batas">
          </div>
        </div>
        <p style="font-size:10.5px;color:var(--muted);margin-top:-6px">
          "Maks. Jumlah Outlet" = total outlet (semua jenis) yang boleh dimiliki owner.
          "Maks. Akun Kasir" = jumlah akun login kasir yang boleh dibuat owner. Kosongkan salah satu = tanpa batas.
        </p>

        <div>
          <label class="f-label">Fitur yang Termasuk</label>
          <div style="display:flex;flex-direction:column;gap:12px;margin-top:6px">
            @foreach($outletTypes as $typeKey => $typeLabel)
            @if(($featuresByType[$typeKey] ?? collect())->isNotEmpty())
            <div style="border:1px solid var(--border);border-radius:10px;padding:10px 12px">
              <div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:7px">{{ $typeLabel }}</div>
              <div style="display:flex;flex-direction:column;gap:7px">
                @foreach($featuresByType[$typeKey] as $f)
                <label style="display:flex;align-items:flex-start;gap:9px;cursor:pointer;font-size:12.5px">
                  <input type="checkbox" name="features[]" value="{{ $f['slug'] }}" style="margin-top:2px">
                  <span>
                    <span style="color:var(--text);font-weight:600">{{ $f['name'] }}</span><br>
                    <span style="color:var(--muted);font-size:11.5px">{{ $f['description'] }}</span>
                  </span>
                </label>
                @endforeach
              </div>
            </div>
            @endif
            @endforeach
          </div>
        </div>

        <div>
          <label class="f-label">Jenis Outlet yang Boleh Dibuat</label>
          <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;border:1px solid var(--border);border-radius:10px;padding:10px 12px">
            @foreach($allOutletTypes as $ot)
            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:12.5px">
              <input type="checkbox" name="allowed_outlet_types[]" value="{{ $ot->id }}">
              {{ $ot->name }}
            </label>
            @endforeach
          </div>
          <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Kosongkan semua = tidak dibatasi, owner boleh buat semua jenis outlet.</p>
        </div>

        <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:13px;color:var(--sub)">
          <input type="checkbox" name="is_active" value="1" checked> Paket aktif (bisa dipakai untuk kode baru)
        </label>
        <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:13px;color:var(--sub)">
          <input type="checkbox" name="is_self_activatable" value="1">
          Owner bisa aktifkan sendiri di halaman Langganan (tanpa kode aktivasi)
        </label>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-add-plan')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Edit Paket --}}
<div class="modal-backdrop" id="modal-edit-plan" onclick="if(event.target===this)closeModal('modal-edit-plan')">
  <div class="modal-box" style="max-width:560px">
    <form id="form-edit-plan" method="POST" action="">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Paket</h3>
        <button type="button" onclick="closeModal('modal-edit-plan')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        <div>
          <label class="f-label">Nama Paket <span style="color:var(--ac)">*</span></label>
          <input id="ed-plan-name" name="name" class="f-input" required maxlength="60">
        </div>
        <div>
          <label class="f-label">Deskripsi Singkat</label>
          <textarea id="ed-plan-desc" name="desc" class="f-input" rows="2" maxlength="200"></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Harga per Bulan (Rp)</label>
            <input id="ed-plan-price" name="price" type="number" class="f-input" min="0" step="1000">
          </div>
          <div>
            <label class="f-label">Maks. Jumlah Outlet</label>
            <input id="ed-plan-max-outlet" name="max_outlet_types" type="number" class="f-input" min="1" placeholder="tanpa batas">
          </div>
          <div>
            <label class="f-label">Maks. Akun Kasir</label>
            <input id="ed-plan-max-kasir" name="max_kasir" type="number" class="f-input" min="1" placeholder="tanpa batas">
          </div>
        </div>
        <p style="font-size:10.5px;color:var(--muted);margin-top:-6px">
          "Maks. Jumlah Outlet" = total outlet (semua jenis) yang boleh dimiliki owner.
          "Maks. Akun Kasir" = jumlah akun login kasir yang boleh dibuat owner. Kosongkan salah satu = tanpa batas.
        </p>

        <div>
          <label class="f-label">Fitur yang Termasuk</label>
          <div id="ed-plan-features" style="display:flex;flex-direction:column;gap:12px;margin-top:6px">
            @foreach($outletTypes as $typeKey => $typeLabel)
            @if(($featuresByType[$typeKey] ?? collect())->isNotEmpty())
            <div style="border:1px solid var(--border);border-radius:10px;padding:10px 12px">
              <div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:7px">{{ $typeLabel }}</div>
              <div style="display:flex;flex-direction:column;gap:7px">
                @foreach($featuresByType[$typeKey] as $f)
                <label style="display:flex;align-items:flex-start;gap:9px;cursor:pointer;font-size:12.5px">
                  <input type="checkbox" class="ed-plan-feature-cb" name="features[]" value="{{ $f['slug'] }}" style="margin-top:2px">
                  <span>
                    <span style="color:var(--text);font-weight:600">{{ $f['name'] }}</span><br>
                    <span style="color:var(--muted);font-size:11.5px">{{ $f['description'] }}</span>
                  </span>
                </label>
                @endforeach
              </div>
            </div>
            @endif
            @endforeach
          </div>
        </div>

        <div>
          <label class="f-label">Jenis Outlet yang Boleh Dibuat</label>
          <div id="ed-plan-outlet-types" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;border:1px solid var(--border);border-radius:10px;padding:10px 12px">
            @foreach($allOutletTypes as $ot)
            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:12.5px">
              <input type="checkbox" class="ed-plan-outlet-type-cb" name="allowed_outlet_types[]" value="{{ $ot->id }}">
              {{ $ot->name }}
            </label>
            @endforeach
          </div>
          <p style="font-size:10.5px;color:var(--muted);margin-top:3px">Kosongkan semua = tidak dibatasi, owner boleh buat semua jenis outlet.</p>
        </div>

        <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:13px;color:var(--sub)">
          <input id="ed-plan-active" type="checkbox" name="is_active" value="1"> Paket aktif (bisa dipakai untuk kode baru)
        </label>
        <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:13px;color:var(--sub)">
          <input id="ed-plan-self-activatable" type="checkbox" name="is_self_activatable" value="1">
          Owner bisa aktifkan sendiri di halaman Langganan (tanpa kode aktivasi)
        </label>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit-plan')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditPlan(id, plan){
  document.getElementById('form-edit-plan').action = '/admin/pro/paket/' + id;
  document.getElementById('ed-plan-name').value  = plan.name;
  document.getElementById('ed-plan-desc').value  = plan.description;
  document.getElementById('ed-plan-price').value = plan.price;
  document.getElementById('ed-plan-max-outlet').value = plan.max_outlet_types ?? '';
  document.getElementById('ed-plan-max-kasir').value = plan.max_kasir ?? '';
  document.getElementById('ed-plan-active').checked = plan.is_active;
  document.getElementById('ed-plan-self-activatable').checked = plan.is_self_activatable;

  document.querySelectorAll('.ed-plan-feature-cb').forEach(cb => {
    cb.checked = plan.features.includes(cb.value);
  });
  document.querySelectorAll('.ed-plan-outlet-type-cb').forEach(cb => {
    cb.checked = plan.allowed_outlet_types.includes(parseInt(cb.value));
  });

  openModal('modal-edit-plan');
}
</script>
