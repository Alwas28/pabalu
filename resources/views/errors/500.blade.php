@include('errors.layout', [
    'code'         => '500',
    'icon'         => 'fa-triangle-exclamation',
    'title'        => 'Kesalahan Server',
    'message'      => 'Terjadi kesalahan pada server. Tim kami sudah diberitahu. Silakan coba beberapa saat lagi.',
    'accentColor'  => '#ef4444',
    'accentColor2' => '#7c3aed',
    'showBack'     => true,
])
