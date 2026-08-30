@php
  $fabUser = Auth::user();
  $fabRole = $fabUser?->role;
  // Disembunyikan khusus di halaman POS/Kasir (semua modul: fnb.pos.index,
  // retail.pos.index, salon.pos.index, laundry.pos.index) — posisinya
  // (bottom:28px;right:28px) bertabrakan dengan tombol keranjang/bayar POS
  // (bottom:24px;right:24px), jadi tombol bayar ketutupan. Tetap tampil di
  // halaman lain (dashboard, laporan, dst).
  $showFab = $fabRole === 'owner' && !request()->routeIs('*.pos.index');
@endphp

@if($showFab)
@php $fabOwnerId = $fabUser->id; @endphp

{{-- ── Floating Action Button ── --}}
<div id="fab-wrapper" style="position:fixed;bottom:28px;right:28px;z-index:1200;display:flex;flex-direction:column;align-items:flex-end;gap:10px">

  {{-- Bubble chat unread --}}
  <div id="fab-unread-bubble"
    style="display:none;background:var(--ac);color:#fff;border-radius:16px;
           padding:8px 14px;font-size:12.5px;font-weight:700;box-shadow:0 4px 16px rgba(0,0,0,.3);
           animation:fabBounce .4s ease;white-space:nowrap;max-width:200px">
    <i class="fa-solid fa-message" style="margin-right:6px"></i>
    <span id="fab-unread-text">Pesan baru dari Admin</span>
  </div>

  <button id="fab-main-btn" onclick="fabOpen()"
    style="width:54px;height:54px;border-radius:50%;background:var(--ac);color:#fff;border:none;
           cursor:pointer;display:flex;align-items:center;justify-content:center;
           box-shadow:0 4px 20px rgba(0,0,0,.35);transition:transform .2s,box-shadow .2s;position:relative"
    onmouseenter="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 28px rgba(0,0,0,.45)'"
    onmouseleave="this.style.transform='scale(1)';this.style.boxShadow='0 4px 20px rgba(0,0,0,.35)'"
    title="Chat &amp; Laporan Administrator">
    <i id="fab-icon" class="fa-solid fa-comments" style="font-size:22px"></i>
    <span id="fab-badge" style="position:absolute;top:4px;right:4px;min-width:16px;height:16px;
      border-radius:99px;background:#f59e0b;font-size:9px;font-weight:800;color:#000;
      display:none;align-items:center;justify-content:center;padding:0 4px;line-height:1">0</span>
  </button>
</div>

{{-- ── Overlay ── --}}
<div id="fab-overlay" onclick="fabClose()"
  style="display:none;position:fixed;inset:0;z-index:1199;background:rgba(0,0,0,.4);backdrop-filter:blur(2px)"></div>

