<div style="border:1px solid var(--border);border-radius:10px;max-height:260px;overflow-y:auto;padding:10px 14px">
  @forelse($categoriesByType as $typeName => $cats)
    <div style="margin-bottom:10px">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:6px">{{ $typeName }}</div>
      @foreach($cats as $cat)
      <label for="{{ $prefix }}-cat-{{ $cat->id }}" style="display:flex;align-items:center;gap:8px;padding:5px 0;font-size:13px;color:var(--text);cursor:pointer">
        <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="{{ $prefix }}-cat-{{ $cat->id }}"
          style="accent-color:var(--ac);width:15px;height:15px;flex-shrink:0">
        {{ $cat->name }}
      </label>
      @endforeach
    </div>
  @empty
  <p style="font-size:12.5px;color:var(--muted);text-align:center;padding:12px 0">
    Belum ada kategori produk. Buat dulu di halaman <strong>Jenis Outlet → Detail</strong>.
  </p>
  @endforelse
</div>
