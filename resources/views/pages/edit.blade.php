<x-app-layout>
<x-slot name="pageTitle">Edit Halaman</x-slot>

<div style="max-width:760px;margin:0 auto">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:var(--muted)">
    <a href="{{ route('pages.index') }}" style="color:var(--muted);text-decoration:none">
      <i class="fa-solid fa-file-lines"></i> Halaman
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">{{ $page->title }}</span>
  </div>

  {{-- URL publik + aksi Preview & Salin URL --}}
  <div class="card animate-fadeUp" style="margin-bottom:16px">
    <div style="padding:16px 22px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
      <div style="flex:1;min-width:200px">
        <div style="font-size:11px;color:var(--muted);margin-bottom:3px">URL Publik</div>
        <div style="font-size:13px;font-family:monospace;color:var(--text);word-break:break-all" id="page-url">{{ route('pages.show', $page->slug) }}</div>
      </div>
      <button type="button" onclick="copyPageUrl()" id="copy-btn"
        style="padding:9px 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px">
        <i class="fa-solid fa-copy"></i> Salin URL
      </button>
      <a href="{{ route('pages.show', $page->slug) }}" target="_blank"
        style="padding:9px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:7px">
        <i class="fa-solid fa-eye"></i> Preview
      </a>
    </div>
    <div style="padding:0 22px 14px;font-size:11.5px;color:var(--muted)">
      Tempel URL ini di <strong style="color:var(--sub)">Pengaturan Homepage → Menu</strong> supaya halaman ini bisa diakses dari menu navigasi.
    </div>
  </div>

  <form method="POST" action="{{ route('pages.update', $page) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @include('pages._form')

    <div style="display:flex;justify-content:space-between;align-items:center">
      <a href="{{ route('pages.index') }}"
        style="padding:10px 20px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13.5px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:7px">
        <i class="fa-solid fa-arrow-left"></i> Kembali
      </a>
      <button type="submit"
        style="padding:10px 26px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:8px">
        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
      </button>
    </div>
  </form>

</div>

<script>
function copyPageUrl() {
  const url = document.getElementById('page-url').textContent.trim();
  navigator.clipboard.writeText(url).then(() => {
    const btn = document.getElementById('copy-btn');
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Tersalin!';
    setTimeout(() => { btn.innerHTML = original; }, 1800);
  });
}
</script>

</x-app-layout>
