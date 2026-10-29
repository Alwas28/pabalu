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
.pbc-typing{color:#6b7280;font-style:italic}
.pbc-sep{align-self:center;font-size:10.5px;color:#9ca3af}
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
@media (max-width:480px){#pbc-btn{right:16px}#pbc-panel{right:16px}#pbc-bubble{right:16px}}
</style>

<div id="pbc-root"
     data-send="{{ route('chatbot.send') }}"
     data-reset="{{ route('chatbot.reset') }}"
     @if($pbcLoggedIn) data-history="{{ route('chatbot.history') }}" @endif
     data-csrf="{{ csrf_token() }}">

  <button id="pbc-btn" type="button" aria-label="Buka Asisten Pabalu" title="Asisten Pabalu">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8M8 13h5"/>
    </svg>
    @if($pbcAdminChat)<span class="pbc-badge" id="pbc-btn-badge">0</span>@endif
  </button>

  @if($pbcAdminChat)
  <button id="pbc-bubble" type="button">Pesan baru dari Admin</button>
  @endif

  <div id="pbc-panel" role="dialog" aria-label="Asisten Pabalu">
    <div class="pbc-head">
      <div>
        <strong>Asisten Pabalu</strong>
        <span>{{ $pbcLoggedIn ? 'Panduan penggunaan aplikasi' : 'Tanya seputar Pabalu' }}</span>
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

  function add(text, who) {
    var el = document.createElement('div');
    el.className = 'pbc-msg ' + (who === 'user' ? 'pbc-user' : 'pbc-bot');
    if (who === 'user') { el.textContent = text; } else { el.innerHTML = render(text); }
    log.appendChild(el);
    log.scrollTop = log.scrollHeight;
    return el;
  }

  function start() {
    log.innerHTML = '';
    add(greeting, 'bot');
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
    add(question, 'user');
    input.value = '';
    var typing = add('Mengetik…', 'bot');
    typing.classList.add('pbc-typing');

    fetch(root.dataset.send, {
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
      .catch(function () { return 'Tidak dapat terhubung. Periksa koneksi internet Anda.'; })
      .then(function (reply) {
        typing.classList.remove('pbc-typing');
        typing.innerHTML = render(reply);
        log.scrollTop = log.scrollHeight;
        busy = false; send.disabled = false;
        input.focus();
      });
  }

  function openPanel() {
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
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); ask(input.value); }
  });
})();
</script>
@endif