{{-- ── Panel ── --}}
<div id="fab-panel"
  style="position:fixed;bottom:0;right:0;z-index:1200;width:100%;max-width:460px;
         background:var(--surface);border-radius:20px 20px 0 0;
         box-shadow:0 -8px 40px rgba(0,0,0,.4);transform:translateY(100%);
         transition:transform .3s cubic-bezier(.4,0,.2,1);max-height:90vh;
         display:flex;flex-direction:column;overflow:hidden">

  {{-- Header --}}
  <div style="padding:16px 20px 12px;border-bottom:1px solid var(--border);
               display:flex;align-items:center;gap:12px;flex-shrink:0">
    <div style="width:36px;height:36px;border-radius:50%;background:var(--ac-lt);color:var(--ac);
                display:grid;place-items:center;flex-shrink:0">
      <i class="fa-solid fa-comments" style="font-size:15px"></i>
    </div>
    <div style="flex:1">
      <div style="font-size:14px;font-weight:700;color:var(--text)">Administrator</div>
      <div style="font-size:11.5px;color:var(--muted)">Chat &amp; laporan sistem</div>
    </div>
    <button onclick="fabClose()" style="background:none;border:none;cursor:pointer;color:var(--muted);
      padding:6px;border-radius:8px;font-size:18px;line-height:1;transition:color .15s"
      onmouseenter="this.style.color='var(--text)'" onmouseleave="this.style.color='var(--muted)'">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  {{-- Tabs --}}
  <div style="display:flex;border-bottom:1px solid var(--border);flex-shrink:0">
    <button id="fab-tab-chat" onclick="fabTab('chat')"
      style="flex:1;padding:10px;font-size:13px;font-weight:600;background:none;border:none;cursor:pointer;
             border-bottom:2px solid var(--ac);color:var(--ac);display:flex;align-items:center;justify-content:center;gap:6px">
      <i class="fa-solid fa-comments" style="font-size:12px"></i>Chat
      <span id="fab-chat-badge" style="display:none;font-size:9.5px;font-weight:800;padding:1px 6px;border-radius:99px;
                                        background:rgba(245,158,11,.18);color:#f59e0b">0</span>
    </button>
    <button id="fab-tab-send" onclick="fabTab('send')"
      style="flex:1;padding:10px;font-size:13px;font-weight:600;background:none;border:none;cursor:pointer;
             border-bottom:2px solid transparent;color:var(--muted);display:flex;align-items:center;justify-content:center;gap:6px">
      <i class="fa-solid fa-flag" style="font-size:12px"></i>Laporan
    </button>
    <button id="fab-tab-history" onclick="fabTab('history')"
      style="flex:1;padding:10px;font-size:13px;font-weight:600;background:none;border:none;cursor:pointer;
             border-bottom:2px solid transparent;color:var(--muted);display:flex;align-items:center;justify-content:center;gap:6px">
      <i class="fa-solid fa-clock-rotate-left" style="font-size:12px"></i>Riwayat
    </button>
  </div>

  {{-- ═══ TAB: Chat ═══ --}}
  <div id="fab-pane-chat" style="display:flex;flex-direction:column;flex:1;overflow:hidden;min-height:0">

    {{-- Message bubbles --}}
    <div id="fab-chat-box" style="flex:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:8px">
      <div style="text-align:center;padding:30px 0;color:var(--muted)">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:20px;margin-bottom:8px;display:block"></i>
        Memuat pesan…
      </div>
    </div>

    {{-- Input --}}
    <div style="padding:10px 14px;border-top:1px solid var(--border);flex-shrink:0">
      <form id="fab-chat-form" onsubmit="fabChatSend(event)" style="display:flex;gap:8px;align-items:flex-end">
        <textarea id="fab-chat-input" rows="1" maxlength="2000"
          placeholder="Tulis pesan ke Admin…"
          onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();fabChatSend(event)}"
          oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,80)+'px'"
          style="flex:1;padding:9px 12px;border-radius:12px;border:1.5px solid var(--border);
                 background:var(--surface2);color:var(--text);font-size:13px;outline:none;
                 font-family:inherit;resize:none;max-height:80px;line-height:1.5;transition:border-color .15s"
          onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'"></textarea>
        <button type="submit"
          style="width:38px;height:38px;border-radius:10px;background:var(--ac);color:#fff;
                 border:none;cursor:pointer;display:grid;place-items:center;flex-shrink:0">
          <i class="fa-solid fa-paper-plane" style="font-size:14px"></i>
        </button>
      </form>
    </div>
  </div>

  {{-- ═══ TAB: Kirim Laporan ═══ --}}
  <div id="fab-pane-send" style="display:none;padding:18px 20px;overflow-y:auto;flex:1">
    <form id="fab-form" onsubmit="fabSubmit(event)">
      @csrf
      <div style="margin-bottom:14px">
        <label style="font-size:12px;font-weight:700;color:var(--muted);display:block;margin-bottom:6px">Perihal *</label>
        <input type="text" name="subject" id="fab-subject" maxlength="150" required
          placeholder="cth: Tidak bisa akses menu laporan"
          style="width:100%;padding:10px 14px;border-radius:10px;border:1.5px solid var(--border);
                 background:var(--surface2);color:var(--text);font-size:13.5px;outline:none;
                 font-family:inherit;transition:border-color .15s"
          onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
      </div>
      <div style="margin-bottom:16px">
        <label style="font-size:12px;font-weight:700;color:var(--muted);display:block;margin-bottom:6px">
          Pesan <span style="font-weight:400">(maks. 2000 karakter)</span> *
        </label>
        <textarea name="message" id="fab-message" rows="5" maxlength="2000" required
          placeholder="Jelaskan masalah secara detail…"
          style="width:100%;padding:10px 14px;border-radius:10px;border:1.5px solid var(--border);
                 background:var(--surface2);color:var(--text);font-size:13.5px;outline:none;
                 font-family:inherit;resize:vertical;transition:border-color .15s"
          onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'"></textarea>
        <div style="text-align:right;font-size:11px;color:var(--muted);margin-top:4px">
          <span id="fab-charcount">0</span>/2000
        </div>
      </div>
      <button type="submit" id="fab-submit-btn"
        style="width:100%;padding:11px;border-radius:10px;background:var(--ac);color:#fff;
               font-size:13.5px;font-weight:700;border:none;cursor:pointer;
               display:flex;align-items:center;justify-content:center;gap:8px">
        <i class="fa-solid fa-paper-plane"></i> Kirim Laporan
      </button>
    </form>
  </div>

  {{-- ═══ TAB: Riwayat Laporan ═══ --}}
  <div id="fab-pane-history" style="display:none;overflow-y:auto;flex:1">
    <div id="fab-history-body" style="padding:12px 20px">
      <div style="text-align:center;padding:32px 0;color:var(--muted)">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:22px;margin-bottom:10px;display:block"></i>
        Memuat riwayat…
      </div>
    </div>
  </div>
