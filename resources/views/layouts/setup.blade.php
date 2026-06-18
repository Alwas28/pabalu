<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle ?? 'Setup Akun' }} — {{ config('app.name', 'Pabalu') }}</title>
<link rel="icon" href="/img/Logo.ico">

<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])

<script>
(function(){
  var p={pabalu:{ac:'#e8192c',ac2:'#c41020',rgb:'232,25,44'},amber:{ac:'#f59e0b',ac2:'#ef4444',rgb:'245,158,11'},emerald:{ac:'#10b981',ac2:'#06b6d4',rgb:'16,185,129'},blue:{ac:'#4f6ef7',ac2:'#7c3aed',rgb:'79,110,247'},violet:{ac:'#8b5cf6',ac2:'#ec4899',rgb:'139,92,246'},rose:{ac:'#f43f5e',ac2:'#f97316',rgb:'244,63,94'},cyan:{ac:'#06b6d4',ac2:'#3b82f6',rgb:'6,182,212'},lime:{ac:'#84cc16',ac2:'#10b981',rgb:'132,204,22'},pink:{ac:'#ec4899',ac2:'#8b5cf6',rgb:'236,72,153'},orange:{ac:'#f97316',ac2:'#f59e0b',rgb:'249,115,22'},sky:{ac:'#38bdf8',ac2:'#6366f1',rgb:'56,189,248'}};
  var c=p[localStorage.getItem('pabalu_accent')]||p.pabalu;
  var r=document.documentElement;
  r.style.setProperty('--ac',c.ac);r.style.setProperty('--ac2',c.ac2);r.style.setProperty('--ac-rgb',c.rgb);
  r.style.setProperty('--ac-lt','rgba('+c.rgb+',.14)');r.style.setProperty('--ac-lt2','rgba('+c.rgb+',.08)');
})();
</script>

<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;flex-direction:column}
.font-display,h1,h2,h3{font-family:'Clash Display',sans-serif}

