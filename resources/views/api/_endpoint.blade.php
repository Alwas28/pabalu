@php
  [$method, $uid, $path, $summary, $desc, $needsAuth, $params, $responseExample, $statusCode] = $ep;
  $methodLower = strtolower($method);
  $methodClass = ['get'=>'method-get','post'=>'method-post','put'=>'method-put','delete'=>'method-del'][$methodLower] ?? 'method-get';
  $methodLabel = ['get'=>'GET','post'=>'POST','put'=>'PUT','delete'=>'DELETE'][$methodLower] ?? $method;
  // Color status code
  $scClass = match(true){ str_starts_with($statusCode,'2') => 'res-'.$statusCode, str_starts_with($statusCode,'4') => 'res-'.$statusCode, default => '' };
  $codeId = 'code-'.$uid;
@endphp

<div class="endpoint" id="ep-{{ $uid }}">
  <div class="endpoint-header" id="hdr-{{ $uid }}" onclick="toggleEndpoint('{{ $uid }}')">
    <span class="method {{ $methodClass }}">{{ $methodLabel }}</span>
    <span class="endpoint-path">
      /api{!! preg_replace('/\{(\w+)\}/', '<span class="param">{$1}</span>', $path) !!}
    </span>
    <span class="endpoint-summary">{{ $summary }}</span>
    @if($needsAuth)
      <span class="endpoint-auth"><i class="fa-solid fa-lock" style="font-size:10px"></i> Auth</span>
    @endif
    <i class="fa-solid fa-chevron-down chevron" id="chev-{{ $uid }}"></i>
  </div>

  <div class="endpoint-body" id="body-{{ $uid }}">
    <p class="endpoint-desc">{{ $desc }}</p>

    @if(!$needsAuth)
      <div class="info-box" style="margin-bottom:12px">
        <i class="fa-solid fa-lock-open"></i>
        <span>Endpoint ini <strong>tidak membutuhkan autentikasi</strong>.</span>
      </div>
    @endif

    @if(count($params) > 0)
      <div class="params-title">
        @if(in_array($methodLower, ['post','put']))
          <i class="fa-solid fa-code" style="margin-right:4px"></i> Request Body (JSON)
        @else
          <i class="fa-solid fa-filter" style="margin-right:4px"></i> Query Parameters
        @endif
      </div>
      <table>
        <thead>
          <tr>
            <th>Parameter</th>
            <th>Tipe</th>
            <th>Status</th>
            <th>Deskripsi</th>
            <th>Contoh</th>
          </tr>
        </thead>
        <tbody>
          @foreach($params as $p)
            <tr>
              <td><span class="param-name">{{ $p[0] }}</span></td>
              <td><span class="param-type">{{ $p[1] }}</span></td>
              <td>
                @if(str_starts_with(strtolower($p[2]), 'required') && strtolower($p[2]) !== 'required jika tunai' && strtolower($p[2]) !== 'required jika gateway')
                  <span class="badge-req">required</span>
                  @if(str_contains($p[2], ' jika'))
                    <span style="font-size:11px;color:var(--sub)"> {{ substr($p[2], strpos($p[2],' ')) }}</span>
                  @endif
                @elseif(str_starts_with(strtolower($p[2]), 'required jika'))
                  <span class="badge-req">required</span>
                  <span style="font-size:11px;color:var(--sub)"> {{ substr($p[2], 8) }}</span>
                @else
                  <span class="badge-opt">optional</span>
                @endif
              </td>
              <td style="color:var(--muted)">{{ $p[3] }}</td>
              <td><code style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--accent2)">{{ $p[4] }}</code></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

    <div class="params-title" style="margin-top:16px"><i class="fa-solid fa-arrow-down" style="margin-right:4px"></i> Contoh Response</div>
    <div class="response-codes">
      <span class="res-code res-{{ $statusCode }}">{{ $statusCode }}</span>
      @if($needsAuth)
        <span class="res-code res-401">401 Unauthenticated</span>
        <span class="res-code res-403">403 Forbidden</span>
      @endif
      @if(count($params) > 0)
        <span class="res-code res-422">422 Validation Error</span>
      @endif
    </div>

    <div class="code-block" style="margin-top:12px">
      <div class="code-block-header">
        <span class="code-lang">JSON</span>
        <button class="code-copy" onclick="copyCode('{{ $codeId }}')"><i class="fa-regular fa-copy"></i> Copy</button>
      </div>
      <pre id="{{ $codeId }}">{{ json_encode(json_decode($responseExample), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
  </div>
</div>
