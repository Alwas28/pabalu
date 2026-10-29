@props(['adminWa' => null])
@php
  // Asisten Pabalu — dipasang di layout publik, guest (login/daftar), app, dan 5 layout outlet.
  // Pojok kanan bawah (kiri bawah tertutup sidebar). Ini satu-satunya tombol chat: untuk owner,
  // panelnya punya tab "Chat Admin" yang membuka panel system-report-fab (tombol FAB-nya disembunyikan).
  // Disembunyikan di halaman POS agar tidak menutupi tombol keranjang/bayar.
  $pbcShow      = !request()->routeIs('*.pos.index');
  $pbcLoggedIn  = Auth::check();
  // Sama dengan syarat tampil system-report-fab (owner, bukan halaman POS).
  $pbcAdminChat = Auth::user()?->role === 'owner';
  // Layout publik mengirim link WhatsApp admin (pengganti tombol WA melayang) → tab "Chat Admin" untuk tamu.
  $pbcAdminWa   = $pbcAdminChat ? null : $adminWa;
  $pbcSuggest  = $pbcLoggedIn
    ? ['Cara menambah akun kasir', 'Cara melakukan closing harian', 'Alur pesanan laundry']
    : ['Apa itu Pabalu?', 'Jenis usaha apa saja yang didukung?', 'Bagaimana cara mendaftar?'];
  // Disiapkan di sini, bukan langsung di @json(...): Blade memecah argumen @json per koma,
  // jadi teks yang mengandung koma merusak hasil kompilasinya.
  $pbcGreeting = $pbcLoggedIn
    ? 'Halo! Saya Asisten Pabalu. Tanyakan cara memakai fitur apa saja — misalnya menambah kasir, mengatur stok, atau membaca laporan.'
    : 'Halo! Saya Asisten Pabalu. Saya bisa menjelaskan apa itu Pabalu, kelebihannya, dan cara mulai memakainya.';
  $pbcTeaser = $pbcLoggedIn
    ? 'Ada yang bisa saya bantu? Tanyakan cara memakai fitur Pabalu di sini.'
    : 'Baru di Pabalu? Tanyakan apa saja seputar aplikasi ini, saya siap bantu.';
@endphp

@if($pbcShow)
<style>
#pbc-root{--pbc-ac:var(--ac,#dc2626);font-family:inherit}
#pbc-btn{position:fixed;right:24px;bottom:24px;z-index:1190;width:54px;height:54px;border-radius:50%;border:none;
  background:var(--pbc-ac);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;
  box-shadow:0 4px 20px rgba(0,0,0,.3);transition:transform .2s}
#pbc-btn:hover{transform:scale(1.08)}
.pbc-badge{position:absolute;top:2px;right:2px;min-width:18px;height:18px;padding:0 5px;border-radius:99px;
  background:#f59e0b;color:#000;font-size:10px;font-weight:800;display:none;align-items:center;justify-content:center}
#pbc-bubble{position:fixed;right:24px;bottom:88px;z-index:1190;display:none;background:var(--pbc-ac);color:#fff;
  border:none;border-radius:16px;padding:8px 14px;font-size:12.5px;font-weight:700;cursor:pointer;
  box-shadow:0 4px 16px rgba(0,0,0,.3);font-family:inherit}
