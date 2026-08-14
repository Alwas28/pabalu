<x-app-layout>
<x-slot name="pageTitle">Halaman</x-slot>

<div class="card animate-fadeUp">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-file-lines a-text" style="margin-right:8px"></i>Kelola Halaman</div>
    <a href="{{ route('pages.create') }}"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none">
      <i class="fa-solid fa-plus"></i> Tambah Halaman
    </a>
  </div>

  @if($pages->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-file-lines"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Halaman</div>
    <p style="font-size:13px;color:var(--muted)">Buat halaman statis seperti Tentang Kami, Kebijakan Privasi, atau Syarat & Ketentuan.</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:60px">Gambar</th>
          <th>Judul</th>
          <th>Slug / URL</th>
          <th style="text-align:center;width:90px">Status</th>
          <th style="width:130px">Diperbarui</th>
          <th style="text-align:center;width:150px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pages as $page)
        <tr>
          <td>
            @if($page->image)
            <img src="{{ asset('storage/' . $page->image) }}" alt="{{ $page->title }}"
              style="width:40px;height:40px;object-fit:cover;border-radius:9px;border:1px solid var(--border)">
            @else
            <div style="width:40px;height:40px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:13px">
              <i class="fa-solid fa-file-lines"></i>
            </div>
            @endif
          </td>
          <td class="td-main">{{ $page->title }}</td>
          <td>
            <span style="font-size:12px;font-family:monospace;color:var(--sub)">/halaman/{{ $page->slug }}</span>
          </td>
          <td style="text-align:center">
            @if($page->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $page->updated_at->diffForHumans() }}</td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              <a href="{{ route('pages.show', $page->slug) }}" target="_blank" title="Lihat / Preview"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px;display:grid;place-items:center;text-decoration:none">
                <i class="fa-solid fa-eye"></i>
              </a>
              <a href="{{ route('pages.edit', $page) }}" title="Edit"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px;display:grid;place-items:center;text-decoration:none">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="POST" action="{{ route('pages.toggle-active', $page) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $page->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $page->is_active ? '#fbbf24' : '#34d399' }}">
                  <i class="fa-solid {{ $page->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              <button title="Hapus" onclick="openDeletePage({{ $page->id }}, '{{ addslashes($page->title) }}')"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- Modal Hapus --}}
<div class="modal-backdrop" id="modal-delete-page" onclick="if(event.target===this)closeModal('modal-delete-page')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete-page" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Halaman?</h3>
        <p style="font-size:13px;color:var(--muted)">
          Halaman <strong id="del-page-name" style="color:var(--text)"></strong> akan dihapus permanen.
        </p>
      </div>
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete-page')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Ya, Hapus
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openDeletePage(id, title) {
  document.getElementById('form-delete-page').action = '/pages/' + id;
  document.getElementById('del-page-name').textContent = title;
  openModal('modal-delete-page');
}
</script>

</x-app-layout>
