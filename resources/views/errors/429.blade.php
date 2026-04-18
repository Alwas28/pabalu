@include('errors.layout', [
    'code'         => '429',
    'icon'         => 'fa-gauge-high',
    'title'        => 'Terlalu Banyak Permintaan',
    'message'      => 'Anda telah mengirim terlalu banyak permintaan dalam waktu singkat. Harap tunggu sebentar sebelum mencoba lagi.',
    'accentColor'  => '#f97316',
    'accentColor2' => '#ef4444',
    'showBack'     => true,
])
