{{-- Partial form dipakai oleh create.blade.php & edit.blade.php --}}
@php $page = $page ?? null; @endphp

<div class="card animate-fadeUp" style="margin-bottom:16px">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-file-lines a-text" style="margin-right:8px"></i>Informasi Halaman</span>
  </div>
  <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
    <div>
      <label class="f-label">Judul Halaman <span style="color:var(--ac)">*</span></label>
      <input name="title" id="title" class="f-input" required maxlength="200"
        value="{{ old('title', $page->title ?? '') }}" placeholder="cth: Tentang Kami" oninput="syncSlug()">
      @error('title')<p class="f-error" style="margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>@enderror
    </div>

    <div>
      <label class="f-label">Slug (URL) <span style="color:var(--muted);font-weight:400">— dibiarkan kosong = otomatis dari judul</span></label>
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font-size:12.5px;color:var(--muted);white-space:nowrap">{{ url('/halaman') }}/</span>
        <input name="slug" id="slug" class="f-input" maxlength="180"
          value="{{ old('slug', $page->slug ?? '') }}" placeholder="tentang-kami" oninput="slugTouched=true">
      </div>
      @error('slug')<p class="f-error" style="margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>@enderror
    </div>

    <div>
      <label class="f-label">Gambar Unggulan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
      @if($page && $page->image)
      <img src="{{ asset('storage/' . $page->image) }}" alt="" style="width:100%;max-width:320px;height:160px;object-fit:cover;border-radius:10px;border:1px solid var(--border);margin-bottom:8px">
      @endif
      <input type="file" name="image" accept="image/*" class="f-input">
      <p style="font-size:11px;color:var(--muted);margin-top:4px">Maks. 2MB. {{ $page && $page->image ? 'Kosongkan jika tidak ingin mengganti gambar.' : '' }}</p>
    </div>

    <div>
      <label class="f-label">Konten Halaman</label>
      <textarea name="content" id="page-content-editor" rows="12" placeholder="Isi halaman...">{{ old('content', $page->content ?? '') }}</textarea>
      @error('content')<p class="f-error" style="margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>@enderror
    </div>
  </div>
</div>

<div class="card animate-fadeUp d1" style="margin-bottom:16px">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-magnifying-glass-chart a-text" style="margin-right:8px"></i>SEO</span>
  </div>
  <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
    <div>
      <label class="f-label">Meta Title <span style="color:var(--muted);font-weight:400">— kosongkan = pakai Judul Halaman</span></label>
      <input name="meta_title" class="f-input" maxlength="200" value="{{ old('meta_title', $page->meta_title ?? '') }}">
    </div>
    <div>
      <label class="f-label">Meta Description</label>
      <textarea name="meta_description" class="f-input" rows="2" maxlength="255" placeholder="Ringkasan singkat untuk hasil pencarian Google...">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
    </div>
  </div>
</div>

<div class="card animate-fadeUp d2" style="margin-bottom:16px">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-toggle-on a-text" style="margin-right:8px"></i>Status</span>
  </div>
  <div style="padding:18px 22px">
    <label style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:10px;border:1px solid var(--border);cursor:pointer">
      <input type="checkbox" name="is_active" value="1" style="accent-color:var(--ac);width:15px;height:15px;margin-top:2px;flex-shrink:0"
        {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
      <div>
        <div style="font-size:13px;font-weight:600;color:var(--text)">Halaman Aktif</div>
        <div style="font-size:11.5px;color:var(--muted)">Kalau nonaktif, halaman tidak ditampilkan ke publik (hanya admin yang bisa preview)</div>
      </div>
    </label>
  </div>
</div>

<script>
let slugTouched = {{ old('slug', $page->slug ?? '') ? 'true' : 'false' }};
function slugify(str) {
  return str.toLowerCase().trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}
function syncSlug() {
  if (slugTouched) return;
  document.getElementById('slug').value = slugify(document.getElementById('title').value);
}
</script>

{{-- Editor teks kaya untuk konten halaman — TinyMCE (open source/GPL), disajikan lewat
     jsDelivr (paket npm asli, bukan Tiny Cloud) sehingga gratis dan tidak butuh API key. --}}
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: '#page-content-editor',
  height: 440,
  menubar: false,
  branding: false,
  license_key: 'gpl',
  plugins: 'lists link image media table code fullscreen',
  toolbar: 'undo redo | blocks | bold italic underline forecolor | ' +
           'alignleft aligncenter alignright | bullist numlist | ' +
           'link image media table | code fullscreen',
  images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append('file', blobInfo.blob(), blobInfo.filename());
    fetch('{{ route('pages.upload-image') }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: formData,
    })
      .then(res => res.ok ? res.json() : Promise.reject(res.statusText))
      .then(data => resolve(data.location))
      .catch(() => reject('Upload gambar gagal.'));
  }),
});
</script>
