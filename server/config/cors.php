<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'OPTIONS'],
    'allowed_origins' => array_values(array_unique(array_filter([
        env('FRONTEND_URL', 'http://localhost:5173'),
        ...(env('APP_ENV', 'production') === 'local' ? ['http://localhost:5173', 'http://127.0.0.1:5173'] : []),
    ]))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Content-Type', 'Authorization', 'X-XSRF-TOKEN', 'X-Requested-With'],
    'exposed_headers' => ['Content-Disposition', 'X-Report-SHA256'],
    'max_age' => 600,
    'supports_credentials' => true,
];
