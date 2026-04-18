<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $code }} — {{ config('app.name', 'Pabalu') }}</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:#0f1117;color:#e2e8f0;
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  padding:24px;
}
body.light{background:#f1f5f9;color:#1e293b}

.wrap{
  width:100%;max-width:520px;text-align:center;
  animation:fadeUp .4s ease both;
}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

.logo{
  display:inline-flex;align-items:center;gap:10px;margin-bottom:40px;
  text-decoration:none;
}
.logo-icon{
  width:40px;height:40px;border-radius:11px;
  background:linear-gradient(135deg,#f59e0b,#ef4444);
  display:grid;place-items:center;font-size:16px;color:#fff;flex-shrink:0;
}
.logo-text{font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;color:#e2e8f0}
body.light .logo-text{color:#1e293b}
.logo-text span{color:#f59e0b}

.code-wrap{
  position:relative;display:inline-block;margin-bottom:24px;
}
.code{
  font-family:'Clash Display',sans-serif;font-size:120px;font-weight:700;line-height:1;
  background:linear-gradient(135deg, {{ $accentColor ?? '#f59e0b' }}, {{ $accentColor2 ?? '#ef4444' }});
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  opacity:.18;
}
.code-icon{
  position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
  font-size:52px;color:{{ $accentColor ?? '#f59e0b' }};
}

.title{
  font-family:'Clash Display',sans-serif;font-size:26px;font-weight:700;
  color:#e2e8f0;margin-bottom:10px;
}
body.light .title{color:#1e293b}

.desc{
  font-size:14px;color:#94a3b8;line-height:1.7;margin-bottom:32px;max-width:400px;margin-left:auto;margin-right:auto;
}
body.light .desc{color:#64748b}

.actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}

.btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 20px;border-radius:12px;font-size:13.5px;font-weight:600;
  cursor:pointer;font-family:inherit;text-decoration:none;transition:all .15s;border:none;
}
.btn-primary{
  background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;
}
.btn-primary:hover{opacity:.88;transform:translateY(-1px)}
.btn-secondary{
  background:#1c2336;border:1px solid #252d42;color:#94a3b8;
}
body.light .btn-secondary{background:#f8fafc;border-color:#e2e8f0;color:#64748b}
.btn-secondary:hover{color:#e2e8f0;border-color:#475569}
body.light .btn-secondary:hover{color:#1e293b}

.divider{height:1px;background:#252d42;margin:32px 0}
body.light .divider{background:#e2e8f0}

.hint{font-size:12px;color:#475569}
body.light .hint{color:#94a3b8}
</style>
</head>
<body>

<div class="wrap">
  <a href="{{ url('/') }}" class="logo">
    <div class="logo-icon"><i class="fa-solid fa-store"></i></div>
    <div class="logo-text">Pa<span>balu</span></div>
  </a>

  <div class="code-wrap">
    <div class="code">{{ $code }}</div>
    <div class="code-icon"><i class="fa-solid {{ $icon }}"></i></div>
  </div>

  <h1 class="title">{{ $title }}</h1>
  <p class="desc">{{ $message }}</p>

  <div class="actions">
    @if(isset($showBack) && $showBack)
    <button onclick="history.back()" class="btn btn-secondary">
      <i class="fa-solid fa-arrow-left"></i> Kembali
    </button>
    @endif
    @if(isset($showReload) && $showReload)
    <button onclick="location.reload()" class="btn btn-secondary">
      <i class="fa-solid fa-rotate-right"></i> Muat Ulang
    </button>
    @endif
    @auth
    <a href="{{ route('dashboard') }}" class="btn btn-primary">
      <i class="fa-solid fa-house"></i> Dashboard
    </a>
    @else
    <a href="{{ route('login') }}" class="btn btn-primary">
      <i class="fa-solid fa-right-to-bracket"></i> Login
    </a>
    @endauth
  </div>

  <div class="divider"></div>
  <p class="hint">Kode Error: <strong style="color:#64748b">{{ $code }}</strong> &nbsp;·&nbsp; {{ config('app.name', 'Pabalu') }}</p>
</div>

<script>
const t = localStorage.getItem('pb-theme');
if (t === 'light') document.body.classList.add('light');
const a = localStorage.getItem('pb-accent');
const palette = {
  amber:  {ac:'#f59e0b',ac2:'#ef4444'},
  emerald:{ac:'#10b981',ac2:'#06b6d4'},
  blue:   {ac:'#4f6ef7',ac2:'#7c3aed'},
  violet: {ac:'#8b5cf6',ac2:'#ec4899'},
  rose:   {ac:'#f43f5e',ac2:'#f97316'},
  cyan:   {ac:'#06b6d4',ac2:'#3b82f6'},
  lime:   {ac:'#84cc16',ac2:'#10b981'},
  pink:   {ac:'#ec4899',ac2:'#8b5cf6'},
  orange: {ac:'#f97316',ac2:'#f59e0b'},
  sky:    {ac:'#38bdf8',ac2:'#6366f1'},
};
if (a && palette[a]) {
  const c = palette[a];
  document.querySelectorAll('.btn-primary').forEach(el => {
    el.style.background = `linear-gradient(135deg,${c.ac},${c.ac2})`;
  });
  document.querySelectorAll('.code').forEach(el => {
    el.style.background = `linear-gradient(135deg,${c.ac},${c.ac2})`;
    el.style.webkitBackgroundClip = 'text';
    el.style.backgroundClip = 'text';
  });
  document.querySelectorAll('.code-icon').forEach(el => {
    el.style.color = c.ac;
  });
}
</script>
</body>
</html>
