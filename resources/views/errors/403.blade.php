@include('errors.layout', [
    'code'         => '403',
    'icon'         => 'fa-ban',
    'title'        => 'Akses Ditolak',
    'message'      => $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika Anda merasa ini adalah kesalahan.',
    'accentColor'  => '#ef4444',
    'accentColor2' => '#f97316',
    'showBack'     => true,
])
