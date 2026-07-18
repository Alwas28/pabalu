<x-app-layout>
  <x-slot name="header">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div>
        <h2 class="font-display" style="font-size:22px;color:var(--text);margin:0">
          <i class="fa-solid fa-users a-text" style="margin-right:10px"></i>Daftar Owner
        </h2>
        <p style="font-size:13px;color:var(--muted);margin:4px 0 0">Semua akun owner yang terdaftar di sistem</p>
      </div>
      <span style="font-size:13px;font-weight:700;padding:6px 14px;border-radius:99px;
                   background:var(--ac-lt);color:var(--ac)">
        {{ $owners->total() }} owner
      </span>
    </div>
  </x-slot>

  <div class="py-6">
  <div style="max-width:1100px;margin:0 auto;padding:0 20px">

    {{-- Search --}}
    <form method="GET" action="{{ route('owners.index') }}" style="margin-bottom:18px">
      <div style="display:flex;gap:10px;max-width:400px">
        <div style="flex:1;position:relative">
          <i class="fa-solid fa-magnifying-glass"
             style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                    color:var(--muted);font-size:12px;pointer-events:none"></i>
          <input type="text" name="q" value="{{ request('q') }}"
            placeholder="Cari nama, email, atau bisnis…"
            style="width:100%;padding:9px 12px 9px 34px;border-radius:10px;
                   border:1.5px solid var(--border);background:var(--surface2);
                   color:var(--text);font-size:13px;outline:none;font-family:inherit"
            onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
        </div>
        <button type="submit"
          style="padding:9px 18px;border-radius:10px;background:var(--ac);color:#fff;
                 border:none;cursor:pointer;font-size:13px;font-weight:700;white-space:nowrap">
          Cari
        </button>
        @if(request('q'))
        <a href="{{ route('owners.index') }}"
           style="padding:9px 14px;border-radius:10px;background:var(--surface2);color:var(--muted);
                  text-decoration:none;font-size:13px;font-weight:600;display:flex;align-items:center;gap:5px">
          <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
      </div>
    </form>

    @if($owners->isEmpty())
    <div class="card" style="text-align:center;padding:60px 20px">
      <i class="fa-solid fa-users-slash" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block;opacity:.4"></i>
      <div style="font-size:15px;font-weight:600;color:var(--text)">Tidak ada owner ditemukan</div>
      <div style="font-size:13px;color:var(--muted);margin-top:6px">
        @if(request('q'))Coba kata kunci lain atau <a href="{{ route('owners.index') }}" style="color:var(--ac)">reset pencarian</a>.
        @else Belum ada akun owner terdaftar.
        @endif
      </div>
    </div>
    @else

    <div class="card" style="overflow:hidden">
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;min-width:640px">
          <thead>
            <tr style="border-bottom:1px solid var(--border)">
              <th style="padding:12px 16px;text-align:left;font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Owner</th>
              <th style="padding:12px 16px;text-align:left;font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Kontak</th>
              <th style="padding:12px 16px;text-align:left;font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Bisnis</th>
              <th style="padding:12px 16px;text-align:center;font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Outlet</th>
              <th style="padding:12px 16px;text-align:left;font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Bergabung</th>
              <th style="padding:12px 16px;text-align:center;font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($owners as $owner)
            @php
              $words    = explode(' ', trim($owner->name));
              $initials = strtoupper(substr($words[0],0,1) . (isset($words[1]) ? substr($words[1],0,1) : ''));
            @endphp
            <tr onclick="window.location='{{ route('owners.show', $owner) }}'"
                style="border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s"
                onmouseenter="this.style.background='var(--surface2)'" onmouseleave="this.style.background='transparent'">

              {{-- Kolom owner --}}
              <td style="padding:14px 16px">
                <div style="display:flex;align-items:center;gap:12px">
                  <div style="width:38px;height:38px;border-radius:50%;background:var(--ac-lt);color:var(--ac);
                              display:grid;place-items:center;font-size:14px;font-weight:800;
                              font-family:'Clash Display',sans-serif;flex-shrink:0">
                    {{ $initials }}
                  </div>
                  <div>
                    <div style="font-size:14px;font-weight:700;color:var(--text)">{{ $owner->name }}</div>
                    <div style="font-size:11.5px;color:var(--muted)">{{ $owner->email }}</div>
                  </div>
                </div>
              </td>

              {{-- Kolom kontak --}}
              <td style="padding:14px 16px">
                @if($owner->phone)
                @php $waNum = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $owner->phone)); @endphp
                <a href="https://wa.me/{{ $waNum }}" target="_blank" rel="noopener"
                   onclick="event.stopPropagation()"
                   style="display:flex;align-items:center;gap:5px;font-size:12.5px;color:var(--text);text-decoration:none">
                  <i class="fa-brands fa-whatsapp" style="color:#25d366;font-size:13px"></i>{{ $owner->phone }}
                </a>
                @else
                <span style="font-size:12px;color:var(--muted)">—</span>
                @endif
              </td>

              {{-- Kolom bisnis --}}
              <td style="padding:14px 16px">
                @if($owner->business_name)
                <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $owner->business_name }}</div>
                @else
                <span style="font-size:12px;color:var(--muted)">—</span>
                @endif
              </td>

              {{-- Kolom outlet --}}
              <td style="padding:14px 16px;text-align:center">
                <span style="font-size:13px;font-weight:700;color:{{ $owner->outlets_count > 0 ? 'var(--ac)' : 'var(--muted)' }}">
                  {{ $owner->outlets_count }}
                </span>
              </td>

              {{-- Kolom bergabung --}}
              <td style="padding:14px 16px">
                <div style="font-size:12.5px;color:var(--text)">{{ $owner->created_at->translatedFormat('d M Y') }}</div>
                <div style="font-size:11px;color:var(--muted)">{{ $owner->created_at->diffForHumans() }}</div>
              </td>

              {{-- Kolom status --}}
              <td style="padding:14px 16px;text-align:center">
                <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;
                             background:{{ $owner->is_active ? 'rgba(16,185,129,.12)' : 'rgba(239,68,68,.12)' }};
                             color:{{ $owner->is_active ? '#34d399' : '#f87171' }}">
                  {{ $owner->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    @if($owners->hasPages())
    <div style="margin-top:20px">{{ $owners->links() }}</div>
    @endif

    @endif
  </div>
  </div>
</x-app-layout>
