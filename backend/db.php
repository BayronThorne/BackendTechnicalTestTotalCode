<?php
$config = require __DIR__ . '/config.php';

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $config['db']['host'],
    $config['db']['port'],
    $config['db']['name']
);

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'DB connection failed', 'detail' => $e->getMessage()]);
    exit;
}
