<?php
// backend/public/index.php

$config = require __DIR__ . '/../config.php';

// CORS + JSON
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowOrigin = $config['api']['cors_origin'] ?: '*';
header("Access-Control-Allow-Origin: $allowOrigin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

// Manejo de errores uniforme (si algo explota, siempre JSON)
error_reporting(E_ALL);
ini_set('display_errors', '0');
set_error_handler(function($severity, $message, $file, $line) {
  if (!(error_reporting() & $severity)) return;
  throw new ErrorException($message, 0, $severity, $file, $line);
});
set_exception_handler(function($e) {
  http_response_code(500);
  echo json_encode([
    'error'  => 'Server error',
    'detail' => $e->getMessage(),
  ], JSON_UNESCAPED_UNICODE);
  exit;
});

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// --------- Auth helpers (Bearer) ----------
function get_auth_header_value(): string {
  if (!empty($_SERVER['HTTP_AUTHORIZATION'])) return $_SERVER['HTTP_AUTHORIZATION'];
  if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
  if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    foreach ($headers as $k => $v) {
      if (strtolower($k) === 'authorization') return $v;
    }
  }
  return '';
}
function check_token(array $config): bool {
  // 1) Header Bearer
  $auth = get_auth_header_value();
  if ($auth && stripos($auth, 'Bearer ') === 0) {
    $token = trim(substr($auth, 7));
    return hash_equals($config['api']['token'], $token);
  }
  // 2) Fallback por query SOLO para debug manual (no lo uses desde el frontend)
  if (!empty($_GET['token'])) {
    return hash_equals($config['api']['token'], (string)$_GET['token']);
  }
  return false;
}

// --------- Router ----------
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

if ($path === '/api/orders/summary' || preg_match('#/api/orders/summary$#', $path)) {
  if (!check_token($config)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
  }
  require __DIR__ . '/../routes/orders.php';
  exit;
}

// 404
http_response_code(404);
echo json_encode(['error' => 'Not found', 'path' => $path]);
