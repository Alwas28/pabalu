<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'Menu' }}</title>
<link rel="icon" href="/img/Logo.ico">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ac:#E63329;--ac2:#C42820;
  --red:#E63329;--red-dark:#C42820;--red-soft:#FDECEA;--cream:#FCF7F4;
  --bg:#f4f4f6;--surface:#fff;--border:#e5e7eb;
  --text:#111827;--sub:#374151;--muted:#9ca3af;
}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;-webkit-font-smoothing:antialiased}
img{max-width:100%;display:block}
button{font-family:inherit;cursor:pointer}
input,select,textarea{font-family:inherit}
.f-input{width:100%;padding:10px 14px;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);color:var(--text);font-size:14px;outline:none;transition:border-color .15s}
.f-input:focus{border-color:var(--ac)}
.f-label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:6px}
</style>
{{ $styles ?? '' }}
</head>
<body>
{{ $slot }}
{{ $scripts ?? '' }}
</body>
</html>