:root{--ac:#e8192c;--ac2:#c41020;--ac-rgb:232,25,44;--ac-lt:rgba(232,25,44,.14);--ac-lt2:rgba(232,25,44,.08)}

body{
  --bg:#0f1117;--surface:#161b27;--surface2:#1c2336;--border:#252d42;
  --text:#e2e8f0;--muted:#64748b;--sub:#94a3b8;
  background:var(--bg);color:var(--text);
}
body.light{
  --bg:#f1f5f9;--surface:#ffffff;--surface2:#f8fafc;--border:#e2e8f0;
  --text:#1e293b;--muted:#94a3b8;--sub:#64748b;
}

.a-text  {color:var(--ac)!important}
.a-grad  {background:linear-gradient(135deg,var(--ac),var(--ac2))!important}
.a-grad-text{background:linear-gradient(135deg,var(--ac),var(--ac2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

/* Card */
.card{background:var(--surface);border:1px solid var(--border);border-radius:16px}

/* Form */
.f-label{display:block;font-size:12px;font-weight:600;color:var(--sub);margin-bottom:5px;letter-spacing:.3px}
.f-input{width:100%;background:var(--surface2);border:1px solid var(--border);color:var(--text);border-radius:12px;padding:9px 13px;font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s,box-shadow .15s}
.f-input:focus{border-color:var(--ac);box-shadow:0 0 0 3px var(--ac-lt)}
.f-input:disabled{opacity:.6;cursor:not-allowed}
select.f-input option{background:var(--surface2);color:var(--text)}

/* Stepper */
.stepper{display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:36px}
.step-item{display:flex;align-items:center;gap:0}
.step-circle{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;font-size:13px;font-weight:700;transition:all .3s;flex-shrink:0;border:2px solid var(--border)}
.step-circle.done{background:#10b981;border-color:#10b981;color:#fff}
.step-circle.active{border-color:var(--ac);color:var(--ac);background:var(--ac-lt)}
.step-circle.pending{color:var(--muted);background:var(--surface2)}
.step-label{font-size:11px;font-weight:600;margin-top:5px;text-align:center;white-space:nowrap}
.step-label.active{color:var(--ac)}
.step-label.done{color:#10b981}
.step-label.pending{color:var(--muted)}
.step-line{width:60px;height:2px;margin:0 4px;margin-bottom:20px;border-radius:2px;transition:background .3s}
.step-line.done{background:#10b981}
.step-line.pending{background:var(--border)}

/* Type card picker */
.type-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
@media(max-width:560px){.type-cards{grid-template-columns:repeat(2,1fr)}}
.type-card{border:1.5px solid var(--border);border-radius:12px;padding:14px 10px;display:flex;flex-direction:column;align-items:center;gap:7px;cursor:pointer;transition:border-color .2s,background .2s;text-align:center}
.type-card:hover{border-color:var(--ac);background:var(--ac-lt2)}
.type-card.selected{border-color:var(--ac);background:var(--ac-lt)}
.type-icon{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;font-size:16px;background:var(--surface2);color:var(--sub);transition:background .2s,color .2s}
.type-card.selected .type-icon{background:var(--ac-lt);color:var(--ac)}
.type-name{font-size:12px;font-weight:600;color:var(--text)}
.type-desc{font-size:10.5px;color:var(--muted);line-height:1.4}

/* Animations */
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.animate-fadeUp{animation:fadeUp .35s ease both}
@keyframes popIn{0%{transform:scale(.6);opacity:0}70%{transform:scale(1.08)}100%{transform:scale(1);opacity:1}}
.animate-pop{animation:popIn .5s cubic-bezier(.34,1.56,.64,1) both}
@keyframes drawCheck{to{stroke-dashoffset:0}}

/* Scrollbar */
::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px}

/* Toast */
#toast-container{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none}
.toast{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:14px;font-size:13px;font-weight:500;pointer-events:auto;box-shadow:0 4px 24px rgba(0,0,0,.35);animation:fadeUp .25s ease both;border:1px solid var(--border);background:var(--surface);color:var(--text);min-width:240px;max-width:340px}
.toast.success .ti{color:#10b981}.toast.error .ti{color:#f87171}.toast.info .ti{color:#60a5fa}

/* Badge */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600}
.badge-green{background:rgba(16,185,129,.15);color:#34d399}
</style>
</head>

<body>
<script>if(localStorage.getItem('pabalu_theme')==='light')document.body.classList.add('light');</script>

{{-- ── Wrapper ── --}}
<div style="min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px">

  {{-- Logo --}}
  <div style="display:flex;align-items:center;gap:11px;margin-bottom:36px">
    <div style="width:44px;height:44px;border-radius:13px;background:#fff;display:grid;place-items:center;overflow:hidden;box-shadow:0 4px 16px rgba(232,25,44,.3)">
      <img src="/img/logo-pabalu.png" style="width:38px;height:38px;object-fit:contain" alt="Pabalu">
    </div>
    <div>
      <div class="font-display" style="font-size:22px;font-weight:700;color:var(--text);line-height:1">Pa<span class="a-text">balu</span></div>
      <div style="font-size:10px;color:var(--muted);letter-spacing:.3px">Sistem Manajemen UMKM</div>
    </div>
  </div>

  {{-- Stepper --}}
  @isset($step)
  <div class="stepper">
    @php
      $steps = [['label'=>'Info Bisnis','icon'=>'fa-user'], ['label'=>'Outlet','icon'=>'fa-store'], ['label'=>'Selesai','icon'=>'fa-check']];
    @endphp
    @foreach($steps as $i => $s)
      @php $n = $i+1; $state = $n < $step ? 'done' : ($n === $step ? 'active' : 'pending'); @endphp
      <div class="step-item" style="flex-direction:column;align-items:center">
        <div class="step-circle {{ $state }}">
          @if($state === 'done')
            <i class="fa-solid fa-check" style="font-size:12px"></i>
          @else
            {{ $n }}
          @endif
        </div>
        <div class="step-label {{ $state }}">{{ $s['label'] }}</div>
      </div>
      @if(!$loop->last)
        <div class="step-line {{ $n < $step ? 'done' : 'pending' }}"></div>
      @endif
    @endforeach
  </div>
  @endisset

  {{-- Content --}}
  {{ $slot }}

</div>

<div id="toast-container"></div>

<script>
function showToast(type,msg){
  const ic={success:'fa-circle-check',error:'fa-circle-xmark',info:'fa-circle-info'};
  const el=document.createElement('div');
  el.className=`toast ${type}`;
  el.innerHTML=`<i class="fa-solid ${ic[type]||ic.info} ti" style="font-size:16px;flex-shrink:0"></i><span>${msg}</span>`;
  document.getElementById('toast-container').appendChild(el);
  setTimeout(()=>el.style.cssText+=';opacity:0;transition:opacity .3s',2700);
  setTimeout(()=>el.remove(),3000);
}
@if(session('success')) showToast('success', @json(session('success'))); @endif
@if(session('error'))   showToast('error',   @json(session('error')));   @endif
@if(session('info'))    showToast('info',     @json(session('info')));    @endif
</script>

@stack('scripts')
</body>
</html>
