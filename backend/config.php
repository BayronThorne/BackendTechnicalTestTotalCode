<?php
// backend/config.php

// Define env() solo una vez para evitar "Cannot redeclare env()"
if (!function_exists('env')) {
    function env($key, $default = null) {
        static $vars = null;
        if ($vars === null) {
            $vars = [];
            $path = __DIR__ . '/.env';
            if (file_exists($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') continue;
                    [$k, $v] = array_pad(explode('=', $line, 2), 2, null);
                    if ($k !== null) {
                        $vars[trim($k)] = trim($v ?? '');
                    }
                }
            }
        }
        return $vars[$key] ?? $default;
    }
}

return [
    'db' => [
        'host' => env('DB_HOST', 'db'),
        'port' => (int)env('DB_PORT', 3306),
        'name' => env('DB_NAME', 'totalcode'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', 'root'),
    ],
    'api' => [
        'token'       => env('API_TOKEN', 'mi_token_super_seguro'),
        'cors_origin' => env('CORS_ORIGIN', '*'),
        'debug'       => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
    ],
];
