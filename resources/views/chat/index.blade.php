<x-app-layout>
  <x-slot name="header">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:38px;height:38px;border-radius:50%;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center">
        <i class="fa-solid fa-comments" style="font-size:16px"></i>
      </div>
      <div>
        <h2 class="font-display" style="font-size:22px;color:var(--text);margin:0">Chat dengan Owner</h2>
        <p style="font-size:13px;color:var(--muted);margin:3px 0 0">Komunikasi langsung dengan pemilik outlet</p>
      </div>
    </div>
  </x-slot>

  <div style="height:calc(100vh - 130px);display:flex;gap:0;overflow:hidden;max-width:100%;margin:0 20px 0">

    {{-- ── Daftar Percakapan ── --}}
    <div id="conv-panel" style="width:300px;min-width:260px;flex-shrink:0;border-right:1px solid var(--border);
                                 display:flex;flex-direction:column;overflow:hidden;background:var(--surface)">
      <div style="padding:14px 16px;border-bottom:1px solid var(--border);flex-shrink:0">
        <input id="conv-search" type="text" placeholder="Cari owner…"
          oninput="filterConv(this.value)"
          style="width:100%;padding:8px 12px;border-radius:10px;border:1.5px solid var(--border);
                 background:var(--surface2);color:var(--text);font-size:13px;outline:none;font-family:inherit">
      </div>
      <div id="conv-list" style="overflow-y:auto;flex:1;padding:6px 0">
        <div style="text-align:center;padding:40px 20px;color:var(--muted)">
          <i class="fa-solid fa-spinner fa-spin" style="font-size:20px;margin-bottom:10px;display:block"></i>
          Memuat percakapan…
        </div>
      </div>
    </div>

    {{-- ── Panel Chat ── --}}
    <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0">

      {{-- Placeholder saat belum pilih --}}
      <div id="chat-placeholder" style="flex:1;display:flex;flex-direction:column;align-items:center;
                                         justify-content:center;color:var(--muted)">
        <i class="fa-regular fa-comments" style="font-size:48px;margin-bottom:16px;opacity:.4"></i>
        <div style="font-size:15px;font-weight:600;opacity:.6">Pilih percakapan untuk memulai chat</div>
      </div>

      {{-- Chat Window --}}
      <div id="chat-window" style="display:none;flex:1;flex-direction:column;overflow:hidden">

        {{-- Header chat --}}
        <div id="chat-header" style="padding:14px 20px;border-bottom:1px solid var(--border);
                                      display:flex;align-items:center;gap:12px;flex-shrink:0;background:var(--surface)">
          <div id="chat-avatar" style="width:38px;height:38px;border-radius:50%;background:var(--ac-lt);
                                        color:var(--ac);display:grid;place-items:center;flex-shrink:0">
            <i class="fa-solid fa-user" style="font-size:15px"></i>
          </div>
          <div style="flex:1">
            <div id="chat-owner-name" style="font-size:14px;font-weight:700;color:var(--text)">—</div>
            <div id="chat-status" style="font-size:11.5px;color:var(--muted)">Online</div>
          </div>
          <a id="chat-profile-link" href="#"
            style="padding:7px 12px;border-radius:9px;background:var(--surface2);color:var(--muted);
                   text-decoration:none;font-size:12px;font-weight:600;display:flex;align-items:center;gap:6px;
                   flex-shrink:0;transition:color .15s"
            title="Lihat Profil Owner"
            onmouseenter="this.style.color='var(--text)'" onmouseleave="this.style.color='var(--muted)'">
            <i class="fa-solid fa-user" style="font-size:11px"></i> Profil
          </a>
        </div>

        {{-- Messages --}}
        <div id="chat-messages" style="flex:1;overflow-y:auto;padding:16px 20px;display:flex;flex-direction:column;gap:10px">
        </div>

        {{-- Input --}}
        <div style="padding:12px 20px;border-top:1px solid var(--border);background:var(--surface);flex-shrink:0">
          <form id="chat-form" onsubmit="adminSend(event)" style="display:flex;gap:10px;align-items:flex-end">
            <textarea id="chat-input" rows="1" maxlength="2000"
              placeholder="Tulis pesan…"
              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();adminSend(event)}"
              oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"
              style="flex:1;padding:10px 14px;border-radius:12px;border:1.5px solid var(--border);
                     background:var(--surface2);color:var(--text);font-size:13.5px;outline:none;
                     font-family:inherit;resize:none;line-height:1.5;max-height:120px;transition:border-color .15s"
              onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'"></textarea>
            <button type="submit"
              style="width:42px;height:42px;border-radius:12px;background:var(--ac);color:#fff;
                     border:none;cursor:pointer;display:grid;place-items:center;flex-shrink:0;
                     transition:opacity .15s">
              <i class="fa-solid fa-paper-plane" style="font-size:15px"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
  <script>
  (function(){
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var state = { ownerId: null, ownerName: '', lastId: 0, polling: null, convData: [] };

    // ── Load conversations ──
    function loadConversations() {
      fetch('{{ route('chat.conversations') }}', {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
      })
      .then(r => r.json())
      .then(function(data) {
        state.convData = data;
        renderConvList(data);
      });
    }

    function renderConvList(data) {
      var q = document.getElementById('conv-search').value.toLowerCase();
      var filtered = q ? data.filter(c => c.owner_name.toLowerCase().includes(q)) : data;
      var el = document.getElementById('conv-list');
      if (!filtered.length) {
        el.innerHTML = '<div style="text-align:center;padding:32px 16px;color:var(--muted);font-size:13px">Belum ada percakapan.</div>';
        return;
      }
      var profileBase = '{{ url('owners') }}';
      el.innerHTML = filtered.map(function(c) {
        var active = state.ownerId == c.owner_id;
        return '<div style="padding:12px 16px;border-bottom:1px solid var(--border);' +
          'background:' + (active ? 'var(--ac-lt)' : 'transparent') + ';transition:background .15s" ' +
          'onmouseenter="if(!' + active + ')this.style.background=\'var(--surface2)\'" ' +
          'onmouseleave="this.style.background=\'' + (active ? 'var(--ac-lt)' : 'transparent') + '\'">' +
          '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;gap:8px">' +
          '<div onclick="selectOwner(' + c.owner_id + ',\'' + esc(c.owner_name) + '\')" ' +
          'style="font-size:13px;font-weight:' + (c.unread > 0 ? '800' : '600') + ';' +
          'color:' + (active ? 'var(--ac)' : 'var(--text)') + ';cursor:pointer;flex:1;min-width:0;' +
          'white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + esc(c.owner_name) + '</div>' +
          '<div style="display:flex;align-items:center;gap:5px;flex-shrink:0">' +
          '<span style="font-size:10.5px;color:var(--muted)">' + esc(c.last_at) + '</span>' +
          (c.unread > 0 ? '<span style="font-size:9.5px;font-weight:800;padding:1px 6px;border-radius:99px;background:var(--ac);color:#fff">' + c.unread + '</span>' : '') +
          '<a href="' + profileBase + '/' + c.owner_id + '" title="Profil Owner" onclick="event.stopPropagation()" ' +
          'style="width:22px;height:22px;border-radius:6px;background:var(--surface2);color:var(--muted);' +
          'display:grid;place-items:center;text-decoration:none;font-size:10px">' +
          '<i class="fa-solid fa-user"></i></a>' +
          '</div></div>' +
          '<div onclick="selectOwner(' + c.owner_id + ',\'' + esc(c.owner_name) + '\')" ' +
          'style="font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:pointer">' +
          esc(c.last_msg) + '</div>' +
          '</div>';
      }).join('');
    }

    window.filterConv = function(q) { renderConvList(state.convData); };

    // ── Select owner ──
    var ownerProfileBase = '{{ url('owners') }}';
    window.selectOwner = function(ownerId, ownerName) {
      if (state.ownerId === ownerId) return;
      state.ownerId   = ownerId;
      state.ownerName = ownerName;
      state.lastId    = 0;
      document.getElementById('chat-owner-name').textContent = ownerName;
      document.getElementById('chat-profile-link').href      = ownerProfileBase + '/' + ownerId;
      document.getElementById('chat-messages').innerHTML     = '';
      document.getElementById('chat-placeholder').style.display = 'none';
      document.getElementById('chat-window').style.display      = 'flex';
      if (state.polling) clearInterval(state.polling);
      pollMessages();
      state.polling = setInterval(pollMessages, 3000);
      renderConvList(state.convData);
    };

    // ── Poll messages ──
    function pollMessages() {
      if (!state.ownerId) return;
      fetch('{{ url('chat/messages') }}/' + state.ownerId + '?since=' + state.lastId, {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
      })
      .then(r => r.json())
      .then(function(msgs) {
        if (!msgs.length) return;
        var box    = document.getElementById('chat-messages');
        var atBot  = box.scrollHeight - box.scrollTop - box.clientHeight < 80;
        var lastDate = box.dataset.lastDate || '';
        msgs.forEach(function(m) {
          if (m.date !== lastDate) {
            var sep = document.createElement('div');
            sep.style.cssText = 'text-align:center;font-size:11px;color:var(--muted);margin:6px 0';
            sep.textContent = m.date;
            box.appendChild(sep);
            lastDate = m.date;
          }
          box.appendChild(buildBubble(m));
          state.lastId = Math.max(state.lastId, m.id);
        });
        box.dataset.lastDate = lastDate;
        if (atBot || state.lastId === msgs[msgs.length-1].id) box.scrollTop = box.scrollHeight;
      });
    }

    function buildBubble(m) {
      var isAdmin = m.from_admin;
      var wrap = document.createElement('div');
      wrap.style.cssText = 'display:flex;justify-content:' + (isAdmin ? 'flex-end' : 'flex-start');
      wrap.innerHTML =
        '<div style="max-width:70%;padding:10px 14px;border-radius:' +
        (isAdmin ? '16px 4px 16px 16px' : '4px 16px 16px 16px') +
        ';background:' + (isAdmin ? 'var(--ac)' : 'var(--surface2)') +
        ';color:' + (isAdmin ? '#fff' : 'var(--text)') +
        ';font-size:13.5px;line-height:1.6;word-break:break-word">' +
        esc(m.message) +
        '<div style="font-size:10.5px;opacity:.7;margin-top:4px;text-align:right">' + m.time + '</div>' +
        '</div>';
      return wrap;
    }

    // ── Admin sends message ──
    window.adminSend = function(e) {
      e.preventDefault();
      var input = document.getElementById('chat-input');
      var msg   = input.value.trim();
      if (!msg || !state.ownerId) return;
      input.value = '';
      input.style.height = 'auto';

      var data = new FormData();
      data.append('owner_id', state.ownerId);
      data.append('message', msg);

      fetch('{{ route('chat.send') }}', {
        method: 'POST', body: data,
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN': CSRF}
      })
      .then(r => r.json())
      .then(function(m) {
        var box = document.getElementById('chat-messages');
        box.appendChild(buildBubble(m));
        box.scrollTop = box.scrollHeight;
        state.lastId = Math.max(state.lastId, m.id);
        loadConversations();
      })
      .catch(function() { input.value = msg; });
    };

    function esc(s) {
      return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Init
    loadConversations();
    setInterval(loadConversations, 8000);
  })();
  </script>
  @endpush
</x-app-layout>
