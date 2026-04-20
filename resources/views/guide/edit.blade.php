<x-app-layout title="Edit Panduan Penggunaan">

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<style>
.EasyMDEContainer { position: relative; z-index: 1; }
.EasyMDEContainer .CodeMirror{
  background:var(--surface2);color:var(--text);border:1px solid var(--border);
  border-radius:0 0 10px 10px;min-height:480px;font-size:14px;line-height:1.7;
  position:relative;z-index:1;
}
.EasyMDEContainer .CodeMirror,
.EasyMDEContainer .CodeMirror *,
.EasyMDEContainer .CodeMirror-scroll,
.EasyMDEContainer .CodeMirror-sizer,
.EasyMDEContainer .CodeMirror-lines {
  pointer-events: auto !important;
}
.EasyMDEContainer .CodeMirror-cursor {
  border-left: 2px solid var(--ac) !important;
  pointer-events: none !important;
}
.EasyMDEContainer .editor-toolbar{
  background:var(--surface);border:1px solid var(--border);border-bottom:none;
  border-radius:10px 10px 0 0;opacity:1;position:relative;z-index:2;
}
.EasyMDEContainer .editor-toolbar button{color:var(--sub)!important;border-radius:6px}
.EasyMDEContainer .editor-toolbar button:hover,.EasyMDEContainer .editor-toolbar button.active{
  background:var(--ac-lt)!important;color:var(--ac)!important;border-color:transparent!important
}
.EasyMDEContainer .editor-toolbar i.separator{border-color:var(--border)!important}
.editor-preview{background:var(--surface);color:var(--text);padding:16px 20px}
.editor-preview h1,.editor-preview h2,.editor-preview h3{color:var(--text);font-family:'Clash Display',sans-serif;font-weight:700}
.editor-preview h2{color:var(--ac)}
.editor-preview p,.editor-preview li{color:var(--sub)}
.editor-preview code{background:var(--surface2);padding:1px 5px;border-radius:4px;color:var(--ac);font-size:12px}
.editor-preview blockquote{border-left:3px solid var(--ac);padding:8px 14px;background:var(--ac-lt);border-radius:0 8px 8px 0}
.CodeMirror-placeholder { color: var(--muted) !important; }
</style>
@endpush

<div style="max-width:900px">

  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:4px">
    <div style="font-size:13px;color:var(--muted)">
      Tulis panduan dalam format <strong style="color:var(--text)">Markdown</strong>.
      Mendukung heading, bold, list, tabel, blockquote, dan kode.
    </div>
    <a href="{{ route('guide.index') }}" class="btn" style="text-decoration:none">
      <i class="fa-solid fa-eye"></i> Lihat Hasil
    </a>
  </div>

  <form method="POST" action="{{ route('guide.update') }}" id="guide-form">
    @csrf @method('PUT')

    <div class="card animate-fadeUp">
      <div class="card-body" style="padding:20px">
        <textarea id="editor" name="content">{{ $content }}</textarea>
      </div>
      <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div style="font-size:12px;color:var(--muted)">
          <i class="fa-solid fa-keyboard" style="margin-right:5px"></i>
          <kbd style="background:var(--surface2);border:1px solid var(--border);padding:1px 5px;border-radius:4px;font-size:11px">Ctrl+S</kbd>
          untuk simpan cepat
        </div>
        <div style="display:flex;gap:10px">
          <a href="{{ route('guide.index') }}" class="btn" style="text-decoration:none">Batal</a>
          <button type="button" onclick="syncAndSubmit()" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Panduan
          </button>
        </div>
      </div>
    </div>

  </form>

  {{-- Cheatsheet ringkas --}}
  <div class="card animate-fadeUp d2">
    <div class="card-header">
      <div class="card-title" style="font-size:13px">
        <i class="fa-solid fa-circle-info" style="color:var(--ac);margin-right:6px"></i>Referensi Markdown Cepat
      </div>
    </div>
    <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px 20px;font-size:12px;color:var(--sub)">
      <div><code style="color:var(--ac)"># Judul 1</code> → Heading besar</div>
      <div><code style="color:var(--ac)">## Judul 2</code> → Sub-heading</div>
      <div><code style="color:var(--ac)">**teks**</code> → <strong>Tebal</strong></div>
      <div><code style="color:var(--ac)">*teks*</code> → <em>Miring</em></div>
      <div><code style="color:var(--ac)">- item</code> → Daftar bullet</div>
      <div><code style="color:var(--ac)">1. item</code> → Daftar bernomor</div>
      <div><code style="color:var(--ac)">&gt; teks</code> → Kutipan/catatan</div>
      <div><code style="color:var(--ac)">`kode`</code> → Kode inline</div>
      <div><code style="color:var(--ac)">---</code> → Garis pemisah</div>
      <div><code style="color:var(--ac)">| A | B |</code> → Tabel</div>
    </div>
  </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
var easyMDE = new EasyMDE({
  element: document.getElementById('editor'),
  spellChecker: false,
  autosave: { enabled: false },
  toolbar: [
    'heading-1','heading-2','heading-3','|',
    'bold','italic','|',
    'unordered-list','ordered-list','|',
    'quote','code','table','horizontal-rule','|',
    'preview','side-by-side','fullscreen','|',
    'guide'
  ],
  placeholder: '# Panduan Penggunaan Sistem Pabalu\n\n## 1. Kasir (POS)\n\nLangkah transaksi: pilih produk, masukkan qty, pilih metode bayar, proses.\n\n## 2. Stok\n\nCatat stok awal, tambah stok, dan barang rusak/waste.\n\n## 3. Kelola Produk\n\nTambah, edit, atau nonaktifkan produk dari menu Kelola Produk.\n\n## 4. Laporan\n\nLihat omzet, pengeluaran, dan laba per periode di menu Laporan.',
  minHeight: '480px',
  status: ['lines', 'words'],
});

// Sync EasyMDE ke textarea sebelum submit (apapun cara submitnya)
function syncAndSubmit() {
  document.getElementById('editor').value = easyMDE.value();
  document.getElementById('guide-form').submit();
}

document.getElementById('guide-form').addEventListener('submit', function() {
  document.getElementById('editor').value = easyMDE.value();
});

// Ctrl+S simpan
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    syncAndSubmit();
  }
});
</script>
@endpush

</x-app-layout>
