@php
  $date   = $date ?? '';
  $hour   = $hour ?? '00';
  $minute = $minute ?? '00';
@endphp
<div>
  <label class="f-label">{{ $label }} <span style="color:var(--ac)">*</span></label>
  <div style="display:grid;grid-template-columns:1.4fr .8fr .8fr;gap:8px">
    <input type="date" id="{{ $name }}_date" class="f-input" value="{{ $date }}" required
      onchange="syncDatetime('{{ $name }}')">
    <select id="{{ $name }}_hour" class="f-input" onchange="syncDatetime('{{ $name }}')">
      @for($h = 0; $h < 24; $h++)
      @php $hh = sprintf('%02d', $h); @endphp
      <option value="{{ $hh }}" {{ $hh === $hour ? 'selected' : '' }}>{{ $hh }}</option>
      @endfor
    </select>
    <select id="{{ $name }}_minute" class="f-input" onchange="syncDatetime('{{ $name }}')">
      @for($i = 0; $i < 60; $i++)
      @php $mm = sprintf('%02d', $i); @endphp
      <option value="{{ $mm }}" {{ $mm === $minute ? 'selected' : '' }}>{{ $mm }}</option>
      @endfor
    </select>
  </div>
  <div style="font-size:10.5px;color:var(--muted);margin-top:4px">Format 24 jam</div>
  <input type="hidden" id="{{ $name }}_at" name="{{ $name }}_at">
</div>
