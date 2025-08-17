<?php
// backend/public/summary_test.php
header('Content-Type: application/json; charset=utf-8');

// CORS mínimo para probar desde el navegador
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

try {
  // Carga entorno y DB
  $config = require __DIR__ . '/../config.php';
  require __DIR__ . '/../db.php';

  // (opcional) auth por query mientras probamos
  $token = $_GET['token'] ?? '';
  if (!hash_equals($config['api']['token'], (string)$token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized (test)']);
    exit;
  }

  // Relajar sql_mode a nivel de sesión por si acaso
  try {
    $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','')");
    $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE','')");
    $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'NO_ZERO_IN_DATE','')");
    $pdo->exec("SET time_zone = '+00:00'");
  } catch (Throwable $e) { /* ignore */ }

  // Filtros
  $monthParam  = $_GET['month']  ?? 'all';
  $statusParam = $_GET['status'] ?? 'all';

  $where  = [];
  $params = [];

  if ($monthParam !== 'all') {
    $where[] = 'MONTH(o.date_placed) = :month';
    $params[':month'] = (int)$monthParam;
  }

  if ($statusParam !== 'all') {
    $allowed = [0,3,4];
    $st = (int)$statusParam;
    if (!in_array($st, $allowed, true)) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid status']);
      exit;
    }
    $where[] = 'o.status = :status';
    $params[':status'] = $st;
  } else {
    $where[] = 'o.status IN (0,3,4)';
  }

  $sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

  $sql = "
    SELECT
      TRIM(CONCAT(o.first_name, ' ', o.last_name)) AS client_name,
      o.email AS email,
      COUNT(*) AS orders_count,
      COALESCE(SUM(o.total), 0) AS total_amount
    FROM orders o
    $sqlWhere
    GROUP BY o.first_name, o.last_name, o.email
    ORDER BY total_amount DESC
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $totals = ['orders_count' => 0, 'total_amount' => 0.0];
  foreach ($rows as $r) {
    $totals['orders_count'] += (int)$r['orders_count'];
    $totals['total_amount'] += (float)$r['total_amount'];
  }

  echo json_encode([
    'from'    => 'summary_test.php',
    'filters' => [
      'month'  => ($monthParam === 'all') ? 'all' : (int)$monthParam,
      'status' => $statusParam,
    ],
    'rows'   => $rows,
    'totals' => $totals,
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'test_endpoint_error',
    'detail' => $e->getMessage(),
  ], JSON_UNESCAPED_UNICODE);
}