.pbc-tabs{display:flex;background:#fff;border-bottom:1px solid #e5e7eb}
.pbc-tabs button,.pbc-tabs a{flex:1;text-decoration:none;padding:9px;border:none;background:none;cursor:pointer;font-size:12.5px;font-weight:600;
  color:#6b7280;border-bottom:2px solid transparent;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:6px}
.pbc-tabs button.pbc-active{color:var(--pbc-ac);border-bottom-color:var(--pbc-ac)}
.pbc-tabs .pbc-badge{position:static}
#pbc-panel{position:fixed;right:24px;bottom:90px;z-index:1191;width:360px;max-width:calc(100vw - 32px);
  height:520px;max-height:calc(100vh - 120px);background:#fff;color:#1f2937;border-radius:16px;
  box-shadow:0 12px 40px rgba(0,0,0,.28);display:none;flex-direction:column;overflow:hidden}
#pbc-panel.pbc-open{display:flex}
.pbc-head{background:var(--pbc-ac);color:#fff;padding:12px 14px;display:flex;align-items:center;gap:10px}
.pbc-head strong{font-size:14px;display:block}
.pbc-head span{font-size:11px;opacity:.85}
.pbc-head button{margin-left:auto;background:transparent;border:none;color:#fff;cursor:pointer;padding:4px;opacity:.85}
.pbc-head button:hover{opacity:1}
#pbc-log{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;background:#f8fafc}
.pbc-msg{max-width:85%;padding:9px 12px;border-radius:12px;font-size:13px;line-height:1.5;word-wrap:break-word}
.pbc-bot{background:#fff;border:1px solid #e5e7eb;align-self:flex-start;border-bottom-left-radius:4px}
.pbc-user{background:var(--pbc-ac);color:#fff;align-self:flex-end;border-bottom-right-radius:4px}
.pbc-avatar{width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;background:#fff;border:1px solid #e5e7eb}
.pbc-row{display:flex;gap:8px;align-items:flex-end;align-self:flex-start;max-width:90%}
.pbc-row .pbc-msg{max-width:none}
.pbc-in{animation:pbcIn .28s ease-out}
@keyframes pbcIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.pbc-dots{display:inline-flex;gap:4px;padding:4px 2px}
.pbc-dots i{width:7px;height:7px;border-radius:50%;background:#9ca3af;animation:pbcDot 1.2s infinite ease-in-out}
.pbc-dots i:nth-child(2){animation-delay:.15s}
.pbc-dots i:nth-child(3){animation-delay:.3s}
@keyframes pbcDot{0%,60%,100%{transform:translateY(0);opacity:.45}30%{transform:translateY(-5px);opacity:1}}
.pbc-head .pbc-avatar{width:36px;height:36px;border:2px solid rgba(255,255,255,.7)}
.pbc-online{font-size:11px;opacity:.85}
.pbc-online.pbc-busy{font-style:italic}
#pbc-btn .pbc-btn-avatar{width:100%;height:100%;border-radius:50%;object-fit:cover;border:3px solid var(--pbc-ac);background:#fff}
.pbc-sep{align-self:center;font-size:10.5px;color:#9ca3af}
#pbc-teaser{position:fixed;right:24px;bottom:90px;z-index:1190;width:250px;max-width:calc(100vw - 32px);display:none;
  background:#fff;color:#1f2937;border-radius:14px 14px 4px 14px;box-shadow:0 8px 28px rgba(0,0,0,.22);
  padding:12px 30px 12px 12px;font-size:13px;line-height:1.5;cursor:pointer;gap:10px;align-items:flex-start}
#pbc-teaser.pbc-show{display:flex;animation:pbcIn .3s ease-out}
#pbc-teaser strong{display:block;font-size:12.5px;margin-bottom:2px}
#pbc-teaser-close{position:absolute;top:6px;right:6px;border:none;background:none;color:#9ca3af;cursor:pointer;
  font-size:16px;line-height:1;padding:2px 4px}
#pbc-teaser-close:hover{color:#4b5563}
#pbc-chips{display:flex;flex-wrap:wrap;gap:6px;padding:0 14px 10px;background:#f8fafc}
#pbc-chips button{border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:99px;padding:5px 10px;
  font-size:11.5px;cursor:pointer;font-family:inherit}
#pbc-chips button:hover{border-color:var(--pbc-ac);color:var(--pbc-ac)}
#pbc-form{display:flex;gap:8px;padding:10px;border-top:1px solid #e5e7eb;background:#fff}
#pbc-input{flex:1;border:1px solid #d1d5db;border-radius:10px;padding:9px 11px;font-size:13px;font-family:inherit;
  color:#1f2937;background:#fff;resize:none;outline:none}
#pbc-input:focus{border-color:var(--pbc-ac)}
#pbc-send{border:none;background:var(--pbc-ac);color:#fff;border-radius:10px;padding:0 14px;cursor:pointer}
#pbc-send:disabled{opacity:.5;cursor:default}
.pbc-note{font-size:10.5px;color:#9ca3af;text-align:center;padding:0 10px 8px;background:#fff}
@media (max-width:480px){#pbc-btn{right:16px}#pbc-panel{right:16px}#pbc-bubble{right:16px}#pbc-teaser{right:16px}}
</style>

<div id="pbc-root"
     data-send="{{ route('chatbot.send') }}"
     data-reset="{{ route('chatbot.reset') }}"
     @if($pbcLoggedIn) data-history="{{ route('chatbot.history') }}" @endif
     data-csrf="{{ csrf_token() }}"
     data-avatar="{{ asset('img/avatar-asisten.png') }}">

  <button id="pbc-btn" type="button" aria-label="Buka Asisten Pabalu" title="Asisten Pabalu">
    <img class="pbc-btn-avatar" src="{{ asset('img/avatar-asisten.png') }}" alt="">
    @if($pbcAdminChat)<span class="pbc-badge" id="pbc-btn-badge">0</span>@endif
    <span class="pbc-badge" id="pbc-new-badge">1</span>
  </button>

  {{-- Sapaan otomatis untuk pengunjung baru (lihat initTeaser di bawah). --}}
  <div id="pbc-teaser" role="button" tabindex="0" aria-label="Buka Asisten Pabalu">
    <img class="pbc-avatar" src="{{ asset('img/avatar-asisten.png') }}" alt="">
    <div id="pbc-teaser-body"><span class="pbc-dots" aria-hidden="true"><i></i><i></i><i></i></span></div>
    <button type="button" id="pbc-teaser-close" aria-label="Tutup sapaan">&times;</button>
  </div>

  @if($pbcAdminChat)
  <button id="pbc-bubble" type="button">Pesan baru dari Admin</button>
  @endif

  <div id="pbc-panel" role="dialog" aria-label="Asisten Pabalu">
    <div class="pbc-head">
      <img class="pbc-avatar" src="{{ asset('img/avatar-asisten.png') }}" alt="">
      <div>
        <strong>Asisten Pabalu</strong>
        <span class="pbc-online" id="pbc-status">{{ $pbcLoggedIn ? 'Panduan penggunaan aplikasi' : 'Tanya seputar Pabalu' }}</span>
      </div>
      @php $pbcResetLabel = $pbcLoggedIn ? 'Hapus riwayat chat' : 'Mulai percakapan baru'; @endphp
      <button type="button" id="pbc-reset" title="{{ $pbcResetLabel }}" aria-label="{{ $pbcResetLabel }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
      </button>
      <button type="button" id="pbc-close" title="Tutup" aria-label="Tutup">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    @if($pbcAdminChat || $pbcAdminWa)
    <div class="pbc-tabs">
      <button type="button" class="pbc-active">Asisten</button>
      @if($pbcAdminChat)
      <button type="button" id="pbc-to-admin">Chat Admin <span class="pbc-badge" id="pbc-tab-badge">0</span></button>
      @else
      <a href="{{ $pbcAdminWa }}" target="_blank" rel="noopener">Chat Admin (WhatsApp)</a>
      @endif
    </div>
    @endif

    <div id="pbc-log" aria-live="polite"></div>

    <div id="pbc-chips">
      @foreach($pbcSuggest as $s)
        <button type="button" data-q="{{ $s }}">{{ $s }}</button>
      @endforeach
    </div>

    <form id="pbc-form" autocomplete="off">
      <textarea id="pbc-input" rows="1" maxlength="500" placeholder="Tulis pertanyaan…"></textarea>
      <button id="pbc-send" type="submit" aria-label="Kirim">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg>
      </button>
    </form>
    <div class="pbc-note">Jangan bagikan password, OTP, atau kode rahasia di chat ini.</div>
  </div>
</div>

<script>
(function () {
  var root  = document.getElementById('pbc-root');
  var panel = document.getElementById('pbc-panel');
  var log   = document.getElementById('pbc-log');
  var form  = document.getElementById('pbc-form');
  var input = document.getElementById('pbc-input');
  var send  = document.getElementById('pbc-send');
  var chips = document.getElementById('pbc-chips');
  var greeting = @json($pbcGreeting);
  var busy = false;

  // Balasan bot di-escape dulu, baru **tebal** dan baris baru diubah ke HTML.
  function render(text) {
    var esc = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return esc.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
  }

  // Pesan bot diberi avatar di sampingnya; animate = muncul dengan efek geser-pudar.
  function add(text, who, animate) {
    var el = document.createElement('div');
    el.className = 'pbc-msg ' + (who === 'user' ? 'pbc-user' : 'pbc-bot');
    if (who === 'user') { el.textContent = text; } else { el.innerHTML = render(text); }
    var node = el;
    if (who !== 'user') {
      node = document.createElement('div');
      node.className = 'pbc-row';
      var img = document.createElement('img');
      img.className = 'pbc-avatar';
      img.src = root.dataset.avatar;
      img.alt = '';
      node.appendChild(img);
      node.appendChild(el);
    }
    if (animate) node.classList.add('pbc-in');
    log.appendChild(node);
    log.scrollTop = log.scrollHeight;
    return el;
  }

  var statusEl = document.getElementById('pbc-status');
  var statusText = statusEl.textContent;
  function setTyping(on) {
    statusEl.textContent = on ? 'sedang mengetik…' : statusText;
    statusEl.classList.toggle('pbc-busy', on);
  }

  // Balasan "diketik" bertahap (maks ±1,5 detik berapa pun panjangnya), lalu baru
  // diganti HTML final (tebal/baris baru) supaya tag tidak tampil setengah jadi.
  function typeOut(el, text, done) {
    var step = Math.max(1, Math.ceil(text.length / 90));
    var i = 0;
    (function tick() {
      i += step;
      if (i >= text.length) {
        el.innerHTML = render(text);
        log.scrollTop = log.scrollHeight;
        return done();
      }
      el.textContent = text.slice(0, i);
      log.scrollTop = log.scrollHeight;
      setTimeout(tick, 16);
    })();
  }

  var MIN_TYPING_MS = 3000;
  function wait(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

  function start() {
    log.innerHTML = '';
    add(greeting, 'bot', true);
    chips.style.display = '';
  }

  // User login: riwayat tersimpan di server, ditampilkan di bawah salam pembuka.
  function loadHistory() {
    if (!root.dataset.history) return;
    fetch(root.dataset.history, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (res) { return res.ok ? res.json() : { messages: [] }; })
      .then(function (data) {
        var msgs = data.messages || [];
        if (!msgs.length) return;
        chips.style.display = 'none';
        var sep = document.createElement('div');
        sep.className = 'pbc-sep';
        sep.textContent = 'Riwayat chat sebelumnya';
        log.appendChild(sep);
        msgs.forEach(function (m) { add(m.content, m.role === 'user' ? 'user' : 'bot'); });
      })
      .catch(function () {});
  }

  function ask(question) {
    question = question.trim();
    if (!question || busy) return;
    busy = true; send.disabled = true;
    chips.style.display = 'none';
    add(question, 'user', true);
    input.value = '';
    var typing = add('', 'bot', true);
    typing.innerHTML = '<span class="pbc-dots" aria-label="Sedang mengetik"><i></i><i></i><i></i></span>';
    setTyping(true);
    // Indikator mengetik tampil minimal 3 detik walau API menjawab lebih cepat.
    var minDelay = wait(MIN_TYPING_MS);

    var request = fetch(root.dataset.send, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': root.dataset.csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ message: question })
    })
      .then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (data) {
          if (res.ok) return data.reply;
          if (res.status === 419) return 'Sesi Anda kedaluwarsa. Muat ulang halaman lalu coba lagi.';
          return data.message || 'Maaf, terjadi gangguan. Coba lagi nanti.';
        });
      })
      .catch(function () { return 'Tidak dapat terhubung. Periksa koneksi internet Anda.'; });

    Promise.all([request, minDelay]).then(function (results) {
      setTyping(false);
      typeOut(typing, results[0] || '', function () {
        busy = false; send.disabled = false;
        input.focus();
      });
    });
  }

  // ── Sapaan otomatis ──
  // Muncul hanya di kunjungan awal (maks TEASER_MAX_VISITS sesi browser) dan berhenti
  // selamanya begitu chatbot pernah dibuka atau sapaannya ditutup. Disimpan di
  // localStorage (per browser); kalau storage diblokir, sapaan tidak ditampilkan.
  var TEASER_MAX_VISITS = 2;
  var teaser    = document.getElementById('pbc-teaser');
  var newBadge  = document.getElementById('pbc-new-badge');
  var teaserMsg = @json($pbcTeaser);

  function storeGet(key) { try { return localStorage.getItem(key); } catch (e) { return null; } }
  function storeSet(key, val) { try { localStorage.setItem(key, val); } catch (e) {} }

  function hideTeaser(remember) {
    teaser.classList.remove('pbc-show');
    newBadge.style.display = 'none';
    if (remember) storeSet('pbc_teaser_done', '1');
  }

  function initTeaser() {
    try {
      if (storeGet('pbc_teaser_done')) return;
      // Hitung kunjungan sekali per sesi browser, bukan per halaman.
      var visits = parseInt(storeGet('pbc_visits') || '0', 10);
      if (!sessionStorage.getItem('pbc_counted')) {
        visits += 1;
        storeSet('pbc_visits', String(visits));
        sessionStorage.setItem('pbc_counted', '1');
      }
      if (visits > TEASER_MAX_VISITS) return;
    } catch (e) { return; }

    setTimeout(function () {
      // Jangan bertumpuk dengan panel yang sudah terbuka atau notifikasi pesan admin.
      if (panel.classList.contains('pbc-open')) return;
      if (bubble && bubble.style.display === 'block') return;
      newBadge.style.display = 'inline-flex';
      teaser.classList.add('pbc-show');
      setTimeout(function () {
        document.getElementById('pbc-teaser-body').innerHTML =
          '<strong>Halo! 👋</strong>' + render(teaserMsg);
      }, 1500);
    }, 2500);
  }

  teaser.addEventListener('click', function (e) {
    if (e.target.id === 'pbc-teaser-close') { hideTeaser(true); return; }
    openPanel();
  });

  function openPanel() {
    hideTeaser(true);
    panel.classList.add('pbc-open');
    if (!log.children.length) { start(); loadHistory(); }
    input.focus();
  }
  // Dipanggil juga dari tombol "Asisten" di panel Chat Admin (system-report-fab).
  window.pbcOpen = openPanel;

  document.getElementById('pbc-btn').addEventListener('click', function () {
    if (panel.classList.contains('pbc-open')) { panel.classList.remove('pbc-open'); } else { openPanel(); }
  });

  // ── Chat Admin (owner): panel milik system-report-fab, dibuka lewat fabOpen() ──
  function openAdmin() {
    panel.classList.remove('pbc-open');
    if (window.fabOpen) window.fabOpen();
  }
  var toAdmin = document.getElementById('pbc-to-admin');
  if (toAdmin) toAdmin.addEventListener('click', openAdmin);
  var bubble = document.getElementById('pbc-bubble');
  if (bubble) bubble.addEventListener('click', openAdmin);

  document.addEventListener('pabalu:admin-unread', function (e) {
    var n = e.detail;
    ['pbc-btn-badge', 'pbc-tab-badge'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.textContent = n;
      el.style.display = n > 0 ? 'inline-flex' : 'none';
    });
    if (bubble) bubble.style.display = (n > 0 && !panel.classList.contains('pbc-open')) ? 'block' : 'none';
  });
  document.getElementById('pbc-close').addEventListener('click', function () { panel.classList.remove('pbc-open'); });
  document.getElementById('pbc-reset').addEventListener('click', function () {
    if (root.dataset.history && !confirm('Hapus seluruh riwayat chat dengan Asisten?')) return;
    fetch(root.dataset.reset, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf, 'X-Requested-With': 'XMLHttpRequest' }
    }).finally(start);
  });
  chips.addEventListener('click', function (e) {
    if (e.target.dataset.q) ask(e.target.dataset.q);
  });
  form.addEventListener('submit', function (e) { e.preventDefault(); ask(input.value); });
  initTeaser();
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); ask(input.value); }
  });
})();
</script>
@endif
