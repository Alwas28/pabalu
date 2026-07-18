<x-app-layout>
  <x-slot name="header">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <a href="javascript:history.back()"
        style="width:34px;height:34px;border-radius:10px;background:var(--surface2);color:var(--muted);
               display:grid;place-items:center;text-decoration:none;transition:color .15s;flex-shrink:0"
        onmouseenter="this.style.color='var(--text)'" onmouseleave="this.style.color='var(--muted)'">
        <i class="fa-solid fa-arrow-left" style="font-size:14px"></i>
      </a>
      <div>
        <h2 class="font-display" style="font-size:22px;color:var(--text);margin:0">Profil Owner</h2>
        <p style="font-size:13px;color:var(--muted);margin:3px 0 0">Detail akun dan daftar outlet milik owner</p>
      </div>
    </div>
  </x-slot>

  <div class="py-6">
  <div style="max-width:960px;margin:0 auto;padding:0 20px">

  {{-- ── Kartu Profil Owner ── --}}
  <div class="card animate-fadeUp" style="margin-bottom:24px">
    <div style="padding:24px">
      <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap">

        {{-- Avatar inisial --}}
        @php
          $words    = explode(' ', trim($user->name));
          $initials = strtoupper(substr($words[0],0,1) . (isset($words[1]) ? substr($words[1],0,1) : ''));
        @endphp
        <div style="width:72px;height:72px;border-radius:50%;background:var(--ac-lt);color:var(--ac);
                    display:grid;place-items:center;font-size:26px;font-weight:800;
                    font-family:'Clash Display',sans-serif;flex-shrink:0">
          {{ $initials }}
        </div>

        {{-- Info utama --}}
        <div style="flex:1;min-width:200px">
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
            <h3 style="font-size:20px;font-weight:800;color:var(--text);margin:0">{{ $user->name }}</h3>
            <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;
                         background:var(--ac-lt);color:var(--ac)">Owner</span>
            @if(!$user->is_active)
            <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;
                         background:rgba(239,68,68,.12);color:#f87171">Nonaktif</span>
            @endif
          </div>

          <div style="display:flex;flex-direction:column;gap:7px">
            @if($user->email)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
              <i class="fa-solid fa-envelope" style="width:14px;text-align:center;color:var(--ac);font-size:12px"></i>
              <a href="mailto:{{ $user->email }}" style="color:var(--text);text-decoration:none">{{ $user->email }}</a>
            </div>
            @endif
            @if($user->phone)
            @php
              $waNum = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $user->phone));
            @endphp
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
              <i class="fa-brands fa-whatsapp" style="width:14px;text-align:center;color:#25d366;font-size:13px"></i>
              <a href="https://wa.me/{{ $waNum }}" target="_blank" rel="noopener"
                style="color:var(--text);text-decoration:none">{{ $user->phone }}</a>
            </div>
            @endif
            @if($user->business_name)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
              <i class="fa-solid fa-briefcase" style="width:14px;text-align:center;color:var(--ac);font-size:12px"></i>
              <span style="color:var(--text)">{{ $user->business_name }}</span>
            </div>
            @endif
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
              <i class="fa-regular fa-calendar" style="width:14px;text-align:center;font-size:12px"></i>
              Bergabung {{ $user->created_at->translatedFormat('d F Y') }}
            </div>
          </div>
        </div>

        {{-- Tombol aksi --}}
        <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0">
          <a href="{{ route('chat.index') }}?owner={{ $user->id }}" id="btn-chat-owner"
            style="padding:9px 18px;border-radius:10px;background:var(--ac);color:#fff;
                   font-size:13px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:7px;
                   white-space:nowrap">
            <i class="fa-solid fa-comments"></i> Buka Chat
          </a>
          <a href="{{ route('users.edit', $user) }}"
            style="padding:9px 18px;border-radius:10px;background:var(--surface2);color:var(--text);
                   font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:7px;
                   white-space:nowrap">
            <i class="fa-solid fa-pen-to-square"></i> Edit Akun
          </a>
        </div>
      </div>

      {{-- Statistik outlet --}}
      @php
        $totalOutlets  = $user->outlets->count();
        $activeOutlets = $user->outlets->where('is_active', true)->count();
      @endphp
      <div style="display:flex;gap:16px;margin-top:20px;padding-top:18px;border-top:1px solid var(--border);flex-wrap:wrap">
        <div style="flex:1;min-width:100px;text-align:center;padding:12px;border-radius:12px;background:var(--surface2)">
          <div style="font-size:24px;font-weight:800;color:var(--text);font-family:'Clash Display',sans-serif">{{ $totalOutlets }}</div>
          <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Total Outlet</div>
        </div>
        <div style="flex:1;min-width:100px;text-align:center;padding:12px;border-radius:12px;background:rgba(16,185,129,.08)">
          <div style="font-size:24px;font-weight:800;color:#34d399;font-family:'Clash Display',sans-serif">{{ $activeOutlets }}</div>
          <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Outlet Aktif</div>
        </div>
        @if($totalOutlets - $activeOutlets > 0)
        <div style="flex:1;min-width:100px;text-align:center;padding:12px;border-radius:12px;background:rgba(239,68,68,.08)">
          <div style="font-size:24px;font-weight:800;color:#f87171;font-family:'Clash Display',sans-serif">{{ $totalOutlets - $activeOutlets }}</div>
          <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Outlet Nonaktif</div>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- ── Daftar Outlet ── --}}
  <h3 style="font-size:15px;font-weight:700;color:var(--text);margin:0 0 14px;display:flex;align-items:center;gap:8px">
    <i class="fa-solid fa-shop a-text"></i> Daftar Outlet ({{ $totalOutlets }})
  </h3>

  @if($user->outlets->isEmpty())
  <div class="card" style="text-align:center;padding:48px 20px">
    <i class="fa-solid fa-store-slash" style="font-size:36px;color:var(--muted);margin-bottom:12px;display:block;opacity:.4"></i>
    <div style="font-size:14px;font-weight:600;color:var(--text)">Belum ada outlet</div>
    <div style="font-size:13px;color:var(--muted);margin-top:4px">Owner ini belum memiliki outlet.</div>
  </div>
  @else

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
    @foreach($user->outlets->sortByDesc('is_active') as $outlet)
    @php
      $rp        = $outlet->outletType?->route_prefix ?? 'fnb';
      $typeColor = match($rp) {
        'salon'   => ['bg'=>'rgba(139,92,246,.12)', 'fg'=>'#a78bfa'],
        'laundry' => ['bg'=>'rgba(6,182,212,.12)',  'fg'=>'#22d3ee'],
        'retail'  => ['bg'=>'rgba(245,158,11,.12)', 'fg'=>'#f59e0b'],
        default   => ['bg'=>'rgba(232,25,44,.12)',   'fg'=>'#f87171'],
      };
      $typeIcon = match($rp) {
        'salon'   => 'fa-scissors',
        'laundry' => 'fa-shirt',
        'retail'  => 'fa-cart-shopping',
        default   => 'fa-utensils',
      };
      $pmList = $outlet->activePaymentMethods();
    @endphp
    <div class="card animate-fadeUp" style="padding:0;overflow:hidden">

      {{-- Header kartu outlet --}}
      <div style="padding:16px 18px;border-bottom:1px solid var(--border);
                   display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap">
            <span style="font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:99px;
                         background:{{ $typeColor['bg'] }};color:{{ $typeColor['fg'] }};
                         display:flex;align-items:center;gap:4px;flex-shrink:0">
              <i class="fa-solid {{ $typeIcon }}" style="font-size:9px"></i>
              {{ $outlet->outletType?->name ?? ucfirst($rp) }}
            </span>
            <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;flex-shrink:0;
                         background:{{ $outlet->is_active ? 'rgba(16,185,129,.12)' : 'var(--surface2)' }};
                         color:{{ $outlet->is_active ? '#34d399' : 'var(--muted)' }}">
              {{ $outlet->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>
          <a href="{{ route($rp . '.show', $outlet) }}"
             style="font-size:15px;font-weight:700;color:var(--text);text-decoration:none;
                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;
                    transition:color .15s"
             onmouseenter="this.style.color='var(--ac)'" onmouseleave="this.style.color='var(--text)'">
            {{ $outlet->name }}
          </a>
          @if($outlet->code)
          <div style="font-size:11px;color:var(--muted);margin-top:2px;font-family:monospace">
            #{{ $outlet->code }}
          </div>
          @endif
        </div>
        <a href="{{ route($rp . '.settings.edit', $outlet) }}"
          style="width:34px;height:34px;border-radius:10px;background:var(--ac-lt);color:var(--ac);
                 display:grid;place-items:center;text-decoration:none;flex-shrink:0;transition:opacity .15s"
          title="Buka Pengaturan Outlet">
          <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:13px"></i>
        </a>
      </div>

      {{-- Body kartu outlet --}}
      <div style="padding:14px 18px;display:flex;flex-direction:column;gap:9px">

        @if($outlet->bidang_usaha)
        <div style="display:flex;align-items:flex-start;gap:8px;font-size:12.5px">
          <i class="fa-solid fa-briefcase" style="color:var(--ac);font-size:11px;margin-top:2px;flex-shrink:0"></i>
          <span style="color:var(--text)">{{ $outlet->bidang_usaha }}</span>
        </div>
        @endif

        @if($outlet->address)
        <div style="display:flex;align-items:flex-start;gap:8px;font-size:12.5px">
          <i class="fa-solid fa-location-dot" style="color:var(--ac);font-size:11px;margin-top:2px;flex-shrink:0"></i>
          <span style="color:var(--text);line-height:1.5">
            {{ $outlet->address }}
            @if($outlet->regency)
            , {{ $outlet->regency->name }}
            @endif
            @if($outlet->province)
            , {{ $outlet->province->name }}
            @endif
          </span>
        </div>
        @endif

        @if($outlet->phone)
        <div style="display:flex;align-items:center;gap:8px;font-size:12.5px">
          <i class="fa-solid fa-phone" style="color:var(--ac);font-size:11px;flex-shrink:0"></i>
          <span style="color:var(--text)">{{ $outlet->phone }}</span>
        </div>
        @endif

        {{-- Metode pembayaran aktif --}}
        @if(!empty($pmList))
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:2px">
          <span style="font-size:11px;color:var(--muted);flex-shrink:0">Pembayaran:</span>
          <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;
                       background:rgba(16,185,129,.1);color:#34d399;display:flex;align-items:center;gap:4px">
            <i class="fa-solid fa-money-bill-wave" style="font-size:9px"></i>Tunai
          </span>
          @foreach($pmList as $code => [$pmLabel, $pmIcon])
          <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;
                       background:var(--ac-lt);color:var(--ac);display:flex;align-items:center;gap:4px">
            <i class="fa-solid {{ $pmIcon }}" style="font-size:9px"></i>{{ $pmLabel }}
          </span>
          @endforeach
        </div>
        @endif

        <div style="font-size:11px;color:var(--muted);margin-top:2px;padding-top:8px;border-top:1px solid var(--border)">
          <i class="fa-regular fa-calendar" style="margin-right:4px"></i>
          Dibuat {{ $outlet->created_at->translatedFormat('d F Y') }}
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  </div>
  </div>

  @push('scripts')
  <script>
  // Jika datang dari chat page dengan ?owner param, langsung buka chat owner ini
  (function(){
    var params = new URLSearchParams(window.location.search);
    if (params.has('owner')) {
      // Navigasi ke chat page dan auto-select owner (state dikirim via sessionStorage)
      sessionStorage.setItem('chat_open_owner', params.get('owner'));
    }
  })();
  </script>
  @endpush
</x-app-layout>
