<?php

return [

    // Aktifkan CORS hanya untuk rute API, storage, dan cookie
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],

    // Izinkan semua metode HTTP
    'allowed_methods' => ['*'],

    // Izinkan origin Vue (Vite dev server)
    'allowed_origins' => ['http://localhost:5173'],

    // Boleh kosong jika allowed_origins sudah eksplisit
    'allowed_origins_patterns' => [],

    // Izinkan semua header request
    'allowed_headers' => ['*'],

    // Expose header yang dibutuhkan agar browser bisa mengakses response (opsional untuk file download)
    'exposed_headers' => ['Content-Disposition'],

    // Simpan preflight request selama 1 jam
    'max_age' => 3600,

    // Izinkan credential seperti cookie/token
    'supports_credentials' => true,

];
