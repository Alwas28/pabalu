<x-app-layout>
  <x-slot name="header">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div>
        <h2 class="font-display" style="font-size:22px;color:var(--text);margin:0">
          <i class="fa-solid fa-headset a-text" style="margin-right:10px"></i>Laporan Sistem
        </h2>
        <p style="font-size:13px;color:var(--muted);margin:4px 0 0">Laporan dari owner outlet kepada administrator</p>
      </div>
      @if($unreadCount)
      <span style="font-size:13px;font-weight:700;padding:6px 14px;border-radius:99px;
                   background:rgba(245,158,11,.14);color:#f59e0b">
        <i class="fa-solid fa-bell" style="margin-right:6px"></i>{{ $unreadCount }} belum dibaca
      </span>
      @endif
    </div>
  </x-slot>

  <div class="py-6">
    <div style="max-width:860px;margin:0 auto;padding:0 20px">

      @if($reports->isEmpty())
      <div class="card" style="text-align:center;padding:60px 20px">
        <i class="fa-solid fa-inbox" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
        <div style="font-size:15px;font-weight:600;color:var(--text)">Belum ada laporan</div>
        <div style="font-size:13px;color:var(--muted);margin-top:6px">Laporan dari owner outlet akan muncul di sini.</div>
      </div>
      @else

      @foreach($reports as $report)
      @php $isUnread = $report->status === 'unread'; @endphp
      <div class="card animate-fadeUp" style="margin-bottom:14px;{{ $isUnread ? 'border-left:3px solid var(--ac)' : '' }}">
        <div style="padding:18px 20px">

          {{-- Header laporan --}}
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px">
            <div style="flex:1">
              <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px">
                <span style="font-size:14px;font-weight:700;color:var(--text)">{{ $report->subject }}</span>
                @if($isUnread)
                <span style="font-size:10px;font-weight:800;padding:2px 8px;border-radius:99px;
                             background:rgba(245,158,11,.14);color:#f59e0b">BARU</span>
                @else
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;
                             background:rgba(16,185,129,.12);color:#34d399">Dibaca</span>
                @endif
              </div>
              <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                <a href="{{ route('owners.show', $report->user) }}"
                   style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);text-decoration:none"
                   title="Lihat Profil Owner">
                  <i class="fa-solid fa-user" style="font-size:10px"></i>
                  <strong style="color:var(--text)">{{ $report->user->name }}</strong>
                  <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px;opacity:.5"></i>
                </a>
                @if($report->outlet)
                <div style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px">
                  <i class="fa-solid fa-shop" style="font-size:10px"></i>{{ $report->outlet->name }}
                </div>
                @endif
                <div style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px">
                  <i class="fa-regular fa-clock" style="font-size:10px"></i>{{ $report->created_at->diffForHumans() }}
                </div>
              </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:8px;flex-shrink:0">
              @if($isUnread)
              <form method="POST" action="{{ route('system-reports.read', $report) }}">
                @csrf @method('PATCH')
                <button type="submit" title="Tandai Sudah Dibaca"
                  style="padding:7px 12px;border-radius:8px;background:rgba(16,185,129,.12);color:#34d399;
                         border:none;cursor:pointer;font-size:12px;font-weight:600;display:flex;align-items:center;gap:5px">
                  <i class="fa-solid fa-check"></i> Tandai Dibaca
                </button>
              </form>
              @endif
              <button onclick="toggleReply({{ $report->id }})"
                style="padding:7px 12px;border-radius:8px;background:var(--ac-lt);color:var(--ac);
                       border:none;cursor:pointer;font-size:12px;font-weight:600;display:flex;align-items:center;gap:5px">
                <i class="fa-solid fa-reply"></i> {{ $report->admin_reply ? 'Edit Balasan' : 'Balas' }}
              </button>
            </div>
          </div>

          {{-- Pesan --}}
          <div style="background:var(--surface2);border-radius:10px;padding:12px 16px;font-size:13px;
                      color:var(--text);line-height:1.7;margin-bottom:{{ $report->admin_reply ? '12px' : '0' }}">
            {{ $report->message }}
          </div>

          {{-- Balasan admin (jika ada) --}}
          @if($report->admin_reply)
          <div style="background:var(--ac-lt);border-left:3px solid var(--ac);border-radius:0 10px 10px 0;
                      padding:10px 14px;font-size:13px;color:var(--text);line-height:1.6">
            <div style="font-size:11px;font-weight:700;color:var(--ac);margin-bottom:5px">
              <i class="fa-solid fa-reply" style="margin-right:4px"></i>Balasan Administrator
              @if($report->read_at)
              · <span style="font-weight:400;color:var(--muted)">{{ $report->read_at->format('d M Y, H:i') }}</span>
              @endif
            </div>
            {{ $report->admin_reply }}
          </div>
          @endif

          {{-- Form balas (toggle) --}}
          <div id="reply-form-{{ $report->id }}" style="display:none;margin-top:14px">
            <form method="POST" action="{{ route('system-reports.reply', $report) }}">
              @csrf
              <textarea name="admin_reply" rows="4" maxlength="2000" required
                placeholder="Tulis balasan untuk owner..."
                style="width:100%;padding:10px 14px;border-radius:10px;border:1.5px solid var(--border);
                       background:var(--surface2);color:var(--text);font-size:13px;outline:none;
                       font-family:inherit;resize:vertical;transition:border-color .15s;margin-bottom:10px"
                onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'"
              >{{ $report->admin_reply }}</textarea>
              <div style="display:flex;gap:8px;justify-content:flex-end">
                <button type="button" onclick="toggleReply({{ $report->id }})"
                  style="padding:8px 16px;border-radius:8px;background:var(--surface2);color:var(--muted);
                         border:none;cursor:pointer;font-size:13px;font-weight:600">
                  Batal
                </button>
                <button type="submit"
                  style="padding:8px 18px;border-radius:8px;background:var(--ac);color:#fff;
                         border:none;cursor:pointer;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px">
                  <i class="fa-solid fa-paper-plane"></i> Kirim Balasan
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
      @endforeach

      {{-- Pagination --}}
      @if($reports->hasPages())
      <div style="margin-top:20px">{{ $reports->links() }}</div>
      @endif

      @endif
    </div>
  </div>

  @push('scripts')
  <script>
  function toggleReply(id) {
    var el = document.getElementById('reply-form-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    if (el.style.display === 'block') el.querySelector('textarea').focus();
  }
  </script>
  @endpush
</x-app-layout>
