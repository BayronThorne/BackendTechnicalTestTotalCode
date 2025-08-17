<?php
// backend/public/health.php
header('Content-Type: application/json; charset=utf-8');

try {
  // CORS mínimo (por si abres desde el frontend)
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, OPTIONS');
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

  // Carga DB
  require __DIR__ . '/../db.php';

  // Verificar BD y tabla
  $pdo->query('USE totalcode');
  $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
  $hasOrders = in_array('orders', array_map('strtolower', $tables), true);

  $rowCount = null;
  if ($hasOrders) {
    $rowCount = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
  }

  echo json_encode([
    'ok' => true,
    'db' => [
      'connected' => true,
      'tables' => $tables,
      'has_orders' => $hasOrders,
      'orders_count' => $rowCount,
    ],
    'php' => PHP_VERSION,
  ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => 'health_error',
    'detail' => $e->getMessage(),
  ], JSON_UNESCAPED_UNICODE);
}
