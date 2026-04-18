<x-app-layout title="Log Aktivitas">

  {{-- Filter --}}
  <form method="GET" action="{{ route('activity-logs.index') }}" id="filter-form">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:10px;align-items:end;flex-wrap:wrap">
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Pengguna</label>
        <select name="user_id" class="f-input">
          <option value="">— Semua —</option>
          @foreach($users as $u)
          <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Aksi</label>
        <select name="action" class="f-input">
          <option value="">— Semua Aksi —</option>
          @foreach($actions as $a)
          <option value="{{ $a }}" @selected(request('action') === $a)>
            {{ \App\Models\ActivityLog::actionLabel($a) }}
          </option>
          @endforeach
        </select>
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Dari Tanggal</label>
        <input type="date" name="date_from" class="f-input" value="{{ request('date_from', today()->toDateString()) }}">
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Sampai Tanggal</label>
        <input type="date" name="date_to" class="f-input" value="{{ request('date_to', today()->toDateString()) }}">
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-primary" style="padding:9px 16px">
          <i class="fa-solid fa-magnifying-glass"></i> Filter
        </button>
        <a href="{{ route('activity-logs.index') }}" class="btn" style="padding:9px 14px" title="Reset">
          <i class="fa-solid fa-rotate"></i>
        </a>
      </div>
    </div>
  </form>

  {{-- Stats --}}
  <div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card animate-fadeUp">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)"><i class="fa-solid fa-list-check"></i></div>
      <div><div class="stat-num">{{ $logs->count() }}</div><div class="stat-label">Entri Ditampilkan</div></div>
    </div>
    <div class="stat-card animate-fadeUp d1">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-users"></i></div>
      <div><div class="stat-num">{{ $logs->pluck('user_id')->filter()->unique()->count() }}</div><div class="stat-label">Pengguna Aktif</div></div>
    </div>
    <div class="stat-card animate-fadeUp d2">
      <div class="stat-icon" style="background:rgba(129,140,248,.15);color:#818cf8"><i class="fa-solid fa-bolt"></i></div>
      <div><div class="stat-num">{{ $logs->pluck('action')->unique()->count() }}</div><div class="stat-label">Jenis Aksi</div></div>
    </div>
  </div>

  {{-- Log Table --}}
  <div class="card animate-fadeUp d3">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-scroll a-text" style="margin-right:8px"></i>Log Aktivitas
      </div>
      <span class="badge badge-blue">{{ $logs->count() }} entri</span>
    </div>

    @if($logs->isEmpty())
    <div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
      <i class="fa-solid fa-inbox" style="font-size:40px;display:block;margin-bottom:14px;opacity:.3"></i>
      Tidak ada aktivitas ditemukan.
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:150px">Waktu</th>
            <th style="width:130px">Pengguna</th>
            <th style="width:150px">Aksi</th>
            <th>Deskripsi</th>
            <th style="width:80px">Model</th>
            <th style="width:110px">IP</th>
          </tr>
        </thead>
        <tbody>
          @foreach($logs as $log)
          <tr>
            <td style="font-size:11.5px;white-space:nowrap;color:var(--muted)">
              <div style="font-weight:600;color:var(--sub)">{{ $log->created_at->format('d/m/Y') }}</div>
              {{ $log->created_at->format('H:i:s') }}
            </td>
            <td>
              @if($log->user)
              <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $log->user->name }}</div>
              <div style="font-size:11px;color:var(--muted)">{{ $log->user->email }}</div>
              @else
              <span style="color:var(--muted);font-size:12px">— Sistem —</span>
              @endif
            </td>
            <td>
              <span class="badge {{ \App\Models\ActivityLog::actionColor($log->action) }}">
                {{ \App\Models\ActivityLog::actionLabel($log->action) }}
              </span>
            </td>
            <td style="font-size:13px;color:var(--text)">
              {{ $log->description }}
              @if($log->properties)
              <button type="button"
                onclick="toggleProps({{ $log->id }})"
                style="margin-left:6px;font-size:10.5px;color:var(--ac);background:none;border:none;cursor:pointer;padding:0">
                <i class="fa-solid fa-circle-info"></i>
              </button>
              <div id="props-{{ $log->id }}" style="display:none;margin-top:6px;padding:8px 10px;background:var(--surface2);border-radius:8px;font-size:11.5px;font-family:monospace;color:var(--muted);word-break:break-all">
                {{ json_encode($log->properties, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}
              </div>
              @endif
            </td>
            <td style="font-size:12px;color:var(--muted)">
              @if($log->model_type)
              <div style="font-weight:600;color:var(--sub)">{{ $log->model_type }}</div>
              <div style="font-size:11px">#{{ $log->model_id }}</div>
              @else —
              @endif
            </td>
            <td style="font-family:monospace;font-size:11.5px;color:var(--muted)">
              {{ $log->ip ?? '—' }}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($logs->count() >= 500)
    <div style="padding:12px 20px;font-size:12.5px;color:var(--muted);border-top:1px solid var(--border);text-align:center">
      <i class="fa-solid fa-triangle-exclamation" style="color:var(--ac);margin-right:5px"></i>
      Menampilkan 500 entri terbaru. Gunakan filter untuk mempersempit hasil.
    </div>
    @endif
  </div>
  @endif

  @push('scripts')
  <script>
  function toggleProps(id) {
    var el = document.getElementById('props-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
  }
  </script>
  @endpush

</x-app-layout>
