<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],

    // Set FRONTEND_URL in .env to your deployed Vercel URL. We use Bearer
    // token auth (not cookies), so this is the only CORS setting that matters —
    // no SANCTUM_STATEFUL_DOMAINS/CSRF cookie dance needed.
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
