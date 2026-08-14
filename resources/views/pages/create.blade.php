<x-app-layout>
<x-slot name="pageTitle">Tambah Halaman</x-slot>

<div style="max-width:760px;margin:0 auto">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:var(--muted)">
    <a href="{{ route('pages.index') }}" style="color:var(--muted);text-decoration:none">
      <i class="fa-solid fa-file-lines"></i> Halaman
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">Tambah Halaman</span>
  </div>

  <form method="POST" action="{{ route('pages.store') }}" enctype="multipart/form-data">
    @csrf

    @include('pages._form')

    <div style="display:flex;justify-content:space-between;align-items:center">
      <a href="{{ route('pages.index') }}" class="btn-back"
        style="padding:10px 20px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13.5px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:7px">
        <i class="fa-solid fa-arrow-left"></i> Batal
      </a>
      <button type="submit"
        style="padding:10px 26px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:8px">
        <i class="fa-solid fa-floppy-disk"></i> Simpan Halaman
      </button>
    </div>
  </form>

</div>

</x-app-layout>
