@php
  $useOld ??= true;
  $val = fn (string $field) => $useOld ? old($field) : '';
@endphp
<div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

  <div>
    <label class="f-label">Nama Lengkap <span style="color:#f87171">*</span></label>
    <input type="text" name="name" id="{{ $prefix }}name" class="f-input" value="{{ $val('name') }}" required>
    @error('name')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
    <div>
      <label class="f-label">No. HP</label>
      <input type="text" name="phone" id="{{ $prefix }}phone" class="f-input" value="{{ $val('phone') }}" placeholder="08xx-xxxx-xxxx">
    </div>
    <div>
      <label class="f-label">Email</label>
      <input type="email" name="email" id="{{ $prefix }}email" class="f-input" value="{{ $val('email') }}">
      @error('email')<div class="f-err" style="font-size:12px;color:#f87171;margin-top:5px">{{ $message }}</div>@enderror
    </div>
  </div>

  <div>
    <label class="f-label">Alamat</label>
    <input type="text" name="address" id="{{ $prefix }}address" class="f-input" value="{{ $val('address') }}">
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
    <div>
      <label class="f-label">Kota</label>
      <input type="text" name="city" id="{{ $prefix }}city" class="f-input" value="{{ $val('city') }}">
    </div>
    <div>
      <label class="f-label">Tanggal Lahir</label>
      <input type="date" name="birth_date" id="{{ $prefix }}birth_date" class="f-input" value="{{ $val('birth_date') }}">
    </div>
  </div>

  <div>
    <label class="f-label">Jenis Kelamin</label>
    <select name="gender" id="{{ $prefix }}gender" class="f-input">
      <option value="" {{ $val('gender') === '' ? 'selected' : '' }}>— Tidak diisi —</option>
      <option value="L" {{ $val('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
      <option value="P" {{ $val('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
    </select>
  </div>

  <div>
    <label class="f-label">Catatan</label>
    <textarea name="notes" id="{{ $prefix }}notes" class="f-input" rows="2">{{ $val('notes') }}</textarea>
  </div>

</div>