</div>

<style>
@keyframes fabBounce{0%{transform:scale(.8);opacity:0}60%{transform:scale(1.05)}100%{transform:scale(1);opacity:1}}
</style>

<script>
(function(){
  var CSRF    = document.querySelector('meta[name="csrf-token"]').content;
  var OWNER   = {{ $fabOwnerId }};
  var open    = false;
  var lastId  = 0;
  var chatPoller = null;
  var unreadPoller = null;
  var historyLoaded = false;
  var activeTab = 'chat';

  // ── Open / Close ──
  window.fabOpen = function() {
    document.getElementById('fab-overlay').style.display = 'block';
    document.getElementById('fab-panel').style.transform = 'translateY(0)';
    document.getElementById('fab-unread-bubble').style.display = 'none';
    open = true;
    if (activeTab === 'chat') startChatPoller();
  };

  window.fabClose = function() {
    document.getElementById('fab-overlay').style.display = 'none';
    document.getElementById('fab-panel').style.transform = 'translateY(100%)';
    stopChatPoller();
    open = false;
  };

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && open) fabClose();
  });

  // ── Tabs ──
  var tabs = ['chat','send','history'];
  window.fabTab = function(tab) {
    activeTab = tab;
    tabs.forEach(function(t) {
      var pane = document.getElementById('fab-pane-' + t);
      var btn  = document.getElementById('fab-tab-' + t);
      var isActive = t === tab;
      pane.style.display = isActive ? (t === 'chat' ? 'flex' : 'block') : 'none';
      btn.style.borderBottomColor = isActive ? 'var(--ac)' : 'transparent';
      btn.style.color = isActive ? 'var(--ac)' : 'var(--muted)';
    });
    if (tab === 'chat') { startChatPoller(); }
    else { stopChatPoller(); }
    if (tab === 'history' && !historyLoaded) fabLoadHistory();
  };

  // ── Chat Polling ──
  function startChatPoller() {
    if (chatPoller) return;
    loadChatMessages();
    chatPoller = setInterval(loadChatMessages, 3000);
  }
  function stopChatPoller() {
    if (chatPoller) { clearInterval(chatPoller); chatPoller = null; }
  }

  function loadChatMessages() {
    fetch('{{ url('chat/messages') }}/' + OWNER + '?since=' + lastId, {
      headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
    .then(r => r.json())
    .then(function(msgs) {
      if (!msgs.length) return;
      var box = document.getElementById('fab-chat-box');
      var wasLoading = box.querySelector('.fa-spinner');
      if (wasLoading) box.innerHTML = '';
      var atBot = box.scrollHeight - box.scrollTop - box.clientHeight < 80;
      var lastDate = box.dataset.lastDate || '';
      msgs.forEach(function(m) {
        if (m.date !== lastDate) {
          var sep = document.createElement('div');
          sep.style.cssText = 'text-align:center;font-size:10.5px;color:var(--muted);margin:4px 0';
          sep.textContent = m.date;
          box.appendChild(sep);
          lastDate = m.date;
        }
        box.appendChild(buildBubble(m));
        lastId = Math.max(lastId, m.id);
      });
      box.dataset.lastDate = lastDate;
      if (atBot || lastId > 0) box.scrollTop = box.scrollHeight;
      // clear badge when reading
      updateBadge(0);
    })
    .catch(function(){});
  }

  function buildBubble(m) {
    var fromAdmin = m.from_admin;
    var wrap = document.createElement('div');
    wrap.style.cssText = 'display:flex;justify-content:' + (fromAdmin ? 'flex-start' : 'flex-end');
    wrap.innerHTML =
      '<div style="max-width:80%;padding:9px 13px;border-radius:' +
      (fromAdmin ? '4px 14px 14px 14px' : '14px 4px 14px 14px') +
      ';background:' + (fromAdmin ? 'var(--surface2)' : 'var(--ac)') +
      ';color:' + (fromAdmin ? 'var(--text)' : '#fff') +
      ';font-size:13px;line-height:1.6;word-break:break-word">' +
      (fromAdmin ? '<div style="font-size:10px;font-weight:700;opacity:.65;margin-bottom:2px">Administrator</div>' : '') +
      esc(m.message) +
      '<div style="font-size:10px;opacity:.65;margin-top:3px;text-align:right">' + m.time + '</div>' +
      '</div>';
    return wrap;
  }

  // ── Send chat ──
  window.fabChatSend = function(e) {
    e.preventDefault();
    var input = document.getElementById('fab-chat-input');
    var msg   = input.value.trim();
    if (!msg) return;
    input.value = '';
    input.style.height = 'auto';

    var data = new FormData();
    data.append('message', msg);

    fetch('{{ route('chat.send') }}', {
      method:'POST', body:data,
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':CSRF}
    })
    .then(r => r.json())
    .then(function(m) {
      var box = document.getElementById('fab-chat-box');
      box.appendChild(buildBubble(m));
      box.scrollTop = box.scrollHeight;
      lastId = Math.max(lastId, m.id);
    })
    .catch(function() { input.value = msg; });
  };

  // ── Unread badge (background poll) ──
  function checkUnread() {
    fetch('{{ route('chat.unread-count') }}', {
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
    .then(r => r.json())
    .then(function(res) {
      updateBadge(open ? 0 : res.count);
    })
    .catch(function(){});
  }

  function updateBadge(n) {
    var badge  = document.getElementById('fab-badge');
    var chatBadge = document.getElementById('fab-chat-badge');
    var bubble = document.getElementById('fab-unread-bubble');
    if (n > 0) {
      badge.textContent = n;
      badge.style.display = 'flex';
      chatBadge.textContent = n;
      chatBadge.style.display = 'inline-flex';
      if (!open) { bubble.style.display = 'block'; }
    } else {
      badge.style.display = 'none';
      chatBadge.style.display = 'none';
      bubble.style.display = 'none';
    }
  }

  unreadPoller = setInterval(checkUnread, 5000);
  checkUnread();

  // ── Kirim Laporan ──
  window.fabSubmit = function(e) {
    e.preventDefault();
    var btn  = document.getElementById('fab-submit-btn');
    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim…';
    var data = new FormData(document.getElementById('fab-form'));
    fetch('{{ route('system-reports.store') }}', {
      method:'POST', body:data,
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':CSRF}
    })
    .then(r => r.json())
    .then(function(res) {
      if (res.errors) throw res;
      document.getElementById('fab-form').reset();
      document.getElementById('fab-charcount').textContent = '0';
      historyLoaded = false;
      fabClose();
      if (typeof showToast === 'function') showToast('success','Laporan berhasil dikirim ke Administrator.');
    })
    .catch(function(err) {
      var msg = err?.message || 'Gagal mengirim laporan.';
      if (typeof showToast === 'function') showToast('error', msg);
    })
    .finally(function() { btn.disabled = false; btn.innerHTML = orig; });
  };

  // ── Riwayat Laporan ──
  window.fabLoadHistory = function() {
    fetch('{{ route('system-reports.mine') }}', {
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
    .then(r => r.json())
    .then(function(data) {
      historyLoaded = true;
      var el = document.getElementById('fab-history-body');
      if (!data.length) {
        el.innerHTML = '<div style="text-align:center;padding:40px 0;color:var(--muted)"><i class="fa-solid fa-inbox" style="font-size:28px;margin-bottom:10px;display:block"></i>Belum ada laporan.</div>';
        return;
      }
      var html = '';
      data.forEach(function(r) {
        var isRead = r.status === 'read';
        html += '<div style="padding:14px 0;border-bottom:1px solid var(--border)">';
        html += '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:6px">';
        html += '<div style="font-size:13px;font-weight:700;color:var(--text);flex:1">' + esc(r.subject) + '</div>';
        html += '<span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;flex-shrink:0;' +
          (isRead ? 'background:rgba(16,185,129,.12);color:#34d399' : 'background:rgba(245,158,11,.12);color:#f59e0b') + '">' +
          (isRead ? 'Dibaca' : 'Menunggu') + '</span></div>';
        html += '<div style="font-size:12px;color:var(--muted);line-height:1.5;margin-bottom:' + (r.admin_reply ? '10px' : '0') + '">' + esc(r.message) + '</div>';
        if (r.admin_reply) {
          html += '<div style="background:var(--ac-lt);border-left:3px solid var(--ac);border-radius:0 8px 8px 0;padding:8px 12px">';
          html += '<div style="font-size:10.5px;font-weight:700;color:var(--ac);margin-bottom:4px"><i class="fa-solid fa-reply"></i> Balasan Administrator</div>';
          html += '<div style="font-size:12px;color:var(--text);line-height:1.5">' + esc(r.admin_reply) + '</div></div>';
        }
        html += '<div style="font-size:11px;color:var(--muted);margin-top:6px">' + esc(r.created_at) + '</div>';
        html += '</div>';
      });
      el.innerHTML = html;
    })
    .catch(function() {
      document.getElementById('fab-history-body').innerHTML =
        '<div style="text-align:center;padding:32px;color:#f87171"><i class="fa-solid fa-circle-exclamation"></i> Gagal memuat.</div>';
    });
  };

  // ── Charcount ──
  document.getElementById('fab-message').addEventListener('input', function() {
    document.getElementById('fab-charcount').textContent = this.value.length;
  });

  function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>
@endif
